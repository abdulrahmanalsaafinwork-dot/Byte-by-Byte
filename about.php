<!DOCTYPE html>
<html>

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
    
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="styles.css">

            <link rel="stylesheet" href="/project/testing/ai_style.css?v=<?= time(); ?>">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded"/>
    <title>Byte By Byte</title>
</head>

<body>
<?php include 'header.php'; ?>

<?php
// ...existing code...
// include chatbot markup (file lives in testing/)
include_once __DIR__ . '/testing/index.php';
?>


    <section id="page-header" class="about-header">
          <h2>#KnowUS</h2>
          <p>Virtus et Celeritas in Machinis</p>
    </section>

    <section id="about-head" class="section-p1">
        <img src="Img/design/AdobeStock_397851688.jpeg" alt="">
        <div>
            <h2>Who We Are?</h2>
            <p>At Byte By Byte, we believe every great build starts one piece at a time. 
               We are an e-commerce store dedicated to providing high-quality 
               PC components from processors and graphics cards to memory, storage, and accessories. 
               Whether you’re upgrading your setup or starting fresh, our catalog is built for gamers, creators, and tech enthusiasts alike. 
               Beyond selling parts, we also offer a DIY section, guiding you through the process of building your own PC step-by-step. 
               Our mission is to make technology accessible, customizable, and truly yours — byte by byte.</p>

               <abbr title="">Whether you’re upgrading or starting fresh, Byte By Byte makes it simple to build the PC you’ve always dream of</abbr>

               <br><br>

               <marquee bgcolor="#ccc" loop="-1" scrollamount="5" width="100%">“Whether you’re upgrading or starting fresh, 
                Byte By Byte makes it simple to build the PC you’ve always dream of.”</marquee>
        </div>


    </section>

    <section id="feature" class="section-p1">
        <div class="fe-box">
            <img src="Img/feature/shipping.png" alt="">
            <h6>Free shipping</h6>
        </div>
        <div class="fe-box">
            <img src="Img/feature/cart.png" alt="">
            <h6>Online Order</h6>
        </div>
        <div class="fe-box">
            <img src="Img/feature/diy.png" alt="">
            <h6>Do It Yourself</h6>
        </div>
        <div class="fe-box">
            <img src="Img/feature/filter.png" alt="">
            <h6>Advance Filtering</h6>
        </div>
        <div class="fe-box">
            <img src="Img/feature/Clock.png" alt="">
            <h6>Real Time Stock</h6>
        </div>
          <div class="fe-box">
            <img src="Img/feature/deliver.png" alt="">
            <h6>Delivery ETA</h6>
        </div>
    </section>

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
    <script src="/project/testing/ai_script.js?v=<?= time(); ?>"></script>
</body>

</html>