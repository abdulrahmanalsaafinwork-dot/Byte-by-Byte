<?php
// remove_from_cart.php
include 'config.php';
session_start();

// make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $cart_id = (int) $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // delete only this user's cart item
    $sql = "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id";

    if ($conn->query($sql) === TRUE) {
        // redirect back to cart page
        header("Location: cart.php?msg=removed");
        exit();
    } else {
        echo "❌ Error deleting item: " . $conn->error;
    }
} else {
    echo "❌ Invalid request.";
}

$conn->close();
?>
