<?php

session_start();
include 'config.php';
// ...existing code...

// Fetch products with specs
$sql = "SELECT s.*, pd.description, pd.specs, s.stock 
        FROM shop s
        LEFT JOIN product_details pd ON s.id = pd.product_id
        ORDER BY s.category, s.name";
$res = $conn->query($sql);

$products = [];
while ($r = $res->fetch_assoc()) {
    $r['specs'] = $r['specs'] ?? '';
    $r['description'] = $r['description'] ?? '';
    $r['image_url'] = $r['image_url'] ?? '';
    // normalize numeric price
    $raw = 0.0;
    if (isset($r['price'])) {
        if (is_numeric($r['price'])) $raw = (float)$r['price'];
        else $raw = (float) str_replace([',','₱','$',' '], '', $r['price']);
    }
    $r['__price_raw'] = $raw;
    $products[$r['category']][] = $r;
}

// Group map: categories in your data mapped to logical builder groups
$group_map = [
  'motherboard' => ['asus','msi'],
  'cpu'         => ['intel','amd_ryzen'],
  'ram'         => ['corsair','g_skill'],
  'gpu'         => ['nvidia','amd_radeon'],
  'storage'     => ['crucial_ssd','samsung_ssd','wd_hdd','seagate_hdd'],
  'psu'         => ['seasonic','corsair_psu'],
  'case'        => ['nzxt','lian_li'],
  'cooler'      => ['noctua','cooler_master']
];

// --- Save DIY build if form submitted ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['products'])) {
    $json = $_POST['products'];
    $items = json_decode($json, true);
    if ($items && is_array($items)) {
        $total = 0.0;
        foreach ($items as $it) {
            $priceRaw = $it['price'] ?? 0;
            if (!is_numeric($priceRaw)) {
                $priceRaw = str_replace([',','₱','$',' '], '', (string)$priceRaw);
            }
            $price = (float)$priceRaw;
            $qty = isset($it['quantity']) ? (int)$it['quantity'] : 1;
            $total += $price * $qty;
        }
        $code = 'BUILD-' . strtoupper(bin2hex(random_bytes(3)));

        // attach user_id if available in session
        $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        if ($user_id) {
            $stmt = $conn->prepare("INSERT INTO diy_builds (build_code, products_json, total, user_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssdi", $code, $json, $total, $user_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO diy_builds (build_code, products_json, total) VALUES (?, ?, ?)");
            $stmt->bind_param("ssd", $code, $json, $total);
        }

        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();
            header("Location: payment.php?build_id=$id");
            exit;
        } else {
            $error_msg = $conn->error;
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Byte By Byte - DIY Builder</title>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="styles.css">

            <link rel="stylesheet" href="/project/testing/ai_style.css?v=<?= time(); ?>">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded"/>
<style>
:root{
  --accent: #7f00ff;
  --accent2: #9f3aff;
  --bg: transparent;
  --card-border: #466384;
  --hover-border: #5779a1;
  --selected-border: #5779a1;
  --h2-color: #5779a1;
  --muted: #5779a1;
  --hover-gray: #9aa7b6;
  --card-gap-top: 8px;
  --border-width: 3px;
}

/* base */


/* container & headings */
.container{max-width:1300px;margin:0 auto;padding:18px;overflow:visible}
h1,h2{text-align:center;margin:0 0 10px;font-weight:700}
h1{font-size:28px;color:#fff}
.step h2{color:var(--h2-color);display:none}

/* spacing so hover top border isn't clipped */
.step{margin-bottom:28px;padding-top:8px}

/* grid */
.product-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:14px;overflow:visible;padding-top:var(--card-gap-top)}
@media(max-width:1280px){.product-grid{grid-template-columns:repeat(4,1fr)}}
@media(max-width:900px){.product-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:480px){.product-grid{grid-template-columns:repeat(1,1fr)}}

/* card: border + padding so image doesn't touch border */
.card{
  position:relative;
  background:rgba(255,255,255,0.03);
  border-radius:12px;
  transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease,filter .18s ease,margin .18s ease;
  border: var(--border-width) solid var(--card-border);
  padding:12px;
  display:flex;
  flex-direction:column;
  justify-content:flex-start;
  overflow:visible;
  margin-top:var(--card-gap-top);
  will-change:transform,box-shadow;
}

/* image wrapper & image */
.card .img-wrap{width:100%;border-radius:8px;background:rgba(255,255,255,0.01);padding:8px;display:block;margin-bottom:10px}
.card img{width:100%;height:140px;max-height:180px;object-fit:contain;display:block;border-radius:6px;background:transparent}

/* reduced shadow strength for hover (was stronger before) */
.card:hover{
  transform:translateY(-6px);
  margin-top:0;
  border-color:var(--hover-border);
  /* reduced shadow */
  box-shadow:0 8px 18px rgba(0,0,0,0.28);
}

/* ensure gap exists so top border doesn't get clipped */
.product-grid > .card{margin-top:10px}
.card:hover{margin-top:0}

/* selected: darker visual + slightly increased border thickness */
.card.selected{
  filter:brightness(0.82);
  border: calc(var(--border-width) + 1px) solid var(--selected-border);
  /* reduced selected shadow */
  box-shadow:0 10px 22px rgba(0,0,0,0.38);
  transform:translateY(-4px);
  margin-top:0;
}

/* overlay fills entire card; appears on hover for non-selected */
.overlay{
  position:absolute;
  inset:0;
  background:rgba(0,0,0,0);
  display:flex;
  align-items:center;
  justify-content:center;
  gap:14px;
  opacity:0;
  pointer-events:none;
  transition:opacity .18s ease,background .18s ease;
  border-radius:12px;
  padding:12px;
}

/* hover overlay: subtle gray fill that covers whole product container */
.card:hover .overlay{
  opacity:1;
  pointer-events:auto;
  background: rgba(87,121,161,0.08);
}

/* When a card is selected hide the buttons/overlay */
.card.selected .overlay,
.card.selected .btn-detail,
.card.selected .btn-select {
  display:none !important;
  opacity:0 !important;
  pointer-events:none !important;
}

/* buttons (still visible on non-selected cards) */
.round-btn{
  width:48px;height:48px;border-radius:50%;border:none;color:#fff;
  font-weight:700;font-size:18px;display:flex;align-items:center;justify-content:center;cursor:pointer;
  /* softer button shadow */
  box-shadow:0 4px 10px rgba(0,0,0,0.22);
}

/* detail icon uses primary color */
.btn-detail{
  background: linear-gradient(180deg,var(--card-border), #3e5873);
  color:#fff;
  border:1px solid rgba(255,255,255,0.04);
}

/* select button remains green */
.btn-select{ background:linear-gradient(180deg,#39b54a,#2fa63d) }

/* meta */
.meta{padding:0 6px 6px;text-align:center}
.meta strong{display:block;font-size:14px;margin-bottom:6px;color:#fff}
.meta .price{color:var(--muted);font-weight:600}

/* rest unchanged... */
.slide{max-height:0;overflow:hidden;opacity:0;transform:translateY(-10px);transition:max-height .45s ease,opacity .35s ease,transform .35s ease}
.slide.active{max-height:2000px;opacity:1;transform:translateY(0)}
.modal{position:fixed;inset:0;background:rgba(0,0,0,0.78);display:none;align-items:center;justify-content:center;padding:20px;z-index:9999}
.modal.open{display:flex}
.modal .box{width:min(1000px,96%);background:rgba(18,18,20,0.98);border-radius:12px;padding:18px;display:flex;gap:16px;color:#fff}
.modal .left{flex:1}.modal .right{flex:1;display:flex;align-items:center;justify-content:center}
.modal img{max-width:100%;max-height:520px;border-radius:10px;object-fit:contain}
.spec-table{width:100%;border-collapse:collapse}
.spec-table td{padding:8px;border-bottom:1px solid rgba(255,255,255,0.06)}
.close{position:absolute;right:28px;top:18px;font-size:22px;cursor:pointer;color:#ccc}

.proceed-wrap{display:none;margin:30px auto 60px;text-align:center}
.proceed-wrap.active{display:block;animation:fadeInUp .35s ease}
.proceed-btn {
  background: linear-gradient(135deg, #5779a1 0%, #466384 100%);
  color: #fff;
  padding: 14px 28px;
  border: none;
  border-radius: 10px;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 8px 18px rgba(70, 99, 132, 0.25);
}

.proceed-btn:hover {
  background: linear-gradient(135deg, #466384 0%, #324d6a 100%);
  transform: translateY(-2px);
  filter: brightness(1.05);
}


.footer-note{color:#bbb;font-size:13px;margin-top:6px}
@keyframes fadeInUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}


html,body{margin:0;padding:0}               /* remove default browser gap */

/* container: remove top padding so hero image sits close to header */
.container{
  max-width:1300px;
  margin:0 auto;
  padding:0 18px 18px; /* left/right/bottom padding kept, top removed */
  overflow:visible;
}

/* hero (full-bleed h1) adjustments */
.container h1{
  background-image:url("Img/design/AdobeStock_421865104.jpeg");
  width:100vw;
  margin-left:calc(50% - 50vw);
  margin-top:0;            /* ensure no extra gap above */
  height:60vh;
  background-size:cover;
  background-position:center center;
  display:flex;
  align-items:center;
  justify-content:center;
  text-align:center;
  flex-direction:column;
  padding:14px;
  box-sizing:border-box;
  border-radius:0;
  overflow:hidden;
}

/* keep step spacing so hover border isn't clipped */
.step{margin-bottom:36px;padding-top:12px}

/* rest of your styles unchanged... */
/* Stock indicators */
.card { position: relative; }

/* Low stock badge */
.card.low-stock::after {
  content: attr(data-stock);
  position: absolute;
  top: 10px;
  right: 10px;
  background: #d9534f;
  color: #fff;
  font-weight: 700;
  font-size: 12px;
  line-height: 1;
  padding: 6px 8px;
  border-radius: 999px;
  z-index: 30;
  box-shadow: 0 4px 10px rgba(0,0,0,0.12);
  pointer-events: none;
  min-width: 28px;
  text-align: center;
}

/* Out of stock overlay */
.card.out-of-stock::before {
  content: "";
  position: absolute;
  inset: 0;
  background: rgba(10,12,16,0.16);
  border-radius: inherit;
  z-index: 40;
  pointer-events: auto;
}

.card.out-of-stock::after {
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
  z-index: 41;
  white-space: nowrap;
  box-shadow: 0 6px 18px rgba(0,0,0,0.14);
  border: 1px solid rgba(255,255,255,0.06);
  pointer-events: none;
}

.card.out-of-stock * {
  pointer-events: none;
  cursor: default !important;
  opacity: 0.98;
}

.card.out-of-stock .btn-detail,
.card.out-of-stock .btn-select {
  display: none !important;
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

<div class="container">
  <h1>DIY YOUR DREAM PC</h1>

  <!-- STEP: Motherboards -->
<div class="step" id="step-motherboard">
    <h2>Motherboards</h2>
    <div class="product-grid" id="grid-motherboard">
<?php
foreach ($products as $cat => $items) {
    if (!in_array($cat, $group_map['motherboard'])) continue;
    foreach ($items as $p) {
        $specs = htmlspecialchars($p['specs'], ENT_QUOTES | ENT_SUBSTITUTE);
        $desc  = htmlspecialchars($p['description'], ENT_QUOTES | ENT_SUBSTITUTE);
        $img   = htmlspecialchars($p['image_url'], ENT_QUOTES | ENT_SUBSTITUTE);
        $name  = htmlspecialchars($p['name'], ENT_QUOTES | ENT_SUBSTITUTE);
        $rawPrice = isset($p['__price_raw']) ? $p['__price_raw'] : 0;
        $displayPrice = number_format($rawPrice,2);

        // stock handling
        $stock = isset($p['stock']) ? (int)$p['stock'] : 0;
        $stockClass = $stock <= 0 ? 'out-of-stock' : ($stock < 10 ? 'low-stock' : '');

        echo <<<HTML
<div class="card {$stockClass}" data-id="{$p['id']}" data-category="{$cat}" data-name="{$name}" data-price="{$rawPrice}" data-image="{$img}" data-desc="{$desc}" data-specs='{$specs}' data-stock="{$stock}">
  <img src="{$img}" alt="{$name}">
  <div class="meta">
    <strong>{$name}</strong>
    <div class="price">₱{$displayPrice}</div>
  </div>
  <div class="overlay">
    <button class="round-btn btn-detail" title="Details">ℹ</button>
    <button class="round-btn btn-select" title="Select">✓</button>
  </div>
</div>
HTML;
    }
}
?>
    </div>
  </div>
<!-- ...existing code... -->
    </div>
  </div>

  <!-- STEP: CPU -->
  <div class="step" id="step-cpu">
    <h2>CPUs</h2>
    <div id="wrap-cpu" class="slide">
      <div class="product-grid" id="grid-cpu">
<?php
foreach ($products as $cat => $items) {
    if (!in_array($cat, $group_map['cpu'])) continue;
    foreach ($items as $p) {
        $specs = htmlspecialchars($p['specs'], ENT_QUOTES | ENT_SUBSTITUTE);
        $desc  = htmlspecialchars($p['description'], ENT_QUOTES | ENT_SUBSTITUTE);
        $img   = htmlspecialchars($p['image_url'], ENT_QUOTES | ENT_SUBSTITUTE);
        $name  = htmlspecialchars($p['name'], ENT_QUOTES | ENT_SUBSTITUTE);
        $rawPrice = isset($p['__price_raw']) ? $p['__price_raw'] : 0;
        $displayPrice = number_format($rawPrice,2);

        // stock handling (keeps UI/UX logic unchanged)
        $stock = isset($p['stock']) ? (int)$p['stock'] : 0;
        $stockClass = $stock <= 0 ? 'out-of-stock' : ($stock < 10 ? 'low-stock' : '');

        echo <<<HTML
<div class="card cpu-card {$stockClass}" data-id="{$p['id']}" data-category="{$cat}" data-name="{$name}" data-price="{$rawPrice}" data-image="{$img}" data-desc="{$desc}" data-specs='{$specs}' data-stock="{$stock}">
  <img src="{$img}" alt="{$name}">
  <div class="meta">
    <strong>{$name}</strong>
    <div class="price">₱{$displayPrice}</div>
  </div>
  <div class="overlay">
    <button class="round-btn btn-detail" title="Details">ℹ</button>
    <button class="round-btn btn-select" title="Select">✓</button>
  </div>
</div>
HTML;
    }
}
?>
      </div>
    </div>
  </div>
<!-- ...existing code... -->
      </div>
    </div>
  </div>

  <!-- STEP: RAM -->
  <div class="step" id="step-ram">
    <h2>RAM</h2>
    <div id="wrap-ram" class="slide">
      <div class="product-grid" id="grid-ram">
<?php
foreach ($products as $cat => $items) {
    if (!in_array($cat, $group_map['ram'])) continue;
    foreach ($items as $p) {
        $specs = htmlspecialchars($p['specs'], ENT_QUOTES | ENT_SUBSTITUTE);
        $desc  = htmlspecialchars($p['description'], ENT_QUOTES | ENT_SUBSTITUTE);
        $img   = htmlspecialchars($p['image_url'], ENT_QUOTES | ENT_SUBSTITUTE);
        $name  = htmlspecialchars($p['name'], ENT_QUOTES | ENT_SUBSTITUTE);
        $rawPrice = isset($p['__price_raw']) ? $p['__price_raw'] : 0;
        $displayPrice = number_format($rawPrice,2);

        // stock handling (keeps UI/UX logic unchanged)
        $stock = isset($p['stock']) ? (int)$p['stock'] : 0;
        $stockClass = $stock <= 0 ? 'out-of-stock' : ($stock < 10 ? 'low-stock' : '');

        echo <<<HTML
<div class="card ram-card {$stockClass}" data-id="{$p['id']}" data-category="{$cat}" data-name="{$name}" data-price="{$rawPrice}" data-image="{$img}" data-desc="{$desc}" data-specs='{$specs}' data-stock="{$stock}">
  <img src="{$img}" alt="{$name}">
  <div class="meta">
    <strong>{$name}</strong>
    <div class="price">₱{$displayPrice}</div>
  </div>
  <div class="overlay">
    <button class="round-btn btn-detail" title="Details">ℹ</button>
    <button class="round-btn btn-select" title="Select">✓</button>
  </div>
</div>
HTML;
    }
}
?>
      </div>
    </div>
  </div>

  <!-- STEP: GPU -->
  <div class="step" id="step-gpu">
    <h2>GPU</h2>
    <div id="wrap-gpu" class="slide">
      <div class="product-grid" id="grid-gpu">
<?php
foreach ($products as $cat => $items) {
    if (!in_array($cat, $group_map['gpu'])) continue;
    foreach ($items as $p) {
        $specs = htmlspecialchars($p['specs'], ENT_QUOTES | ENT_SUBSTITUTE);
        $desc  = htmlspecialchars($p['description'], ENT_QUOTES | ENT_SUBSTITUTE);
        $img   = htmlspecialchars($p['image_url'], ENT_QUOTES | ENT_SUBSTITUTE);
        $name  = htmlspecialchars($p['name'], ENT_QUOTES | ENT_SUBSTITUTE);
        $rawPrice = isset($p['__price_raw']) ? $p['__price_raw'] : 0;
        $displayPrice = number_format($rawPrice,2);

        // stock handling (keeps UI/UX logic unchanged)
        $stock = isset($p['stock']) ? (int)$p['stock'] : 0;
        $stockClass = $stock <= 0 ? 'out-of-stock' : ($stock < 10 ? 'low-stock' : '');

        echo <<<HTML
<div class="card gpu-card {$stockClass}" data-id="{$p['id']}" data-category="{$cat}" data-name="{$name}" data-price="{$rawPrice}" data-image="{$img}" data-desc="{$desc}" data-specs='{$specs}' data-stock="{$stock}">
  <img src="{$img}" alt="{$name}">
  <div class="meta">
    <strong>{$name}</strong>
    <div class="price">₱{$displayPrice}</div>
  </div>
  <div class="overlay">
    <button class="round-btn btn-detail" title="Details">ℹ</button>
    <button class="round-btn btn-select" title="Select">✓</button>
  </div>
</div>
HTML;
    }
}
?>
      </div>
    </div>
  </div>

  <!-- STEP: Storage -->
  <div class="step" id="step-storage">
    <h2>Storage (SSD / HDD)</h2>
    <div id="wrap-storage" class="slide">
      <div class="product-grid" id="grid-storage">
<?php
foreach ($products as $cat => $items) {
    if (!in_array($cat, $group_map['storage'])) continue;
    foreach ($items as $p) {
        $specs = htmlspecialchars($p['specs'], ENT_QUOTES | ENT_SUBSTITUTE);
        $desc  = htmlspecialchars($p['description'], ENT_QUOTES | ENT_SUBSTITUTE);
        $img   = htmlspecialchars($p['image_url'], ENT_QUOTES | ENT_SUBSTITUTE);
        $name  = htmlspecialchars($p['name'], ENT_QUOTES | ENT_SUBSTITUTE);
        $rawPrice = isset($p['__price_raw']) ? $p['__price_raw'] : 0;
        $displayPrice = number_format($rawPrice,2);

        // stock handling (keeps UI/UX logic unchanged)
        $stock = isset($p['stock']) ? (int)$p['stock'] : 0;
        $stockClass = $stock <= 0 ? 'out-of-stock' : ($stock < 10 ? 'low-stock' : '');

        echo <<<HTML
<div class="card storage-card {$stockClass}" data-id="{$p['id']}" data-category="{$cat}" data-name="{$name}" data-price="{$rawPrice}" data-image="{$img}" data-desc="{$desc}" data-specs='{$specs}' data-stock="{$stock}">
  <img src="{$img}" alt="{$name}">
  <div class="meta">
    <strong>{$name}</strong>
    <div class="price">₱{$displayPrice}</div>
  </div>
  <div class="overlay">
    <button class="round-btn btn-detail" title="Details">ℹ</button>
    <button class="round-btn btn-select" title="Select">✓</button>
  </div>
</div>
HTML;
    }
}
?>
      </div>
    </div>
  </div>

  <!-- STEP: PSU -->
  <div class="step" id="step-psu">
    <h2>Power Supply (PSU)</h2>
    <div id="wrap-psu" class="slide">
      <div class="product-grid" id="grid-psu">
<?php
foreach ($products as $cat => $items) {
    if (!in_array($cat, $group_map['psu'])) continue;
    foreach ($items as $p) {
        $specs = htmlspecialchars($p['specs'], ENT_QUOTES | ENT_SUBSTITUTE);
        $desc  = htmlspecialchars($p['description'], ENT_QUOTES | ENT_SUBSTITUTE);
        $img   = htmlspecialchars($p['image_url'], ENT_QUOTES | ENT_SUBSTITUTE);
        $name  = htmlspecialchars($p['name'], ENT_QUOTES | ENT_SUBSTITUTE);
        $rawPrice = isset($p['__price_raw']) ? $p['__price_raw'] : 0;
        $displayPrice = number_format($rawPrice,2);

        // stock handling (keeps UI/UX logic unchanged)
        $stock = isset($p['stock']) ? (int)$p['stock'] : 0;
        $stockClass = $stock <= 0 ? 'out-of-stock' : ($stock < 10 ? 'low-stock' : '');

        echo <<<HTML
<div class="card psu-card {$stockClass}" data-id="{$p['id']}" data-category="{$cat}" data-name="{$name}" data-price="{$rawPrice}" data-image="{$img}" data-desc="{$desc}" data-specs='{$specs}' data-stock="{$stock}">
  <img src="{$img}" alt="{$name}">
  <div class="meta">
    <strong>{$name}</strong>
    <div class="price">₱{$displayPrice}</div>
  </div>
  <div class="overlay">
    <button class="round-btn btn-detail" title="Details">ℹ</button>
    <button class="round-btn btn-select" title="Select">✓</button>
  </div>
</div>
HTML;
    }
}
?>
      </div>
    </div>
  </div>

  <!-- STEP: Case -->
  <div class="step" id="step-case">
    <h2>Case</h2>
    <div id="wrap-case" class="slide">
      <div class="product-grid" id="grid-case">
<?php
foreach ($products as $cat => $items) {
    if (!in_array($cat, $group_map['case'])) continue;
    foreach ($items as $p) {
        $specs = htmlspecialchars($p['specs'], ENT_QUOTES | ENT_SUBSTITUTE);
        $desc  = htmlspecialchars($p['description'], ENT_QUOTES | ENT_SUBSTITUTE);
        $img   = htmlspecialchars($p['image_url'], ENT_QUOTES | ENT_SUBSTITUTE);
        $name  = htmlspecialchars($p['name'], ENT_QUOTES | ENT_SUBSTITUTE);
        $rawPrice = isset($p['__price_raw']) ? $p['__price_raw'] : 0;
        $displayPrice = number_format($rawPrice,2);

        // stock handling (keeps UI/UX logic unchanged)
        $stock = isset($p['stock']) ? (int)$p['stock'] : 0;
        $stockClass = $stock <= 0 ? 'out-of-stock' : ($stock < 10 ? 'low-stock' : '');

        echo <<<HTML
<div class="card case-card {$stockClass}" data-id="{$p['id']}" data-category="{$cat}" data-name="{$name}" data-price="{$rawPrice}" data-image="{$img}" data-desc="{$desc}" data-specs='{$specs}' data-stock="{$stock}">
  <img src="{$img}" alt="{$name}">
  <div class="meta">
    <strong>{$name}</strong>
    <div class="price">₱{$displayPrice}</div>
  </div>
  <div class="overlay">
    <button class="round-btn btn-detail" title="Details">ℹ</button>
    <button class="round-btn btn-select" title="Select">✓</button>
  </div>
</div>
HTML;
    }
}
?>
      </div>
    </div>
  </div>

  <!-- STEP: Cooler -->
  <div class="step" id="step-cooler">
    <h2>CPU Cooler</h2>
    <div id="wrap-cooler" class="slide">
      <div class="product-grid" id="grid-cooler">
<?php
foreach ($products as $cat => $items) {
    if (!in_array($cat, $group_map['cooler'])) continue;
    foreach ($items as $p) {
        $specs = htmlspecialchars($p['specs'], ENT_QUOTES | ENT_SUBSTITUTE);
        $desc  = htmlspecialchars($p['description'], ENT_QUOTES | ENT_SUBSTITUTE);
        $img   = htmlspecialchars($p['image_url'], ENT_QUOTES | ENT_SUBSTITUTE);
        $name  = htmlspecialchars($p['name'], ENT_QUOTES | ENT_SUBSTITUTE);
        $rawPrice = isset($p['__price_raw']) ? $p['__price_raw'] : 0;
        $displayPrice = number_format($rawPrice,2);

        // stock handling (keeps UI/UX logic unchanged)
        $stock = isset($p['stock']) ? (int)$p['stock'] : 0;
        $stockClass = $stock <= 0 ? 'out-of-stock' : ($stock < 10 ? 'low-stock' : '');

        echo <<<HTML
<div class="card cooler-card {$stockClass}" data-id="{$p['id']}" data-category="{$cat}" data-name="{$name}" data-price="{$rawPrice}" data-image="{$img}" data-desc="{$desc}" data-specs='{$specs}' data-stock="{$stock}">
  <img src="{$img}" alt="{$name}">
  <div class="meta">
    <strong>{$name}</strong>
    <div class="price">₱{$displayPrice}</div>
  </div>
  <div class="overlay">
    <button class="round-btn btn-detail" title="Details">ℹ</button>
    <button class="round-btn btn-select" title="Select">✓</button>
  </div>
</div>
HTML;
    }
}
?>
      </div>
    </div>
  </div>

  <!-- Proceed area -->
  <div class="proceed-wrap" id="proceed-wrap">
    <form id="proceedForm" action="diy.php" method="POST">
      <input type="hidden" name="products" id="productsInput" value="">
      <button type="submit" class="proceed-btn">Proceed to Payment</button>
    </form>
  </div>
</div>

<!-- Modal -->
<div id="modal" class="modal" role="dialog" aria-hidden="true">
  <div class="box">
    <div class="left">
      <h3 id="md-name"></h3>
      <p id="md-desc" style="color:#ccc"></p>
      <table class="spec-table" id="md-specs"></table>
    </div>
    <div class="right">
      <img id="md-img" src="" alt="">
    </div>
    <div class="close" onclick="closeModal()">✖</div>
  </div>
</div>

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

<script>
// Helper parsing & detection
function safeParseSpecs(str){
  if(!str) return {};
  try { return JSON.parse(str); }
  catch(e){
    const obj = {};
    str.split(/\r?\n/).forEach(line=>{
      const m = line.match(/^\s*([^:]+)\s*:\s*(.+)$/);
      if(m){ obj[m[1].trim()] = m[2].trim(); }
    });
    return obj;
  }
}
function detectSocket(specs, name){
  if(!specs) return null;
  if(specs.Socket) return specs.Socket;
  if(specs['CPU Socket']) return specs['CPU Socket'];
  const up = (name||'').toUpperCase();
  const m = up.match(/\b(LGA\d{3,4}|AM[45]|AM3|AM4|AM5)\b/);
  return m ? m[0] : null;
}
function detectMemoryType(specs, name){
  const candidates = [];
  if(specs['Memory Support']) candidates.push(specs['Memory Support']);
  if(specs['Memory']) candidates.push(specs['Memory']);
  if(specs['Memory Type']) candidates.push(specs['Memory Type']);
  const joined = (candidates.join(' ') + ' ' + (name||'')).toUpperCase();
  if(joined.includes('DDR5')) return 'DDR5';
  if(joined.includes('DDR4')) return 'DDR4';
  return null;
}
function parseW(str){
  if(!str) return 0;
  const m = (''+str).match(/(\d+)\s*W/i);
  if(m) return parseInt(m[1],10);
  const m2 = (''+str).match(/(\d{3,4})/);
  return m2 ? parseInt(m2[1],10) : 0;
}
function shuffleArray(arr){
  for(let i = arr.length - 1; i > 0; i--){
    const j = Math.floor(Math.random() * (i + 1));
    [arr[i], arr[j]] = [arr[j], arr[i]];
  }
  return arr;
}

/*
  applyPriorityOrdering(nodes, scoreFn, opts)
  - scoreFn(card) -> 2 (best compatible), 1 (partial), 0 (not compatible)
  - opts.restCount: number of random lower-scored items to include (default 1)
  Behavior: show best then partial then opts.restCount random from score 0 group.
*/
function applyPriorityOrdering(nodes, scoreFn, opts){
  opts = opts || {};
  const restCount = typeof opts.restCount === 'number' ? opts.restCount : 1;
  const cards = Array.from(nodes);
  if(cards.length === 0) return;
  const scored = cards.map(c => ({card:c, score: scoreFn(c)}));
  const best = scored.filter(s=>s.score===2).map(s=>s.card);
  const partial = scored.filter(s=>s.score===1).map(s=>s.card);
  let rest = scored.filter(s=>s.score<=0).map(s=>s.card);
  shuffleArray(partial);
  shuffleArray(rest);
  const chosenRest = rest.slice(0, restCount);
  const ordered = best.concat(partial, chosenRest);
  // hide those not chosen
  const chosenSet = new Set(ordered);
  const parent = cards[0].parentNode;
  // first hide all
  cards.forEach(c => c.style.display = 'none');
  // append chosen in order and show
  ordered.forEach(c => { c.style.display = ''; parent.appendChild(c); });
}

/* modal logic */
const modal = document.getElementById('modal');
function openModal(card){
  const name = card.dataset.name || '';
  const desc = card.dataset.desc || '';
  const img  = card.dataset.image || '';
  const specs = safeParseSpecs(card.dataset.specs || '');

  document.getElementById('md-name').textContent = name;
  document.getElementById('md-desc').textContent = desc;
  document.getElementById('md-img').src = img;
  const table = document.getElementById('md-specs');
  table.innerHTML = '';
  if(Object.keys(specs).length === 0){
    table.innerHTML = '<tr><td>No structured specs available.</td></tr>';
  } else {
    for(const k of Object.keys(specs)){
      const tr = document.createElement('tr');
      tr.innerHTML = '<td style="width:36%"><strong>' + k + '</strong></td><td>' + specs[k] + '</td>';
      table.appendChild(tr);
    }
  }
  modal.classList.add('open');
}
function closeModal(){ modal.classList.remove('open'); }
modal.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });

function revealStepTitleForWrap(wrap) {
  try {
    const step = wrap.closest('.step');
    if(step) {
      const h2 = step.querySelector('h2');
      if(h2) h2.style.display = 'block';
    }
  } catch(e){}
}

function attach(root=document){
  root.querySelectorAll('.btn-detail').forEach(b=>{
    if(b._attached) return; b._attached = true;
    b.addEventListener('click', e=>{
      e.stopPropagation();
      openModal(e.target.closest('.card'));
    });
  });

  root.querySelectorAll('.btn-select').forEach(b=>{
    if(b._attached) return; b._attached = true;
    b.addEventListener('click', e=>{
      e.stopPropagation();
      const card = e.target.closest('.card');

      const g = card.closest('.product-grid');
      g.querySelectorAll('.card').forEach(c=>c.classList.remove('selected'));
      card.classList.add('selected');

      if(card.closest('#grid-motherboard')) onMotherboardSelected(card);
      else if(card.classList.contains('cpu-card')) onCpuSelected(card);
      else if(card.classList.contains('ram-card')) onRamSelected(card);
      else if(card.classList.contains('gpu-card')) onGpuSelected(card);
      else if(card.classList.contains('storage-card')) onStorageSelected(card);
      else if(card.classList.contains('psu-card')) onPsuSelected(card);
      else if(card.classList.contains('case-card')) onCaseSelected(card);
      else if(card.classList.contains('cooler-card')) onCoolerSelected(card);
    });
  });
}

/* Step handlers using priority ordering */
function onMotherboardSelected(card){
  const mbSpecs = safeParseSpecs(card.dataset.specs || '');
  const mbSocket = detectSocket(mbSpecs, card.dataset.name);
  const mbMemory = detectMemoryType(mbSpecs, card.dataset.name);

  const wrapCpu = document.getElementById('wrap-cpu');
  wrapCpu.classList.add('active');
  revealStepTitleForWrap(wrapCpu);

  const cpuNodes = document.querySelectorAll('.cpu-card');
  applyPriorityOrdering(cpuNodes, function(c){
    const cs = safeParseSpecs(c.dataset.specs || '');
    const csock = detectSocket(cs, c.dataset.name);
    if(mbSocket && csock && csock.toUpperCase() === mbSocket.toUpperCase()) return 2;
    const mbIsAMD = /AM[345]|B\d|X\d/i.test(card.dataset.name);
    const cpuIsAMD = /RYZEN|AMD/i.test(c.dataset.name);
    if(mbIsAMD === cpuIsAMD) return 1;
    return 0;
  }, { restCount: 1 });

  setTimeout(()=> wrapCpu.scrollIntoView({behavior:'smooth', block:'center'}), 120);
  attach(document.getElementById('wrap-cpu'));
}

function onCpuSelected(card){
  const cpuSpecs = safeParseSpecs(card.dataset.specs || '');
  let mem = detectMemoryType(cpuSpecs, card.dataset.name);
  if(!mem){
    const selMb = document.querySelector('#grid-motherboard .card.selected');
    if(selMb) mem = detectMemoryType(safeParseSpecs(selMb.dataset.specs || ''), selMb.dataset.name);
  }
  const wrapRam = document.getElementById('wrap-ram');
  wrapRam.classList.add('active');
  revealStepTitleForWrap(wrapRam);

  const ramNodes = document.querySelectorAll('.ram-card');
  applyPriorityOrdering(ramNodes, function(r){
    const rs = safeParseSpecs(r.dataset.specs || '');
    const rmem = detectMemoryType(rs, r.dataset.name);
    if(mem && rmem && rmem.toUpperCase() === mem.toUpperCase()) return 2;
    if(mem && r.dataset.name.toUpperCase().includes(mem.toUpperCase())) return 1;
    return 0;
  }, { restCount: 1 });

  setTimeout(()=> wrapRam.scrollIntoView({behavior:'smooth', block:'center'}), 120);
  attach(document.getElementById('wrap-ram'));
}

function onRamSelected(card){
  const wrapGpu = document.getElementById('wrap-gpu');
  wrapGpu.classList.add('active');
  revealStepTitleForWrap(wrapGpu);

  const gpuNodes = document.querySelectorAll('.gpu-card');
  // randomize GPUs but leave UI compact: show a few randomized
  const arr = Array.from(gpuNodes);
  shuffleArray(arr);
  const parent = arr.length?arr[0].parentNode:null;
  arr.forEach((g,i)=>{ g.style.display = (i < Math.min(8, arr.length)) ? '' : 'none'; if(parent) parent.appendChild(g); });

  setTimeout(()=> wrapGpu.scrollIntoView({behavior:'smooth', block:'center'}), 120);
  attach(document.getElementById('wrap-gpu'));
}

function onGpuSelected(card){
  const wrapStorage = document.getElementById('wrap-storage');
  wrapStorage.classList.add('active');
  revealStepTitleForWrap(wrapStorage);
  setTimeout(()=> wrapStorage.scrollIntoView({behavior:'smooth', block:'center'}), 120);
  attach(document.getElementById('wrap-storage'));
}

function probableTdp(card){
  const s = safeParseSpecs(card.dataset.specs || '');
  if(s['TDP']) return s['TDP'];
  if(s['Power Draw']) return s['Power Draw'];
  return card.dataset.specs || card.dataset.name || '';
}

function onStorageSelected(card){
  let cpuCard = document.querySelector('.cpu-card.selected') || document.querySelector('#grid-cpu .card.selected');
  let gpuCard = document.querySelector('.gpu-card.selected') || document.querySelector('#grid-gpu .card.selected');
  const cpuTdp = cpuCard ? parseW(probableTdp(cpuCard)) : 0;
  const gpuTdp = gpuCard ? parseW(probableTdp(gpuCard)) : 0;
  const required = (cpuTdp + gpuTdp + 200);

  const wrapPsu = document.getElementById('wrap-psu');
  wrapPsu.classList.add('active');
  revealStepTitleForWrap(wrapPsu);

  const psuNodes = document.querySelectorAll('.psu-card');
  applyPriorityOrdering(psuNodes, function(p){
    const pSpecs = safeParseSpecs(p.dataset.specs || '');
    let watt = 0;
    if(pSpecs['Wattage']) watt = parseW(pSpecs['Wattage']);
    else if(pSpecs['Wattage (W)']) watt = parseW(pSpecs['Wattage (W)']);
    else watt = parseW(p.dataset.name);
    if(watt >= required) return 2;
    if(watt >= required - 150) return 1;
    return 0;
  }, { restCount: 1 });

  setTimeout(()=> wrapPsu.scrollIntoView({behavior:'smooth', block:'center'}), 120);
  attach(document.getElementById('wrap-psu'));
}

function onPsuSelected(card){
  const selMb = document.querySelector('#grid-motherboard .card.selected');
  let mbForm = null;
  if(selMb){
    const ms = safeParseSpecs(selMb.dataset.specs || '');
    mbForm = ms['Form Factor'] || ms['Form'] || null;
    if(mbForm) mbForm = mbForm.split(',')[0].trim();
  }
  const wrapCase = document.getElementById('wrap-case');
  wrapCase.classList.add('active');
  revealStepTitleForWrap(wrapCase);

  const caseNodes = document.querySelectorAll('.case-card');
  applyPriorityOrdering(caseNodes, function(c){
    if(!mbForm) return 0;
    const cs = safeParseSpecs(c.dataset.specs || '');
    const support = (cs['Motherboard Support'] || cs['Supported MB'] || '').toUpperCase();
    if(support && support.includes(mbForm.toUpperCase())) return 2;
    if(c.dataset.name.toUpperCase().includes(mbForm.toUpperCase()) || (support && support.includes('ATX'))) return 1;
    return 0;
  }, { restCount: 1 });

  setTimeout(()=> wrapCase.scrollIntoView({behavior:'smooth', block:'center'}), 120);
  attach(document.getElementById('wrap-case'));
}

function onCaseSelected(card){
  let cpuSocket = null;
  const selCpu = document.querySelector('#grid-cpu .card.selected');
  if(selCpu) cpuSocket = detectSocket(safeParseSpecs(selCpu.dataset.specs||''), selCpu.dataset.name);
  const wrapCooler = document.getElementById('wrap-cooler');
  wrapCooler.classList.add('active');
  revealStepTitleForWrap(wrapCooler);

  const coolerNodes = document.querySelectorAll('.cooler-card');
  applyPriorityOrdering(coolerNodes, function(c){
    const cs = safeParseSpecs(c.dataset.specs||'');
    const supports = (cs['Supported Sockets'] || cs['Compatibility'] || cs['Socket Support'] || '').toUpperCase();
    if(!cpuSocket) return 0;
    if(supports && supports.includes(cpuSocket.toUpperCase())) return 2;
    if(c.dataset.name.toUpperCase().includes(cpuSocket.toUpperCase())) return 1;
    return 0;
  }, { restCount: 1 });

  setTimeout(()=> wrapCooler.scrollIntoView({behavior:'smooth', block:'center'}), 120);
  attach(document.getElementById('wrap-cooler'));
}

function onCoolerSelected(card){
  showProceedArea();
  setTimeout(()=> {
    document.getElementById('proceed-wrap').scrollIntoView({behavior:'smooth', block:'center'});
  }, 120);
}

function collectSelectedProducts(){
  const selectedCards = Array.from(document.querySelectorAll('.card.selected'));
  const items = selectedCards.map(c => ({
    id: parseInt(c.dataset.id, 10) || null,
    name: c.dataset.name || '',
    price: parseFloat((c.dataset.price || '0').toString().replace(/,/g, '')) || 0,
    category: c.dataset.category || '',
    image_url: c.dataset.image || '',
    description: c.dataset.desc || '',
    specs: c.dataset.specs || '',
    quantity: 1
  }));
  return items;
}

function showProceedArea(){
  const wrap = document.getElementById('proceed-wrap');
  const input = document.getElementById('productsInput');
  const items = collectSelectedProducts();
  input.value = JSON.stringify(items);

  const existing = document.querySelectorAll('#proceedForm input[name="selected[]"]');
  existing.forEach(e => e.remove());
  items.forEach(it => {
    const i = document.createElement('input');
    i.type = 'hidden';
    i.name = 'selected[]';
    i.value = it.id;
    document.getElementById('proceedForm').appendChild(i);
  });

  wrap.classList.add('active');
}

document.getElementById('proceedForm').addEventListener('submit', function(e){
  const items = collectSelectedProducts();
  if(items.length === 0){
    e.preventDefault();
    alert('Please complete the build by selecting parts before proceeding to payment.');
    return false;
  }
  return true;
});

attach(document);

document.addEventListener('DOMContentLoaded', ()=>{
  attach();
  const mbStep = document.getElementById('step-motherboard');
  if(mbStep){ const h2 = mbStep.querySelector('h2'); if(h2) h2.style.display='block'; }
});

// Add inside the existing attach() function 
// ...existing code...
function attach(root=document){
  // existing button wiring (unchanged)
  root.querySelectorAll('.btn-detail').forEach(b=>{
    if(b._attached) return; b._attached = true;
    b.addEventListener('click', e=>{
      e.stopPropagation();
      openModal(e.target.closest('.card'));
    });
  });

  root.querySelectorAll('.btn-select').forEach(b=>{
    if(b._attached) return; b._attached = true;
    b.addEventListener('click', e=>{
      e.stopPropagation();
      const card = e.target.closest('.card');

      // prevent selecting out-of-stock here as a safety check
      if(card && card.classList.contains('out-of-stock')) {
        return;
      }

      const g = card.closest('.product-grid');
      g.querySelectorAll('.card').forEach(c=>c.classList.remove('selected'));
      card.classList.add('selected');

      if(card.closest('#grid-motherboard')) onMotherboardSelected(card);
      else if(card.classList.contains('cpu-card')) onCpuSelected(card);
      else if(card.classList.contains('ram-card')) onRamSelected(card);
      else if(card.classList.contains('gpu-card')) onGpuSelected(card);
      else if(card.classList.contains('storage-card')) onStorageSelected(card);
      else if(card.classList.contains('psu-card')) onPsuSelected(card);
      else if(card.classList.contains('case-card')) onCaseSelected(card);
      else if(card.classList.contains('cooler-card')) onCoolerSelected(card);
    });
  });

  // --- STOCK HANDLING (merged here) ---
  root.querySelectorAll('.card').forEach(card => {
    const stockAttr = card.getAttribute('data-stock');
    if (stockAttr == null) return;

    const stock = parseInt(stockAttr, 10);
    if (isNaN(stock)) return;

    if (stock <= 0) {
      // mark out-of-stock, disable buttons
      card.classList.add('out-of-stock');
      card.querySelectorAll('.btn-detail, .btn-select').forEach(btn => {
        btn.disabled = true;
        btn.setAttribute('aria-disabled', 'true');
      });
    } else {
      // ensure active items are interactive
      card.classList.remove('out-of-stock');
      card.querySelectorAll('.btn-detail, .btn-select').forEach(btn => {
        btn.disabled = false;
        btn.removeAttribute('aria-disabled');
      });
    }
  });

  // one global protection: clicks inside an out-of-stock card do nothing
  // (keeps previous behavior but safety check in handlers prevents blocking in-stock)
  // attach this listener only once
  if (!attach._stockClickGuardAttached) {
    document.addEventListener('click', (e) => {
      const card = e.target.closest('.card');
      if (card && card.classList.contains('out-of-stock')) {
        e.preventDefault();
        e.stopPropagation();
      }
    }, true);
    attach._stockClickGuardAttached = true;
  }
}
// ...existing code...
</script>
        <script src="/project/testing/ai_script.js?v=<?= time(); ?>"></script>
</body>
</html>