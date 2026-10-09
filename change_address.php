<?php
// ...existing code...
$host = "localhost";
$user = "root";
$pass = "";
$db   = "user_db";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

session_start();
$user_id = $_SESSION['user_id'] ?? 1;
$message = "";

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    if ($address === '') {
        $message = "Address cannot be empty.";
    } else {
        $sql = "UPDATE users SET address = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $address, $user_id);
        if ($stmt->execute()) {
            header("Location: profile.php?notif=" . urlencode("Address updated successfully"));
            exit;
        } else {
            $message = "Error updating address.";
        }
        $stmt->close();
    }
}

// fetch current address for form value
$sql = "SELECT address, name, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?: [];
$stmt->close();

$curAddress = $user['address'] ?? '';

require_once 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Change Address - Byte By Byte</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">

    <style>
      .center-page { display:flex; align-items:center; justify-content:center; min-height: calc(100vh - 80px); padding:28px; }
      .card{width:420px;max-width:94%;background:#ffffff;border-radius:12px;padding:20px;border:1px solid #e6edf3;box-shadow:0 8px 22px rgba(20,30,50,0.06);color:#0f172a}
      h1{margin:0 0 8px 0;font-size:18px;font-weight:700}
      .hint{font-size:13px;color:#6b7280;margin-bottom:10px}
      textarea{width:100%;min-height:96px;padding:12px;border-radius:8px;border:1px solid #e9eef4;font-size:14px;color:#0b1220;outline:none;resize:vertical}
      textarea::placeholder{color:#9aa4b2}
      .row{display:flex;gap:10px;margin-top:14px}
      .btn{flex:1;padding:10px;border-radius:8px;border:none;cursor:pointer;font-weight:700}
      .btn-save{background:#5779a1;color:#fff;transition:background .18s ease}
      .btn-save:hover{background:#466384}
.btn-cancel {
  background: transparent;
  border: 1px solid #e6edf3;
  color: #6b7280;
  text-decoration: none;
  display: inline-block;
  text-align: center;
  line-height: 28px;
  transition: all 0.2s ease;
}

.btn-cancel:hover {
  background: rgba(87, 121, 161, 0.08); /* very light tint of #5779a1 */
  border-color: #d0dae4; /* subtle darker border */
  color: #465c74; /* soft blue-gray text on hover */
}

      .message{margin-top:12px;padding:10px;border-radius:8px;background:#fff6f2;border:1px solid #fde2d1;color:#7a2b00;font-size:13px}
    </style>
</head>
<body>
  <main class="center-page">
    <form class="card" method="post" novalidate>
      <h1>Change Address</h1>
      <div class="hint">Update your physical address.</div>

      <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <textarea name="address" placeholder="Street address, city, state, ZIP" required aria-label="Address"><?= htmlspecialchars($_POST['address'] ?? $curAddress) ?></textarea>

      <div class="row">
        <button type="submit" class="btn btn-save">Save</button>
        <a href="profile.php" class="btn btn-cancel">Cancel</a>
      </div>
    </form>
  </main>

    <section id="newsletter" class="section-p1  section-m1">
        <div class="newstext">
            <h4>Sign Up For Newsletters</h4>
            <p>Get Email Updates About Our Latest Shop And <span>Special Offers.</span>
            </p>
        </div>

        <div class="form">
            <input type="text" placeholder="Your Email Adress">
            <Button class="normal">Sign up</Button>
        </div>

    </section>

    <footer class="section-p1">
        <div class="col">
            <img class="flogo" src="Icons/free__1_-removebg-preview.png" alt="">
            <h4>Contact</h4>
            <p><strong>Adress: </strong>St. Nick's Fix, 246 Spring Hill Road, Unit F, Trumbull, CT 06611</p>
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
</body>
</html>