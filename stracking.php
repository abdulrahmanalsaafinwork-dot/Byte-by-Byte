<?php
session_start();
require_once 'config.php';
require_once 'header.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    header("Location: track_orders.php");
    exit;
}

$stmt = $conn->prepare("SELECT o.* FROM orders o WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Order not found.");
}
$order = $result->fetch_assoc();

$itemStmt = $conn->prepare("
    SELECT oi.*, s.name AS product_name, s.image_url, s.price AS product_price
    FROM order_items oi
    JOIN shop s ON oi.product_id = s.id
    WHERE oi.order_id = ?
");
$itemStmt->bind_param("i", $order_id);
$itemStmt->execute();
$itemsRes = $itemStmt->get_result();
$items = [];
while ($row = $itemsRes->fetch_assoc()) {
    $items[] = $row;
}

$isCOD = (strtolower($order['payment_method'] ?? '') === 'cash on delivery');
$statuses = $isCOD
    ? ['pending', 'shipped', 'paid', 'completed']
    : ['pending', 'paid', 'shipped', 'completed'];

$iconMap = [
    'pending'   => 'fa-list',
    'paid'      => 'fa-credit-card',
    'shipped'   => 'fa-truck',
    'completed' => 'fa-check',
    'cancelled' => 'fa-times'
];

$currentIndex = array_search($order['status'], $statuses);
if ($currentIndex === false) $currentIndex = -1;
$progressPercent = (($currentIndex + 1) / count($statuses)) * 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Byte By Byte - Tracking Order</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="styles.css">

<style>
body {
  font-family: "Poppins", sans-serif;
  background: #f7f9fc;
  margin: 0;
  padding: 0;
}

.container {
  max-width: 800px;
  margin: 40px auto;
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
  position: relative;
}

.order-header {
  display: flex;
  align-items: flex-start;
  gap: 25px;
  padding: 25px;
  position: relative;
}

/* Top-right navigation */
.item-nav {
  position: absolute;
  top: 10px;
  right: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.95);
  padding: 5px 12px;
  border-radius: 8px;
  z-index: 10;
}

.product-carousel {
  width: 200px;
  height: 200px;
  position: relative;
  flex-shrink: 0;
  border-radius: 12px;
  overflow: hidden;
  margin-top: 35px;
}

.product-carousel .item {
  display: none;
  width: 100%;
  height: 100%;
}

.product-carousel .item.active {
  display: block;
}

.product-carousel img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  background: #f9fbff;
  border: 4px solid #5779a1;
  border-radius: 12px;
  padding: 10px;
}

.item-count {
  background: #5779a1;
  color: #fff;
  padding: 6px 10px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 14px;
}

.carousel-controls button.nav {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #5779a1;
  padding: 4px;
}
.carousel-controls button.nav:hover {
  color: #35516b;
}

.order-details {
  flex: 1;
  margin-top: 20px;
}

.order-details h2 {
  margin: 0 0 10px;
  font-size: 32px;
  font-weight: 600;
  color: #333;
}

.order-details p {
  margin: 6px 0;
  color: #555;
  line-height: 1.5;
}

/* Tracker */
.tracker {
  position: relative;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 50px 0 30px;
}
.tracker::before {
  content: "";
  position: absolute;
  top: 50%;
  left: 0;
  width: 100%;
  height: 5px;
  background: #d9e3f0;
  transform: translateY(-50%);
  z-index: 1;
  border-radius: 3px;
}
.step {
  z-index: 3;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: #d9e3f0;
  display: flex;
  justify-content: center;
  align-items: center;
  color: white;
}
.step.active {
  background: #5779a1;
}
.step i {
  font-size: 18px;
}

/* Cancel Button */
.cancel-btn {
  display: inline-block;
  background: #c0392b;
  color: #fff;
  padding: 12px 22px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  transition: 0.3s;
  margin-top: 15px;
}
.cancel-btn:hover {
  background: #a93226;
}
.cancel-container {
  text-align: right;
  padding: 0 25px 15px;
}
</style>
</head>

<body>
<div class="container">
  <div class="item-nav">
    <div class="carousel-controls">
      <button class="nav prev" id="prev-btn">‹</button>
    </div>
    <div class="item-count" id="item-count"><?= count($items) > 0 ? '1/' . count($items) : '1/1' ?></div>
    <div class="carousel-controls">
      <button class="nav next" id="next-btn">›</button>
    </div>
  </div>

  <div class="order-header">
    <div class="product-carousel" id="product-carousel">
      <?php foreach ($items as $idx => $it): ?>
        <div class="item <?= $idx === 0 ? 'active' : '' ?>" data-index="<?= $idx ?>">
          <img src="<?= htmlspecialchars($it['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($it['product_name'] ?? '') ?>">
        </div>
      <?php endforeach; ?>
    </div>

    <div class="order-details" id="order-details">
      <h2 id="product-name"><?= htmlspecialchars($items[0]['product_name'] ?? 'Product') ?></h2>
      <p><strong>Payment:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($order['address'] ?? 'N/A') ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone'] ?? 'N/A') ?></p>
      <p><strong>Quantity:</strong> <span id="product-qty"><?= htmlspecialchars($items[0]['quantity'] ?? 1) ?></span></p>
      <p><strong>Price:</strong> ₱<span id="product-price"><?= number_format($items[0]['product_price'] ?? 0, 2) ?></span></p>
      <p><strong>Total:</strong> ₱<?= number_format($order['total'], 2) ?></p>
    </div>
  </div>

  <div class="tracker" data-progress="<?= $progressPercent ?>">
    <?php foreach ($statuses as $i => $st): ?>
      <div class="step <?= $i <= $currentIndex ? 'active' : '' ?>" title="<?= htmlspecialchars(ucfirst($st)) ?>">
        <i class="fa <?= htmlspecialchars($iconMap[$st] ?? 'fa-list') ?>"></i>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'completed'): ?>
    <div class="cancel-container">
      <a href="cancel_order.php?id=<?= $order['id'] ?>" class="cancel-btn">Cancel Order</a>
    </div>
  <?php elseif ($order['status'] === 'cancelled'): ?>
    <p style="color:#c0392b;text-align:center;"><strong>This order has been cancelled.</strong></p>
  <?php endif; ?>
</div>

<!-- Footer and newsletter are unchanged -->

<script>
document.addEventListener("DOMContentLoaded", () => {
  const tracker = document.querySelector('.tracker');
  const progress = tracker.getAttribute('data-progress');
  const bar = document.createElement('div');
  bar.style.position = 'absolute';
  bar.style.top = '50%';
  bar.style.left = '0';
  bar.style.height = '5px';
  bar.style.background = '#5779a1';
  bar.style.transform = 'translateY(-50%)';
  bar.style.borderRadius = '3px';
  bar.style.width = '0';
  bar.style.zIndex = '2';
  bar.style.transition = 'width 0.8s ease-in-out';
  tracker.appendChild(bar);
  setTimeout(() => { bar.style.width = progress + '%'; }, 100);

  const items = <?= json_encode($items) ?>;
  const nameEl = document.getElementById('product-name');
  const qtyEl = document.getElementById('product-qty');
  const priceEl = document.getElementById('product-price');
  const countEl = document.getElementById('item-count');
  const itemDivs = Array.from(document.querySelectorAll('#product-carousel .item'));
  let index = 0;
  const total = itemDivs.length || 1;

  function showIndex(i) {
    index = (i + total) % total;
    itemDivs.forEach(el => el.classList.remove('active'));
    if (itemDivs[index]) itemDivs[index].classList.add('active');
    const item = items[index];
    nameEl.textContent = item.product_name;
    qtyEl.textContent = item.quantity;
    priceEl.textContent = Number(item.product_price).toLocaleString('en-PH', {minimumFractionDigits: 2});
    countEl.textContent = (index + 1) + '/' + total;
  }

  document.getElementById('prev-btn').addEventListener('click', () => showIndex(index - 1));
  document.getElementById('next-btn').addEventListener('click', () => showIndex(index + 1));
});
</script>
</body>
</html>
