<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id > 0) {
    $sql = "UPDATE orders SET status='cancelled' WHERE id=$order_id";
    if ($conn->query($sql)) {
        header("Location: track_orders.php?cancelled=1");
        exit;
    } else {
        header("Location: track_orders.php?cancelled=0");
        exit;
    }
} else {
    header("Location: track_orders.php?cancelled=0");
    exit;
}
?>
