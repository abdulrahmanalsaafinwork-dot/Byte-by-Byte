<?php
session_start();
require_once 'config.php';
require_once 'header.php'; // ✅ Use the global header

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['email'];
$userRes = $conn->query("SELECT id FROM users WHERE email='$email'");
$user = $userRes->fetch_assoc();
$user_id = $user['id'];

$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$whereClause = $statusFilter !== 'all' ? "AND o.status = '$statusFilter'" : '';

$sql = "
    SELECT 
        o.id AS order_id,
        o.status,
        SUM(oi.price * oi.quantity) AS total_price,
        COUNT(DISTINCT oi.product_id) AS item_count,
        GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') AS product_names,
        MIN(s.image_url) AS image_url
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN shop s ON oi.product_id = s.id
    WHERE o.user_id = $user_id $whereClause
    GROUP BY o.id
    ORDER BY o.created_at DESC
";
$orders = $conn->query($sql);
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
    <title>Byte By Byte - Tracking Order</title>
<style>
body {
  font-family: "Poppins", sans-serif;
  background-color: #f5f7fa;
  margin: 0;
  padding: 0;
}
.container {
  max-width: 1100px;
  margin: 40px auto;
  background: #fff;
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  display: flex;
  gap: 30px;
  position: relative;
}
h2 {
  margin-bottom: 25px;
  font-weight: 600;
  color: #333;
  text-align: center;
}

/* Sidebar */
.sidebar {
  width: 230px;
  background: #f9f9fc;
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 20px;
  flex-shrink: 0;
  height: fit-content;
}
.sidebar h3 {
  text-align: center;
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 15px;
}
.sidebar ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.sidebar li { margin-bottom: 10px; }
.sidebar a {
  display: block;
  padding: 10px 14px;
  border-radius: 8px;
  text-decoration: none;
  color: #333;
  font-weight: 500;
  transition: all 0.3s ease;
}
.sidebar a:hover {
  background: #f0e6ff;
  color: #5779a1;
}
.sidebar a.active {
  background: #5779a1;
  color: #fff;
}

/* Orders Section */
.orders-section { flex-grow: 1; }
.order-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}
.order-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #fff;
  border-radius: 10px;
  border: 1px solid #ddd;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  padding: 15px 20px;
  text-decoration: none;
  color: inherit;
  transition: all 0.2s ease;
}
.order-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  border-color: #5779a1;
}
.order-left {
  display: flex;
  align-items: center;
  gap: 20px; 
  flex-grow: 1;
}

/* Image with badge */
.order-image {
  position: relative;
  flex-shrink: 0;
  width: 100px;
  height: 100px;
  border-radius: 8px;
  background: #fafafa;
  border: 2px solid #5779a1;
  padding: 8px;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
}
.order-image img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.item-count-badge {
  position: absolute;
  top: 6px;
  right: 6px;
  background: #5779a1;
  color: #fff;
  font-size: 0.8rem;
  padding: 4px 8px;
  border-radius: 12px;
  font-weight: 600;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* ✅ Fixed: Product name truncates with ellipsis only */
.product-names {
  max-width: 300px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.order-info h4 {
  font-size: 1.05rem;
  margin: 0 0 6px;
  font-weight: 600;
  color: #222;
}
.status {
  display: inline-block;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.85rem;
  text-transform: capitalize;
  font-weight: 500;
}
.status.pending { background: #fff3cd; color: #856404; }
.status.paid { background: #d4edda; color: #155724; }
.status.shipped { background: #cce5ff; color: #004085; }
.status.completed { background: #d1ecf1; color: #0c5460; }
.status.cancelled { background: #f8d7da; color: #721c24; }

.order-details {
  text-align: right;
  min-width: 100px;
}
.order-details p {
  margin: 0;
  font-size: 0.9rem;
  color: #555;
}
.order-details .price {
  font-weight: 600;
  color: #222;
  font-size: 1rem;
  margin-top: 4px;
}

/* Notification */
.notif {
  position: fixed;
  top: -60px;
  left: 50%;
  transform: translateX(-50%);
  background: #28a745;
  color: #fff;
  padding: 14px 26px;
  border-radius: 8px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.2);
  font-weight: 600;
  transition: all 0.5s ease;
  z-index: 9999;
  opacity: 0;
}
.notif.error { background: #dc3545; }
.notif.show { top: 20px; opacity: 1; }

/* Responsive */
@media (max-width: 800px) {
  .container { flex-direction: column; gap: 20px; }
  .sidebar { width: 100%; }
}
@media (max-width: 600px) {
  .order-card { flex-direction: column; align-items: flex-start; }
  .order-left { flex-direction: column; align-items: flex-start; }
  .order-details { text-align: left; margin-top: 10px; }
}
</style>
</head>

<body>

<?php if (isset($_GET['cancelled'])): ?>
<div class="notif <?= $_GET['cancelled'] == 1 ? '' : 'error' ?>" id="notif">
  <?= $_GET['cancelled'] == 1 ? 'Order cancelled successfully!' : 'Failed to cancel order!' ?>
</div>
<?php endif; ?>

<div class="container">
  <!-- Sidebar -->
  <div class="sidebar">
    <h3>Filter Orders</h3>
    <ul>
      <li><a href="?status=all" class="<?= $statusFilter=='all'?'active':'' ?>">All Orders</a></li>
      <li><a href="?status=pending" class="<?= $statusFilter=='pending'?'active':'' ?>">Pending</a></li>
      <li><a href="?status=paid" class="<?= $statusFilter=='paid'?'active':'' ?>">Paid</a></li>
      <li><a href="?status=shipped" class="<?= $statusFilter=='shipped'?'active':'' ?>">Shipped</a></li>
      <li><a href="?status=completed" class="<?= $statusFilter=='completed'?'active':'' ?>">Completed</a></li>
      <li><a href="?status=cancelled" class="<?= $statusFilter=='cancelled'?'active':'' ?>">Cancelled</a></li>
    </ul>
  </div>

  <!-- Orders Section -->
  <div class="orders-section">
    <h2>Your Orders</h2>
    <div class="order-list">
      <?php if ($orders->num_rows > 0): ?>
        <?php while ($row = $orders->fetch_assoc()): ?>
          <a href="stracking.php?id=<?= $row['order_id'] ?>" class="order-card">
            <div class="order-left">
              <div class="order-image">
                <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="Product">
                <?php if ($row['item_count'] > 1): ?>
                  <div class="item-count-badge">+<?= $row['item_count'] ?> items</div>
                <?php endif; ?>
              </div>
              <div class="order-info">
                <div class="product-names" title="<?= htmlspecialchars($row['product_names']) ?>">
                  <?= htmlspecialchars($row['product_names']) ?>
                </div>
                <span class="status <?= strtolower($row['status']) ?>">
                  <?= htmlspecialchars(ucfirst($row['status'])) ?>
                </span>
              </div>
            </div>
            <div class="order-details">
              <p>Total Items: <?= (int)$row['item_count'] ?></p>
              <p class="price">₱<?= number_format($row['total_price'], 2) ?></p>
            </div>
          </a>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No orders found for this category.</p>
      <?php endif; ?>
    </div>
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

<script>
window.addEventListener("DOMContentLoaded", () => {
  const notif = document.getElementById("notif");
  if (notif) {
    setTimeout(() => notif.classList.add("show"), 100);
    setTimeout(() => notif.classList.remove("show"), 3000);

    const url = new URL(window.location);
    url.searchParams.delete("cancelled");
    window.history.replaceState({}, document.title, url);
  }
});
</script>

</body>
</html>
