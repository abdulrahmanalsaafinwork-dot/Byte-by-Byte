<?php
require_once 'header.php'; // loads session + db + navbar

// Query all products
$sql = "SELECT * FROM shop";
$result = $conn->query($sql);
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
</head>

<body>

<?php
// ...existing code...
// include chatbot markup (file lives in testing/)
include_once __DIR__ . '/testing/index.php';
?>

    <section id="page-header">
          <h2>#StayTechy</h2>
          <p>Save up to 30% with exclusive coupons!</p> 
    </section>

<section id="shop" class="section-p1">
  <div class="shop-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar"> 
      <h3>Filter By</h3>

      <!-- CPU -->
      <div class="filter-group">
        <button class="filter-toggle">CPU</button>
        <div class="filter-options">
          <label><input type="checkbox" class="filter" data-filter="intel"> Intel</label>
          <label><input type="checkbox" class="filter" data-filter="amd_ryzen"> AMD(Ryzen)</label>
        </div>
      </div>

      <!-- GPU -->
      <div class="filter-group">
        <button class="filter-toggle">GPU</button>
        <div class="filter-options">
          <label><input type="checkbox" class="filter" data-filter="nvidia"> NVIDIA</label>
          <label><input type="checkbox" class="filter" data-filter="amd_radeon"> AMD(Radeon)</label>
        </div>
      </div>

      <!-- Motherboard -->
      <div class="filter-group">
        <button class="filter-toggle">Motherboard</button>
        <div class="filter-options">
          <label><input type="checkbox" class="filter" data-filter="asus"> ASUS</label>
          <label><input type="checkbox" class="filter" data-filter="msi"> MSI</label>
        </div>
      </div>

      <!-- RAM -->
      <div class="filter-group">
        <button class="filter-toggle">RAM</button>
        <div class="filter-options">
          <label><input type="checkbox" class="filter" data-filter="corsair"> Corsair</label>
          <label><input type="checkbox" class="filter" data-filter="g_skill"> G.Skill</label>
        </div>
      </div>

      <!-- Storage with nested -->
      <div class="filter-group">
        <button class="filter-toggle">Storage</button>
        <div class="filter-options">

          <!-- SSD -->
          <div class="nested-group">
            <button class="nested-toggle">SSD</button>
            <div class="nested-options">
              <label><input type="checkbox" class="filter" data-filter="samsung_ssd"> Samsung</label>
              <label><input type="checkbox" class="filter" data-filter="crucial_ssd"> Crucial</label>
            </div>
          </div>

          <!-- HDD -->
          <div class="nested-group">
            <button class="nested-toggle">HDD</button>
            <div class="nested-options">
              <label><input type="checkbox" class="filter" data-filter="seagate_hdd"> Seagate</label>
              <label><input type="checkbox" class="filter" data-filter="wd_hdd"> Western Digital(WD)</label>
            </div>
          </div>

        </div>
      </div>

      <!-- PSU -->
      <div class="filter-group">
        <button class="filter-toggle">Power Supply Unit</button>
        <div class="filter-options">
          <label><input type="checkbox" class="filter" data-filter="seasonic"> Seasonic</label>
          <label><input type="checkbox" class="filter" data-filter="corsair_psu"> Corsair</label>
        </div>
      </div>

      <!-- Case -->
      <div class="filter-group">
        <button class="filter-toggle">Case</button>
        <div class="filter-options">
          <label><input type="checkbox" class="filter" data-filter="nzxt"> NZXT</label>
          <label><input type="checkbox" class="filter" data-filter="lian_li"> Lian Li</label>
        </div>
      </div>

      <!-- CPU Cooler -->
      <div class="filter-group">
        <button class="filter-toggle">CPU Cooler</button>
        <div class="filter-options">
          <label><input type="checkbox" class="filter" data-filter="noctua"> Noctua</label>
          <label><input type="checkbox" class="filter" data-filter="cooler_master"> Cooler Master</label>
        </div>
      </div>

      <!-- Keyboard -->
      <div class="filter-group">
        <button class="filter-toggle">Keyboard</button>
        <div class="filter-options">
          <label><input type="checkbox" class="filter" data-filter="logitech_keyboard"> Logitech</label>
          <label><input type="checkbox" class="filter" data-filter="razer_keyboard"> Razer</label>
        </div>
      </div>

      <!-- Mouse -->
      <div class="filter-group">
        <button class="filter-toggle">Mouse</button>
        <div class="filter-options">
          <label><input type="checkbox" class="filter" data-filter="logitech_mouse"> Logitech</label>
          <label><input type="checkbox" class="filter" data-filter="razer_mouse"> Razer</label>
        </div>
      </div>

      <!-- Monitor -->
      <div class="filter-group">
        <button class="filter-toggle">Monitor</button>
        <div class="filter-options">
          <label><input type="checkbox" class="filter" data-filter="dell"> Dell</label>
          <label><input type="checkbox" class="filter" data-filter="benq"> BenQ</label>
        </div>
      </div>

      <!-- Headset -->
      <div class="filter-group">
        <button class="filter-toggle">Headset</button>
        <div class="filter-options">
          <label><input type="checkbox" class="filter" data-filter="hyperx"> HyperX</label>
          <label><input type="checkbox" class="filter" data-filter="logitech_headset"> Logitech</label>
        </div>
      </div>
    </aside>

    <!-- Products Section -->
    <section id="product1">
      <div class="pro-container">
        <?php
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $category = htmlspecialchars($row['category']);
                $brand    = htmlspecialchars($row['brand']);
                $name     = htmlspecialchars($row['name']);
                $price    = number_format($row['price'], 2);
                $image    = htmlspecialchars($row['image_url']);
                $stock    = (int)($row['stock'] ?? 0);
        ?>
        <div
          class="pro"
          data-category="<?php echo strtolower($category); ?>"
          data-stock="<?php echo $stock; ?>"
          data-link="sproduct.php?id=<?php echo $row['id']; ?>"
        >
          <div class="image-section">
            <img src="<?php echo $image; ?>" alt="<?php echo $name; ?>">
          </div>

          <div class="info-section">
            <div class="text-content">
              <span><?php echo $brand; ?></span>
              <h5><?php echo $name; ?></h5>
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
                <h4>₱<?php echo $price; ?></h4>
              </div>

              <!-- Add to Cart Button -->
              <button
                type="button"
                class="add-to-cart"
                data-id="<?php echo $row['id']; ?>"
                <?php echo ($stock <= 0) ? 'disabled aria-disabled="true" style="cursor:not-allowed;"' : ''; ?>
                style="background:none;border:none;<?php echo ($stock <= 0) ? 'opacity:.9;' : 'cursor:pointer;'; ?>"
              >
                <i class="fa fa-shopping-cart cart"></i>
              </button>
            </div>
          </div>
        </div>
        <?php
            }
        } else {
            echo "<p>No products found.</p>";
        }
        ?>
      </div>
    </section>
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
