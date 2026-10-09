<?php
session_start();
include 'config.php';

// Temporary for testing (if login not yet implemented)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

$user_id    = (int) $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantity   = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

if ($product_id > 0 && $quantity > 0) {

    // ✅ Check stock
    $stockResult = $conn->query("SELECT stock FROM shop WHERE id = $product_id");
    if (!$stockResult || $stockResult->num_rows == 0) {
        echo json_encode(["success" => false, "error" => "Product not found."]);
        exit;
    }

    $product = $stockResult->fetch_assoc();
    $stock = (int)$product['stock'];

    // ✅ Check if product already exists in the cart
    $check = $conn->query("SELECT id, quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id");

    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $newQty = $row['quantity'] + $quantity;

        if ($newQty > $stock) {
            echo json_encode(["success" => false, "error" => "Cannot exceed available stock ($stock)."]);
            exit;
        }

        $conn->query("UPDATE cart SET quantity = $newQty, updated_at = NOW() WHERE id = " . $row['id']);
    } else {
        if ($quantity > $stock) {
            echo json_encode(["success" => false, "error" => "Only $stock left in stock."]);
            exit;
        }

        $session_id = session_id();
        $conn->query("INSERT INTO cart (user_id, session_id, product_id, quantity, created_at, updated_at)
                      VALUES ($user_id, '$session_id', $product_id, $quantity, NOW(), NOW())");
    }

    echo json_encode(["success" => true, "message" => "✅ Product added to cart!"]);
    exit;
}

echo json_encode(["success" => false, "error" => "Invalid product or quantity."]);
exit;
?>
