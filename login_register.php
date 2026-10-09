<?php
session_start();
require_once 'config.php';

// REGISTER
if (isset($_POST['register'])) {
    $name      = $_POST['name'];
    $email     = $_POST['email'];
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $number    = $_POST['number'];
    $address   = $_POST['address'];
    $gender    = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $age       = $_POST['age'];

    $_SESSION['old'] = $_POST; // keep inputs

    // ✅ Server-side validation: must be 13+
    $calculatedAge = (new DateTime())->diff(new DateTime($birthdate))->y;
    if ($calculatedAge < 13) {
        $_SESSION['register_error'] = '⚠️ You must be at least 13 years old to register.';
        $_SESSION['active_form'] = 'register';
        header("Location: login.php");
        exit();
    }

    $checkEmail = $conn->query("SELECT email FROM users WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = '⚠️ Email is already registered!';
        $_SESSION['active_form'] = 'register';
    } else {
        $conn->query("INSERT INTO users (name, email, password, phone, address, gender, birthdate, age) 
                      VALUES ('$name', '$email', '$password', '$number', '$address', '$gender', '$birthdate', '$calculatedAge')");
        $_SESSION['active_form'] = 'login';
        unset($_SESSION['old']); // clear inputs
    }

    header("Location: login.php");
    exit();
}

// LOGIN
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
if (password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];   // 👈 Save user ID for cart
    $_SESSION['name']    = $user['name'];
    $_SESSION['email']   = $user['email'];
    unset($_SESSION['old']);
    header("Location: index.php");
    exit();
}
    }

    $_SESSION['login_error'] = '⚠️ Incorrect email or password';
    $_SESSION['active_form'] = 'login';
    $_SESSION['old'] = $_POST;
    header("Location: login.php");
    exit();
}
?>
