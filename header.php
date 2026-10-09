<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php'; // DB connection

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);

$email  = $conn->real_escape_string($_SESSION['email']);
$result = $conn->query("SELECT profile_image FROM users WHERE email='$email'");
$user   = $result->fetch_assoc();

$profileImage = !empty($user['profile_image'])
    ? $user['profile_image']
    : "Icons/AdobeStock_692347763.png";

// ✅ Add all pages that should highlight “Profile”
$profile_pages = [
    'profile.php',
    'change_address.php',
    'change_password.php',
    'change_phone.php',
    'track_orders.php',
    'stracking.php'
];

// Decide logo link based on current page
switch ($current_page) {
    case 'index.php':
        $logo_link = 'index.php';
        break;
    case 'shop.php':
    case 'sproduct.php':
        $logo_link = 'shop.php';
        break;
    case 'about.php':
        $logo_link = 'about.php';
        break;
    case 'contact.php':
        $logo_link = 'contact.php';
        break;
    case 'cart.php':
    case 'payment.php': // ✅ Added this line
        $logo_link = 'cart.php';
        break;
    default:
        $logo_link = 'index.php';
        break;
}
?>

<!-- Navbar / Header -->
<section id="header">
    <a href="<?php echo $logo_link; ?>">
        <img src="Icons/free__1_-removebg-preview.png" class="logo" alt="Logo">
    </a>
    <div>
        <ul id="navbar">
            <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a></li>
            <li><a href="shop.php" class="<?php echo ($current_page == 'shop.php' || $current_page == 'sproduct.php') ? 'active' : ''; ?>">Shop</a></li>
            <li><a href="diy.php" class="<?php echo $current_page == 'diy.php' ? 'active' : ''; ?>">DIY</a></li>
            <li><a href="about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">About</a></li>
            <li><a href="contact.php" class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
            <li>
                <!-- ✅ Cart active for both cart.php and payment.php -->
                <a href="cart.php" class="<?php echo ($current_page == 'cart.php' || $current_page == 'payment.php') ? 'active' : ''; ?>">
                    <i class="fa fa-shopping-cart"></i>
                </a>
            </li>
            <li class="profile-menu">
                <img src="<?= htmlspecialchars($profileImage) ?>" 
                     alt="Profile" 
                     class="profile-pic <?php echo in_array($current_page, $profile_pages) ? 'active-profile' : ''; ?>" 
                     id="userIcon">
                <div id="dropdown" class="dropdown-content">
                    <a href="profile.php" class="<?php echo in_array($current_page, $profile_pages) ? 'active' : ''; ?>">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </li>
        </ul>
    </div>
</section>
