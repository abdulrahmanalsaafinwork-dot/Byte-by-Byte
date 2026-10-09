<?php
require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (!empty($name) && !empty($email) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO customer_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        $stmt->execute();
        echo "<script>alert('✅ Message sent successfully!'); window.location.href='contact.php';</script>";
    } else {
        echo "<script>alert('⚠️ Please fill out all required fields.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact | Byte By Byte</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="styles.css">
        <link rel="stylesheet" href="/project/testing/ai_style.css?v=<?= time(); ?>">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded"/>
</head>

<body>
<?php include 'header.php'; ?>

<?php
// ...existing code...
// include chatbot markup (file lives in testing/)
include_once __DIR__ . '/testing/index.php';
?>

    <!-- Header -->
    <section id="page-header" class="contact-header">
        <h2>#Let's Talk</h2>
        <p>LEAVE A MESSAGE, WE LOVE TO HEAR FROM YOU!</p>
    </section>

    <!-- Contact Details -->
    <section id="contact-details" class="section-p1">
        <div class="details">
            <span>GET IN TOUCH</span>
            <h2>Visit one of our agency locations or contact us today</h2>
            <h3>Head Office</h3>

            <div>
                <li><i class="fa fa-map"></i><p>St. Nick's Fix, 246 Spring Hill Road, Unit F, Trumbull, CT 06611</p></li>
                <li><i class="fa fa-envelope"></i><p>bytebybyte@gmail.com</p></li>
                <li><i class="fa fa-phone"></i><p>1-877-398-7337</p></li>
                <li><i class="fa fa-clock-o clock-icon"></i><p>Monday to Friday: 10AM - 7PM EST</p></li>
            </div>
        </div>

        <div class="map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d11990.361416351414!2d-73.25127560703072!3d41.2960191481919!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89e80835af04f80d%3A0xd1f60709a4495281!2s246%20Spring%20Hill%20Rd%20F%2C%20Trumbull%2C%20CT%2006611%2C%20USA!5e0!3m2!1sen!2sph!4v1757299840790!5m2!1sen!2sph"
             width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </section>

    <!-- Contact Form -->
    <section id="form-details">
        <form action="" method="POST">
            <span>LEAVE A MESSAGE</span>
            <h2>We love to hear from you</h2>

            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="subject" placeholder="Subject (optional)">
            <textarea name="message" cols="30" rows="10" placeholder="Your Message" required></textarea>

            <button type="submit" class="normal">Submit</button>
        </form>

        <div class="people">
            <div>
                <img src="Img/design/download.jpg" alt="">
                <p><span>Abdulrahman H. Alsaafin</span> Senior Marketing Manager<br> Phone: +63 906 8614 002 <br> Email: alsaafin@gmail.com</p>
            </div>

            <div>
                <img src="Img/design/555543765_1360523815441994_4852567410003350044_n.jpg" alt="">
                <p><span>John Marc Besmonte</span> Support Specialist<br> Phone: +63 991 3240 700<br> Email: besmonte@gmail.com</p>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
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

    <!-- Footer -->
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
            <script src="/project/testing/ai_script.js?v=<?= time(); ?>"></script>
</body>
</html>
