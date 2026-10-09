<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];

    if ($quantity < 1) $quantity = 1; // enforce minimum 1

    // update quantity
    $sql = "UPDATE cart SET quantity = $quantity WHERE id = $cart_id";
    if ($conn->query($sql) === TRUE) {
        // get updated data for response
        $result = $conn->query("
            SELECT c.quantity, s.price 
            FROM cart c 
            JOIN shop s ON c.product_id = s.id 
            WHERE c.id = $cart_id
        ");

        if ($result && $row = $result->fetch_assoc()) {
            $subtotal = $row['quantity'] * $row['price'];
            echo json_encode([
                "success"  => true,
                "subtotal" => number_format($subtotal, 2),
                "price"    => number_format($row['price'], 2),
                "quantity" => $row['quantity']
            ]);
        } else {
            echo json_encode(["success" => false, "error" => "Cart item not found"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
}
$conn->close();
?>
