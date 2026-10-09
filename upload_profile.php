<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "user_db";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("DB Error: " . $conn->connect_error); }

if (empty($_SESSION['user_id'])) {
    die("Error: No user logged in.");
}
$user_id = $_SESSION['user_id'];

if (!empty($_FILES['profile_img']['tmp_name'])) {
    $upload_dir = __DIR__ . "/uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($_FILES['profile_img']['type'], $allowed_types)) {
        die("Error: Only JPG, PNG, and GIF files are allowed.");
    }

    // Validate size (2MB)
    if ($_FILES['profile_img']['size'] > 2 * 1024 * 1024) {
        die("Error: File too large. Max size is 2MB.");
    }

    $filename = time() . '_' . basename($_FILES['profile_img']['name']);
    $path = "uploads/" . $filename; // saved in DB (relative)

    if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $upload_dir . $filename)) {
        $stmt = $conn->prepare("UPDATE users SET profile_image=? WHERE id=?");
        $stmt->bind_param("si", $path, $user_id);

        if ($stmt->execute()) {
            $_SESSION['msg'] = "Profile updated successfully!";
            header("Location: profile.php");
            exit;
        } else {
            die("DB Update failed: " . $stmt->error);
        }
    } else {
        die("File move failed (check uploads/ folder permissions).");
    }
} else {
    die("No file uploaded.");
}
?>
