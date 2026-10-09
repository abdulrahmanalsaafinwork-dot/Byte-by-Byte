<?php
require_once 'config.php';
// ✅ Check for ?success=1 in the URL after a completed payment
$paymentSuccess = isset($_GET['success']) && $_GET['success'] == '1';

?>

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

    <style>
        /* ✅ Success banner base state */
        .payment-success {
            position: fixed;
            top: -60px; /* start hidden */
            left: 50%;
            transform: translateX(-50%);
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 14px 24px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            opacity: 0;
            pointer-events: none;
            transition: top 0.5s ease, opacity 0.5s ease;
            z-index: 9999;
        }
        /* ✅ When shown */
        .payment-success.show {
            top: 20px;
            opacity: 1;
        }
    </style>
</head>

<body>
<?php include 'header.php'; ?>

<?php
// ...existing code...
// include chatbot markup (file lives in testing/)
include_once __DIR__ . '/testing/index.php';
?>


<?php if ($paymentSuccess): ?>
    <div id="payment-success" class="payment-success">
        ✅ Your order was placed successfully!
    </div>
<?php endif; ?>

    <section id="hero">
          <h4>Upgrade For Less</h4>
          <h2>Super Value Deals</h2>
          <h1>On All PC Components</h1>
          <p>Save up to 30% with exclusive coupons!</p>
          <button>shop now <a href="shop.php"></a></button>
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

<section class="home-products section-p1">
  <h2>Featured Products</h2>
  <p>PC Components</p>
  <div class="pro-container">

        <div class="pro">
      <div class="image-section">
        <img src="Img/product/Intel Core i9-14900K Desktop Processor 24 cores.webp" alt="">
      </div>

      <div class="info-section">
        <div class="text-content">
          <span>Intel</span>
          <h5>Intel Core i9-14900K Desktop Processor 24 cores</h5>
        </div>

        <div class="bottom-info">
          <div class="stars-price">
            <div class="stars">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
            </div>
            <h4>₱22,800.00</h4>
          </div>
          <a href="shop.html"><i class="fa fa-shopping-cart cart"></i></a>
        </div>
      </div>
    </div>

         <div class="pro">
      <div class="image-section">
        <img src="Img/product/AMD Ryzen 9 7950X 16-Core, 32-Thread Unlocked Desktop Processor.jpg" alt="">
      </div>

      <div class="info-section">
        <div class="text-content">
          <span>AMD</span>
          <h5>AMD Ryzen 9 7950X 16-Core</h5>
        </div>

        <div class="bottom-info">
          <div class="stars-price">
            <div class="stars">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
            </div>
            <h4>₱25,536.00</h4>
          </div>
          <a href="shop.html"><i class="fa fa-shopping-cart cart"></i></a>
        </div>
      </div>
    </div>

             <div class="pro">
      <div class="image-section">
        <img src="Img/product/GeForce RTX 5070 Ti.jpg" alt="">
      </div>

      <div class="info-section">
        <div class="text-content">
          <span>NVIDIA</span>
          <h5>GeForce RTX 5070 TI</h5>
        </div>

        <div class="bottom-info">
          <div class="stars-price">
            <div class="stars">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
            </div>
            <h4>₱47,823.00</h4>
          </div>
          <a href="shop.html"><i class="fa fa-shopping-cart cart"></i></a>
        </div>
      </div>
    </div>

                 <div class="pro">
      <div class="image-section">
        <img src="Img/product/Radeon RX 7900 XTX.webp" alt="">
      </div>

      <div class="info-section">
        <div class="text-content">
          <span>AMD</span>
          <h5>Radeon RX 7900 XTX</h5>
        </div>

        <div class="bottom-info">
          <div class="stars-price">
            <div class="stars">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
            </div>
            <h4>₱45,543.00</h4>
          </div>
          <a href="shop.html"><i class="fa fa-shopping-cart cart"></i></a>
        </div>
      </div>
    </div>

                   <div class="pro">
      <div class="image-section">
        <img src="Img/product/ASUS ROG Strix Z790-E Gaming WiFi.webp" alt="">
      </div>

      <div class="info-section">
        <div class="text-content">
          <span>ASUS</span>
          <h5>ASUS ROG Strix Z790-E Gaming WiFi</h5>
        </div>

        <div class="bottom-info">
          <div class="stars-price">
            <div class="stars">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
            </div>
            <h4>₱28,500.00</h4>
          </div>
          <a href="shop.html"><i class="fa fa-shopping-cart cart"></i></a>
        </div>
      </div>
    </div>

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
    
    <script>
      // ✅ Fade-in / fade-out effect for the success banner
      document.addEventListener("DOMContentLoaded", () => {
          const note = document.getElementById("payment-success");
          if (note) {
              requestAnimationFrame(() => note.classList.add("show"));
              setTimeout(() => note.classList.remove("show"), 2500); // start fade/slide out
              setTimeout(() => note.remove(), 3000);                 // remove from DOM
          }
      });
    </script>
</body>

</html>
