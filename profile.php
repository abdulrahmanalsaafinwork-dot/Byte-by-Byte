<?php
// --- Database connection ---
$host = "localhost";
$user = "root";
$pass = "";
$db   = "user_db";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

session_start();
$user_id = $_SESSION['user_id'] ?? 1;

$sql  = "SELECT name, profile_image FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();

$profile_img = !empty($user['profile_image'])
    ? $user['profile_image']
    : "Icons/AdobeStock_692347763.png";

$name = $user['name'] ?? "Your Name";

require_once 'header.php'; // loads session + db + navbar
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
    
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="styles.css">

    <title>Byte By Byte</title>


    <style>
#notif {
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  top: -96px;
  z-index: 9999;
  min-width: 260px;
  max-width: 90%;
  padding: 10px 16px;
  border-radius: 10px;
  box-shadow: 0 8px 30px rgba(2, 6, 23, 0.12);
  color: #ffffff; /* changed to white for better contrast on dark bg */
  background-color: #5779a1; /* solid background color */
  font-weight: 600;
  text-align: center;
  transition: top 420ms cubic-bezier(.2, .9, .2, 1), opacity 220ms;
  opacity: 0;
  backdrop-filter: blur(6px);
  border: 1px solid rgba(3, 50, 75, 0.06);
}

  #notif.show{ top:20px; opacity:1; }
</style>
</head>
<body>

<!-- NOTIFICATION: slide-down banner for success messages -->
<div id="notif" aria-live="polite"></div>


<div class="profile-wrapper">
  <!-- LEFT -->
<form class="profile-left" action="upload_profile.php" method="post" enctype="multipart/form-data">
  <label for="newImage" id="profileCircle">
    <img src="<?= htmlspecialchars($profile_img) ?>" alt="Profile" id="profileImg">
    <!-- 👇 must be profile_img to match PHP -->
    <input type="file" name="profile_img" id="newImage" accept="image/*" hidden>
  </label>

  <h2><?= htmlspecialchars($name) ?></h2>

  <div class="btn-group hidden" id="actionButtons">
    <button type="submit" class="btn-save">Save</button>
    <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
  </div>
</form>


  <!-- RIGHT -->
  <div class="profile-right">
    <div class="accordion">
      <button class="accordion-btn">Manage Account ▾</button>
      <div class="accordion-content">
        <a href="change_password.php">Change Password</a>
        <a href="change_address.php">Change Address</a>
        <a href="change_phone.php">Change Phone Number</a>
      </div>
    </div>
    <a class="track-btn same-width" href="track_orders.php">Tracking Order</a>
  </div>
</div>


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
    <script>
(function(){
  function getParam(name){
    name = name.replace(/[[]/,'\\[').replace(/[\]]/,'\\]');
    var r = new RegExp('[\\?&]'+name+'=([^&#]*)').exec(location.search);
    return r ? decodeURIComponent(r[1].replace(/\+/g,' ')) : null;
  }
  var msg = getParam('notif');
  if (!msg) return;
  var el = document.getElementById('notif');
  el.textContent = msg;
  // show
  requestAnimationFrame(function(){ el.classList.add('show'); });
  // hide after 3.2s and clean URL
  setTimeout(function(){
    el.classList.remove('show');
    try {
      if (history && history.replaceState) {
        history.replaceState(null, '', location.pathname + location.hash);
      }
    } catch(e){}
  }, 3200);
})();
</script>
<!-- NOTIFICATION END -->
</body>
</html> 