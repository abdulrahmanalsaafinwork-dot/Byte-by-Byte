<?php
$host     = "localhost";
$user     = "root";
$password = "";
$database = "user_db";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* -------------------------------------------------
   ADMIN LOGIN CONFIG
   Replace ADMIN_EMAIL with the email you want.
   Replace ADMIN_PASS_HASH with a hash generated
   from your own password (see note below).
---------------------------------------------------*/
// --- Admin credentials ---
define('ADMIN_EMAIL', 'admin@gmail.com');
define('ADMIN_PASS_HASH', password_hash('admin123', PASSWORD_DEFAULT));