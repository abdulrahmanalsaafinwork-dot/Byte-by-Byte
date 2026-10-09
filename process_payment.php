<?php
session_start();
require 'config.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id === 0 && !empty($_SESSION['id'])) {
    $user_id = (int) $_SESSION['id'];
}
if ($user_id === 0) {
    die("Not logged in");
}

// ---------------------------
// Gather input
// ---------------------------
$fullname       = trim($_POST['fullname'] ?? '');
$phone          = trim($_POST['phone'] ?? '');
$address        = trim($_POST['address'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? '');
// products expected as JSON array
$products       = json_decode($_POST['products'] ?? '[]', true);
// cart_ids may come from standard cart flow; ensure array
$cart_ids       = json_decode($_POST['cart_ids'] ?? '[]', true);
if (!is_array($cart_ids)) $cart_ids = [];

// Card inputs
$card_number = trim($_POST['card_number'] ?? '');
$card_expiry = trim($_POST['card_expiry'] ?? '');
$card_cvv    = trim($_POST['card_cvv'] ?? '');

if (!$fullname || !$phone || !$address || !$payment_method) {
    die("Missing required fields");
}
if (empty($products) || !is_array($products)) {
    die("No products selected");
}

// ---------------------------
// Card validation
// ---------------------------
$cardRequired = in_array($payment_method, ['Visa', 'Bancnet'], true);
if ($cardRequired) {
    if (!preg_match('/^\d{4}-\d{4}-\d{4}-\d{4}$/', $card_number)) {
        die("Invalid card format (must be xxxx-xxxx-xxxx-xxxx)");
    }
    if (!$card_expiry) die("Card expiry required");
    if (!preg_match('/^\d{3}$/', $card_cvv)) {
        die("Invalid CVV");
    }
}

// ---------------------------
// Compute total (tolerant parsing)
// ---------------------------
$total = 0.0;
foreach ($products as $p) {
    $priceRaw = $p['price'] ?? 0;
    if (!is_numeric($priceRaw)) {
        $priceRaw = str_replace(['₱','$',',',' '], '', (string)$priceRaw);
    }
    $price = (float)$priceRaw;
    $qty = isset($p['quantity']) ? max(1, (int)$p['quantity']) : 1;
    $total += $price * $qty;
}

if ($total <= 0) {
    die("Invalid order total");
}

// ---------------------------
// Database transaction
// ---------------------------
$conn->begin_transaction();

try {
    // Insert order
    $orderStmt = $conn->prepare("
        INSERT INTO orders (user_id, fullname, phone, address, payment_method, total, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    if (!$orderStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    // types: i user_id, s fullname, s phone, s address, s payment_method, d total
    $orderStmt->bind_param("issssd", $user_id, $fullname, $phone, $address, $payment_method, $total);
    $orderStmt->execute();
    $order_id = $orderStmt->insert_id;
    $orderStmt->close();

    // Insert order items
    $itemStmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    if (!$itemStmt) {
        throw new Exception("Prepare failed (items): " . $conn->error);
    }
    foreach ($products as $p) {
        $pid = (int)($p['id'] ?? 0);
        $qty = isset($p['quantity']) ? max(1, (int)$p['quantity']) : 1;
        $priceRaw = $p['price'] ?? 0;
        if (!is_numeric($priceRaw)) {
            $priceRaw = str_replace(['₱','$',',',' '], '', (string)$priceRaw);
        }
        $price = (float)$priceRaw;
        if ($pid > 0 && $qty > 0) {
            $itemStmt->bind_param("iiid", $order_id, $pid, $qty, $price);
            $itemStmt->execute();
        }
    }
    $itemStmt->close();

    // Remove items from cart (if any)
    if (!empty($cart_ids) && is_array($cart_ids)) {
        // sanitize to ints
        $cart_ids = array_map('intval', $cart_ids);
        // build placeholders for IN(...)
        $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
        $sql = "DELETE FROM cart WHERE user_id = ? AND id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // build types string: one 'i' for user_id + one 'i' per cart id
            $types = str_repeat('i', count($cart_ids) + 1);
            // bind parameters: first types, then actual values (by reference)
            $bind_values = [];
            $bind_values[] = $types;
            $bind_values[] = $user_id;
            foreach ($cart_ids as $cid) $bind_values[] = $cid;

            // convert to references
            $refs = [];
            foreach ($bind_values as $k => $v) {
                $refs[$k] = &$bind_values[$k];
            }
            call_user_func_array([$stmt, 'bind_param'], $refs);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Update user info
    $updateUser = $conn->prepare("UPDATE users SET name=?, phone=?, address=? WHERE id=?");
    if (!$updateUser) {
        throw new Exception("Prepare failed (update user): " . $conn->error);
    }
    $updateUser->bind_param("sssi", $fullname, $phone, $address, $user_id);
    $updateUser->execute();
    $updateUser->close();

    // Optional: store encrypted card info
    if ($cardRequired) {
        $cipher = "aes-256-cbc";
        $key = hash('sha256', 'YourStrongSecretKey', true);
        $iv = random_bytes(openssl_cipher_iv_length($cipher));

        $enc_number = openssl_encrypt($card_number, $cipher, $key, 0, $iv);
        $enc_cvv = openssl_encrypt($card_cvv, $cipher, $key, 0, $iv);
        $encoded_iv = base64_encode($iv);

        $stored_card_number = $enc_number . '::' . $encoded_iv;
        $stored_card_cvv = $enc_cvv . '::' . $encoded_iv;
        $stored_card_expiry = $card_expiry;

        $updateCard = $conn->prepare("
            UPDATE users SET card_number=?, card_expiry=?, card_cvv=?, updated_at=NOW() WHERE id=?
        ");
        if ($updateCard) {
            $updateCard->bind_param("sssi", $stored_card_number, $stored_card_expiry, $stored_card_cvv, $user_id);
            $updateCard->execute();
            $updateCard->close();
        }
    }

    $conn->commit();
    header("Location: index.php?success=1");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Order failed: " . $e->getMessage());
}
?>