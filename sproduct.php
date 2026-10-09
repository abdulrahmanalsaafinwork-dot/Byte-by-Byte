<?php
require_once 'config.php';
require_once 'header.php';

// Get product ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT s.*, d.description, d.specs 
        FROM shop s 
        LEFT JOIN product_details d ON s.id = d.product_id 
        WHERE s.id = $id LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    die("❌ Product not found.");
}

$stock = (int)($product['stock'] ?? 0);

// server-side class so single-product page shows badges/overlay but JS listing handler won't target this page
$pro_classes = '';
if ($stock <= 0) $pro_classes = ' out-of-stock';
elseif ($stock < 10) $pro_classes = ' low-stock';
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
    <title><?php echo htmlspecialchars($product['name']); ?> | Byte By Byte</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="styles.css">

<style>
/* ===== stock indicators ===== */

/* product wrapper */
.pro {
  position: relative;
  z-index: 0;
}

/* --- LOW STOCK (inside image box) --- */
.pro.low-stock .single-pro-image {
  position: relative;
  overflow: visible;
}

.pro.low-stock .single-pro-image .image-box {
  position: relative;
  overflow: visible;
  z-index: 10;
}

/* Red badge INSIDE image (top-right corner) */
.pro.low-stock .single-pro-image .image-box::after {
  content: "Only " attr(data-stock) " left";
  position: absolute;
  top: 10px;
  right: 10px;
  background: #d9534f;
  color: #fff;
  font-weight: 700;
  font-size: 13px;
  line-height: 1;
  padding: 6px 10px;
  border-radius: 999px;
  z-index: 50; /* always visible */
  box-shadow: 0 4px 10px rgba(0,0,0,0.15);
  pointer-events: none;
  white-space: nowrap;
}

/* Faint red overlay inside image */
.pro.low-stock .single-pro-image .image-box::before {
  content: "";
  position: absolute;
  inset: 0;
  background: rgba(217, 83, 79, 0.05);
  border-radius: inherit;
  z-index: 5;
  pointer-events: none;
}

/* --- OUT OF STOCK overlay limited to image --- */
.single-pro-image {
  position: relative;
  overflow: visible;
}
.single-pro-image .image-box {
  position: relative;
  display: block;
  overflow: visible;
}
.pro.out-of-stock .single-pro-image .image-box::before {
  content: "";
  position: absolute;
  inset: 0;
  background: rgba(10, 12, 16, 0.16);
  z-index: 40;
  pointer-events: auto;
  border-radius: 6px;
}
.pro.out-of-stock .single-pro-image .image-box::after {
  content: "OUT OF STOCK";
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  background: #5779a1;
  color: #fff;
  font-weight: 700;
  font-size: 12px;
  padding: 8px 12px;
  border-radius: 999px;
  z-index: 45;
  white-space: nowrap;
  box-shadow: 0 6px 18px rgba(0,0,0,0.14);
  border: 1px solid rgba(255,255,255,0.06);
  pointer-events: none;
}

/* ensure image sits below overlay but keeps interactivity on buttons */
.single-pro-image img {
  display: block;
  width: 100%;
  height: auto;
  position: relative;
  z-index: 1;
}
</style>

</head>

<body>
<section id="prodetails" class="section-p1">
  <!-- Wrap product page in .pro so badges/overlay can appear; set data-stock for CSS content -->
  <div class="pro<?php echo $pro_classes; ?>" data-stock="<?php echo $stock; ?>">
    <div class="prodetails-container">
      <!-- Product Image -->
      <div class="single-pro-image">
        <div class="image-box" data-stock="<?php echo $stock; ?>">
          <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
      </div>

      <!-- Product Details -->
      <div class="single-pro-details">
        <h6><?php echo htmlspecialchars($product['brand']); ?></h6>
        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
        <h2>₱<?php echo number_format($product['price'], 2); ?></h2>

        <!-- Add to Cart with stock-limited quantity -->
        <div class="cart-actions" data-stock="<?php echo $stock; ?>">
          <input
            type="number"
            id="product-qty"
            value="<?php echo ($stock > 0) ? 1 : 0; ?>"
            min="1"
            max="<?php echo $stock; ?>"
            style="width:70px;padding:6px;margin-right:10px;"
            <?php echo ($stock <= 0) ? 'disabled' : ''; ?>
          >
          <button
            type="button"
            id="add-to-cart-btn"
            class="add-to-cart"
            data-id="<?php echo $product['id']; ?>"
            <?php echo ($stock <= 0) ? 'disabled aria-disabled="true" style="cursor:not-allowed; opacity:.6;"' : ''; ?>
          >
            <i class="fa fa-shopping-cart"></i>
            <?php echo ($stock <= 0) ? 'Out of stock' : 'Add to Cart'; ?>
          </button>

          <?php if ($stock > 0): ?>
            <small style="display:block;margin-top:6px;color:#666;">Only <?php echo $stock; ?> left in stock</small>
          <?php endif; ?>
        </div>

        <h4>Product details</h4>
        <span><?php echo nl2br(htmlspecialchars($product['description'])); ?></span>

        <?php if(!empty($product['specs'])): ?>
        <h4>Specifications</h4>
        <table>
          <tbody>
          <?php
          $specs = json_decode($product['specs'], true);
          if ($specs && is_array($specs)) {
            foreach ($specs as $key => $value) {
              echo '<tr><td>' . htmlspecialchars($key) . '</td><td>' . htmlspecialchars($value) . '</td></tr>';
            }
          }
          ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div><!-- /.pro -->
</section>

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
    <p><strong>Adress: </strong>St. Nick's Fix, 246 Spring Hill Road, Unit F, Trumbull, CT 06611</p>
    <p><strong>Phone: </strong>1-877-398-7337</p>
    <p><strong>Hours: </strong>Monday to Friday: 10AM - 7PM EST</p>
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

<script>
(function () {
  const qtyInput = document.getElementById('product-qty');
  const addBtn = document.getElementById('add-to-cart-btn');
  const stock = Number(document.querySelector('.cart-actions')?.dataset.stock || 0);

  if (qtyInput) {
    qtyInput.addEventListener('input', () => {
      let v = Number(qtyInput.value) || 0;
      if (v < 1) v = 1;
      if (stock > 0 && v > stock) v = stock;
      qtyInput.value = v;
    });

    qtyInput.addEventListener('blur', () => {
      let v = Number(qtyInput.value) || 0;
      if (v < 1) v = 1;
      if (stock > 0 && v > stock) v = stock;
      qtyInput.value = v;
    });
  }

  if (addBtn) {
    addBtn.addEventListener('click', (e) => {
      if (stock <= 0) {
        alert('This item is out of stock.');
        return;
      }
      const qty = Number(qtyInput?.value || 1);
      if (qty < 1) {
        alert('Quantity must be at least 1.');
        return;
      }
      if (qty > stock) {
        alert('Requested quantity exceeds available stock.');
        qtyInput.value = stock;
        return;
      }
    });
  }
})();
</script>

<!-- stock handling for listing cards only -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('#product1 .pro, .home-products .pro').forEach(card => {
    const stockAttr = card.getAttribute('data-stock') ?? card.dataset.stock;
    if (stockAttr == null) return;
    const stock = parseInt(stockAttr, 10);
    if (isNaN(stock)) return;

    card.setAttribute('data-stock', String(stock));

    if (stock <= 0) {
      card.classList.add('out-of-stock');
      card.classList.remove('low-stock');
      card.querySelectorAll('a, button, .cart, .add-to-cart').forEach(el => {
        el.classList.add('disabled');
        el.setAttribute('aria-disabled','true');
      });
    } else if (stock < 10) {
      card.classList.add('low-stock');
      card.classList.remove('out-of-stock');
    } else {
      card.classList.remove('low-stock','out-of-stock');
    }
  });

  document.querySelectorAll('#product1 .pro, .home-products .pro').forEach(card => {
    card.addEventListener('click', (e) => {
      if (e.target.closest('.add-to-cart') || e.target.closest('.cart')) return;
      const link = card.dataset.link;
      if (link) window.location.href = link;
    });
  });
});
</script>

<script src="script.js"></script>
</body>
</html>
