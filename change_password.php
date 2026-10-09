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
    $current = trim($_POST['current_password'] ?? '');
    $new     = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($current === '' || $new === '' || $confirm === '') {
        $message = "All fields are required.";
    } elseif ($new !== $confirm) {
        $message = "New password and confirmation do not match.";
    } elseif (strlen($new) < 8) {
        $message = "New password must be at least 8 characters.";
    } else {
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$res || !password_verify($current, $res['password'])) {
            $message = "Current password is incorrect.";
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashed, $user_id);
            if ($stmt->execute()) {
                header("Location: profile.php?notif=" . urlencode("Password changed successfully"));
                exit;
            } else {
                $message = "Error updating password.";
            }
            $stmt->close();
        }
    }
}

// fetch basic user info (header/footer may use it)
$sql = "SELECT name, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?: [];
$stmt->close();

require_once 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Change Password - Byte By Byte</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">

<style>
  /* only page-specific rules — no body override */
  .center-page {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 80px); /* leaves space for header */
    padding: 28px;
  }

  .card {
    width: 380px;
    max-width: 94%;
    background: #ffffff;
    border-radius: 12px;
    padding: 22px;
    border: 1px solid #e6edf3;
    box-shadow: 0 8px 22px rgba(20,30,50,0.06);
    color: #0f172a;
  }

  h1 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 700;
  }

  .hint {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 12px;
  }

  .field {
    margin-top: 12px;
  }

  /* Scoped input styling — only affects inputs inside .card */
  .card input[type="password"],
  .card input[type="text"] {
    width: 100%;
    padding: 11px 12px;
    border-radius: 8px;
    border: 1px solid #e9eef4;
    background: transparent;
    color: #0b1220;
    outline: none;
    font-size: 14px;
  }

  .card input::placeholder {
    color: #9aa4b2;
  }

  .row {
    display: flex;
    gap: 10px;
    margin-top: 16px;
  }

  .btn {
    flex: 1;
    padding: 10px 12px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: 700;
    font-size: 14px;
  }

  .btn-save {
    background: #5779a1;
    color: #fff;
    transition: background 0.18s ease;
  }

  .btn-save:hover {
    background: #466384;
  }

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


  .message {
    margin-top: 12px;
    padding: 10px;
    border-radius: 8px;
    background: #fff6f2;
    border: 1px solid #fde2d1;
    color: #7a2b00;
    font-size: 13px;
  }

</style>

</head>
<body>
  <main class="center-page">
    <form class="card" method="post" novalidate>
      <h1>Change Password</h1>
      <div class="hint">Enter current password and a new one (min 5 chars).</div>

      <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <div class="field">
        <input type="password" name="current_password" placeholder="Current password" autocomplete="current-password" required aria-label="Current password">
      </div>

      <div class="field">
        <input type="password" name="new_password" placeholder="New password" autocomplete="new-password" required aria-label="New password">
      </div>

      <div class="field">
        <input type="password" name="confirm_password" placeholder="Confirm new password" autocomplete="new-password" required aria-label="Confirm new password">
      </div>

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