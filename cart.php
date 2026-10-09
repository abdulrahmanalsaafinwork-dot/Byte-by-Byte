<?php  
require_once 'header.php';

$user_id = (int) ($_SESSION['user_id'] ?? 0);
if ($user_id === 0) {
    header("Location: login.php");
    exit();
}

// Fetch cart items including stock
$sql = "SELECT 
          c.id AS cart_id, 
          c.quantity, 
          s.id AS product_id, 
          s.name, 
          s.price, 
          s.image_url,
          s.stock
        FROM cart c
        JOIN shop s ON c.product_id = s.id
        WHERE c.user_id = $user_id";
$result = $conn->query($sql);
if (!$result) die('SQL Error: ' . $conn->error);
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
  <link rel="stylesheet" href="styles.css?v=<?= time(); ?>">
  <title>Byte By Byte - Cart</title>
  <link rel="stylesheet" href="/project/testing/ai_style.css?v=<?= time(); ?>">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded"/>
</head>
<body> 
  <?php include_once __DIR__ . '/testing/index.php'; ?>

  <section id="cart-header" class="cart-header">
      <h2>#Keep it Till you Can't</h2>
      <p>Buy all you Want</p>
  </section>

  <section id="cart" class="section-p1">
    <form id="checkout-form" action="payment.php" method="POST">
      <input type="hidden" name="highlighted" id="highlighted">

      <table class="cart-table">
        <thead>
          <tr>
            <td>Remove</td>
            <td>Select</td>
            <td>Image</td>
            <td>Product</td>
            <td>Quantity</td>
            <td>Subtotal</td>
          </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr
              data-cart-id="<?= $row['cart_id'] ?>"
              data-product-id="<?= $row['product_id'] ?>"
              data-product-name="<?= htmlspecialchars($row['name']) ?>"
              data-stock="<?= $row['stock'] ?>"
            >
              <td>
                <a href="remove_from_cart.php?id=<?= $row['cart_id'] ?>" onclick="return confirm('Remove this item?');">
                   <i class="fa fa-trash"></i>
                </a>
              </td>
              <td>
                <i class="fa fa-plus-circle add-icon" style="cursor:pointer;"></i>
                <input type="hidden" name="selected[]" value="">
              </td>
              <td><img src="<?= htmlspecialchars($row['image_url']) ?>" alt="" style="width:80px;height:80px;object-fit:cover;"></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td>
                <input type="number" value="<?= $row['quantity'] ?>" min="1" max="<?= $row['stock'] ?>" class="cart-qty">
              </td>
              <td class="price" data-unit-price="<?= $row['price'] ?>">
                ₱<?= number_format($row['price'] * $row['quantity'], 2) ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" style="text-align:center;">Your cart is empty.</td></tr>
        <?php endif; ?>

        <tr class="cart-total">
          <td colspan="6">
            <div class="total-container">
              <span class="total-label">Total:</span>
              <span class="total-price">₱0.00</span>
            </div>
            <button type="submit" class="checkout-btn">Proceed to Checkout</button>
          </td>
        </tr>
        </tbody>
      </table>
    </form>
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

  <script src="script.js?v=<?= time(); ?>"></script>
  <script src="/project/testing/ai_script.js?v=<?= time(); ?>"></script>
</body>
</html>
