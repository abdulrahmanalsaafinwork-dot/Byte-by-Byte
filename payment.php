<?php
session_start();
require 'config.php';

// =======================
// Check login
// =======================
$user_id = (int) ($_SESSION['user_id'] ?? 0);
if ($user_id === 0 && !empty($_SESSION['id'])) {
    $user_id = (int) $_SESSION['id'];
}
if ($user_id === 0) {
    header("Location: cart.php");
    exit;
}

// =======================
// Detect if this is DIY build payment
// =======================
$items = [];
$total = 0;
$is_diy = false;
$prefill_user = ['name' => '', 'phone' => '', 'address' => ''];

if (isset($_GET['build_id'])) {
    $is_diy = true;
    $build_id = (int) $_GET['build_id'];
    $res = $conn->query("SELECT * FROM diy_builds WHERE id = $build_id LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $items = json_decode($row['products_json'], true);
        $total = $row['total'];
    } else {
        die("Invalid or missing DIY build.");
    }
} else {
    // =======================
    // Normal cart checkout flow
    // =======================
    if (!empty($_POST['highlighted'])) {
        $items = json_decode($_POST['highlighted'], true);
    } elseif (!empty($_POST['selected'])) {
        foreach ($_POST['selected'] as $id) {
            $items[] = ['cart_id' => (int)$id];
        }
    }

    if (empty($items)) {
        header("Location: cart.php?err=no_selection");
        exit;
    }

    // Fetch product details from cart
    foreach ($items as &$p) {
        if (!empty($p['cart_id'])) {
            $cid = (int)$p['cart_id'];
            $sql = "SELECT c.id AS cart_id, s.name, s.price, s.image_url, c.quantity 
                    FROM cart c 
                    JOIN shop s ON c.product_id = s.id 
                    WHERE c.id = $cid AND c.user_id = $user_id";
            $r = $conn->query($sql);
            if ($r && $r->num_rows > 0) {
                $row = $r->fetch_assoc();
                $p = array_merge($p, [
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'quantity' => $row['quantity'],
                    'image_url' => $row['image_url']
                ]);
            }
        }
    }
    unset($p);

    // Compute total
    foreach ($items as $p) {
        if (isset($p['price'], $p['quantity'])) {
            $total += $p['price'] * $p['quantity'];
        }
    }
}

// =======================
// Fetch user details
// =======================
$stmt = $conn->prepare("SELECT name, phone, address FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $prefill_user['name'] = $row['name'] ?? '';
        $prefill_user['phone'] = $row['phone'] ?? '';
        $prefill_user['address'] = $row['address'] ?? '';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Byte By Byte - Payment</title>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="styles.css">
<style>
body { background:#f5f6fa; margin:0; font-family:Arial,sans-serif; }
.payment-wrapper { display:flex; gap:40px; max-width:1200px; margin:40px auto; padding:0 20px; }
.payment-left, .payment-right { background:#fff; border:1px solid #e0e0e0; border-radius:12px; padding:24px; flex:1; box-shadow:0 2px 6px rgba(0,0,0,0.05); }
h2 { margin-top:0; font-size:1.4rem; border-bottom:1px solid #eee; padding-bottom:12px; }
.product-row { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px dashed #e0e0e0; font-size:0.95rem; }
.product-row:last-child { border-bottom:none; }
.product-info { display:flex; align-items:center; gap:10px; }
.product-info img {
  width: 70px;                /* adjust as you like (60→70 slightly larger, still neat) */
  height: auto;               /* keep natural height */
  max-height: 70px;           /* limit so it won’t get too tall */
  object-fit: contain !important;  /* show full image */
  border-radius: 6px;
  padding: 4px;
}

.checkout-form label { display:block; margin:12px 0 4px; font-weight:600; }
.checkout-form input, .checkout-form select, .checkout-form textarea { width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:0.95rem; }
.checkout-form button { margin-top:20px; width:100%; padding:12px; background:#5779a1; color:#fff; border:none; border-radius:6px; font-size:1rem; cursor:pointer; transition:background 0.3s ease,transform 0.2s ease; }
.checkout-form button:hover { background:#466384; transform:translateY(-2px); }
#cc-section { overflow:hidden; max-height:0; opacity:0; transform:translateY(-10px); transition:all 0.5s ease-in-out; }
#cc-section.show { max-height:300px; opacity:1; transform:translateY(0); }
.total-display { font-weight:700; text-align:right; margin-top:16px; font-size:1.1rem; }
</style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="payment-wrapper">
    <!-- LEFT -->
    <div class="payment-left">
        <h2><?= $is_diy ? 'Your DIY PC Build' : 'Your Selected Products' ?></h2>
        <?php foreach ($items as $p): ?>
            <div class="product-row">
                <div class="product-info">
                    <?php if (!empty($p['image_url'])): ?>
                        <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    <?php else: ?>
                        <div style="width:60px;height:60px;border:1px solid #ccc;border-radius:6px;background:#f0f0f0;"></div>
                    <?php endif; ?>
                    <span>
                        <?= htmlspecialchars($p['name'] ?? 'Product') ?>
                        <?php if (!$is_diy && isset($p['quantity'])): ?>
                            × <?= (int)$p['quantity'] ?>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if (isset($p['price'])): ?>
                    <span>₱<?= number_format($p['price'] * ($p['quantity'] ?? 1), 2) ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <?php if ($total > 0): ?>
            <div class="total-display">Total: ₱<?= number_format($total, 2) ?></div>
        <?php endif; ?>
    </div>

    <!-- RIGHT -->
    <div class="payment-right">
        <h2>Payment Details</h2>
        <form class="checkout-form" action="process_payment.php" method="POST">
            <label>Full Name</label>
            <input type="text" name="fullname" value="<?= htmlspecialchars($prefill_user['name']) ?>" placeholder="Enter full name" required>

            <label>Address</label>
            <textarea name="address" placeholder="Enter your address" required><?= htmlspecialchars($prefill_user['address']) ?></textarea>

            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($prefill_user['phone']) ?>" placeholder="Enter phone number" required>

            <label>Payment Method</label>
            <select name="payment_method" id="payment_method" required>
                <option value="">--Select--</option>
                <option value="Cash on Delivery">Cash on Delivery</option>
                <option value="Visa">Visa</option>
                <option value="Bancnet">Bancnet</option>
            </select>

            <?php if ($is_diy): ?>
                <input type="hidden" name="build_id" value="<?= $build_id ?>">
                <input type="hidden" name="products" value='<?= json_encode($items, JSON_HEX_APOS|JSON_HEX_QUOT) ?>'>
            <?php else: ?>
                <input type="hidden" name="products" value='<?= json_encode($items, JSON_HEX_APOS|JSON_HEX_QUOT) ?>'>
                <input type="hidden" name="cart_ids" value='<?= json_encode(array_column($items, "cart_id"), JSON_HEX_APOS|JSON_HEX_QUOT) ?>'>
            <?php endif; ?>

            <div id="cc-section">
                <label>Card Number</label>
                <input type="text" name="card_number" id="card_number" maxlength="19" placeholder="xxxx-xxxx-xxxx-xxxx">
                <label>Expiry (MM/YYYY)</label>
                <input type="month" name="card_expiry" id="card_expiry" min="<?= date('Y-m') ?>">
                <label>CVV</label>
                <input type="password" name="card_cvv" id="card_cvv" maxlength="3" pattern="\d{3}" placeholder="***">
            </div>

            <button type="submit">Confirm Payment</button>
        </form>
    </div>
</div>

<section id="newsletter" class="section-p1 section-m1">
    <div class="newstext">
        <h4>Sign Up For Newsletters</h4>
        <p>Get Email Updates About Our Latest Shop And <span>Special Offers.</span></p>
    </div>
    <div class="form">
        <input type="text" placeholder="Your Email Address">
        <button class="normal">Sign up</button>
    </div>
</section>

<footer class="section-p1">
    <div class="col">
        <img class="flogo" src="Icons/free__1_-removebg-preview.png" alt="">
        <h4>Contact</h4>
        <p><strong>Address: </strong>St. Nick's Fix, 246 Spring Hill Road, Unit F, Trumbull, CT 06611</p>
        <p><strong>Phone: </strong>1-877-398-7337</p>
        <p><strong>Hours: </strong>Monday to Friday: 10AM - 7PM EST</p>
        <div class="follow">
            <h4>Follow us</h4>
            <div class="icon">
                <i class="fa fa-facebook"></i>
                <i class="fa fa-twitter"></i>
                <i class="fa fa-instagram"></i>
                <i class="fa fa-pinterest-p"></i>
                <i class="fa fa-youtube-play"></i>
            </div>
        </div>
    </div>
    <div class="col">
        <h4>About us</h4>
        <a href="#">About Us</a>
        <a href="#">Delivery Information</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms & Condition</a>
        <a href="#">Contact Us</a>
    </div>
    <div class="col">
        <h4>My Account</h4>
        <a href="#">View Cart</a>
        <a href="#">My Wishlist</a>
        <a href="#">Track My Order</a>
        <a href="#">Help</a>
    </div>
    <div class="payment">
        <h4>Payment Method</h4>
        <p>Secure Payment Gateway</p>
        <img src="Img/design/payment.png" class="plogo" alt="">
    </div>
</footer>

<script src="script.js"></script>
<script>
(function() {
    const methodSel = document.getElementById('payment_method');
    const ccSection = document.getElementById('cc-section');
    if (methodSel && ccSection) {
        methodSel.addEventListener('change', () => {
            if (methodSel.value === 'Visa' || methodSel.value === 'Bancnet') {
                ccSection.classList.add('show');
            } else {
                ccSection.classList.remove('show');
            }
        });
        if (methodSel.value === 'Visa' || methodSel.value === 'Bancnet') {
            ccSection.classList.add('show');
        } else {
            ccSection.classList.remove('show');
        }
    }
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function() {
            let val = this.value.replace(/\D/g, '').slice(0, 16);
            this.value = val.replace(/(\d{4})(?=\d)/g, '$1-');
        });
    }
})();
</script>
</body>
</html>
