<?php
session_start();

$errors = [
  'login' => $_SESSION['login_error'] ?? '',
  'register' => $_SESSION['register_error'] ?? ''
];

$old = $_SESSION['old'] ?? [];
$activeForm = $_SESSION['active_form'] ?? 'login';

session_unset();

function showError($error) {
  return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($formName, $activeForm) {
  return $formName == $activeForm ? 'active' : '';
}

function old($key, $old) {
  return isset($old[$key]) ? htmlspecialchars($old[$key]) : '';
}

$maxBirthdate = date("Y-m-d", strtotime("-13 years"));
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">

  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Byte By Byte</title>
  <link rel="stylesheet" href="styles.css?v=<?= time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
  <div class="login-bg"></div>

  <section id="login">
    <div class="container">

      <!-- LOGIN FORM -->
      <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
        <form action="login_register.php" method="post">
          <h2>Login</h2>
          <?= showError($errors['login']); ?>

          <div class="input-container">
            <input type="email" name="email" placeholder="Email"
                   value="<?= old('email', $old); ?>" required>
            <button type="button" class="icon-btn"><i class="fa fa-user"></i></button>
          </div>

          <div class="input-container">
            <input type="password" name="password" placeholder="Password" required>
            <button type="button" class="icon-btn password-toggle"><i class="fa fa-lock"></i></button>
          </div>

          <button type="submit" name="login">Login</button>
          <p class="asking">
            Don't have an account?
            <a href="#" onclick="showform('register-form')">Register</a>
          </p>
        </form>
      </div>

      <!-- REGISTER FORM -->
      <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
        <form action="login_register.php" method="post">
          <h2>Register</h2>
          <?= showError($errors['register']); ?>

          <div class="input-container">
            <input type="text" name="name" placeholder="Name"
                   value="<?= old('name', $old); ?>" required>
            <button type="button" class="icon-btn"><i class="fa fa-user"></i></button>
          </div>

          <div class="input-container">
            <input type="email" name="email" placeholder="Email"
                   value="<?= old('email', $old); ?>" required>
            <button type="button" class="icon-btn"><i class="fa fa-envelope"></i></button>
          </div>

          <div class="input-container">
            <input type="password" name="password" placeholder="Password" required>
            <button type="button" class="icon-btn password-toggle"><i class="fa fa-lock"></i></button>
          </div>

          <div class="input-container">
            <input type="text" id="phone" name="number" placeholder="Phone Number"
                   value="<?= old('number', $old); ?>" inputmode="numeric" maxlength="13" required>
            <button type="button" class="icon-btn"><i class="fa fa-mobile"></i></button>
          </div>

          <div class="input-container">
            <input type="text" name="address" placeholder="Address"
                   value="<?= old('address', $old); ?>" required>
            <button type="button" class="icon-btn"><i class="fa fa-home"></i></button>
          </div>

          <select name="gender" required>
            <option value="" disabled <?= old('gender', $old) == '' ? 'selected' : ''; ?>>Select Gender</option>
            <option value="male" <?= old('gender', $old) == 'male' ? 'selected' : ''; ?>>Male</option>
            <option value="female" <?= old('gender', $old) == 'female' ? 'selected' : ''; ?>>Female</option>
            <option value="other" <?= old('gender', $old) == 'other' ? 'selected' : ''; ?>>Other</option>
          </select>

          <!-- Birthdate + Age -->
          <div class="date-age-container">
            <div class="input-container">
              <input type="date" id="birthdate" name="birthdate"
                     value="<?= old('birthdate', $old); ?>" 
                     min="1900-01-01" max="<?= $maxBirthdate ?>" required>
              <button type="button" class="icon-btn" id="calendar-icon"><i class="fa fa-calendar"></i></button>
            </div>
            <input type="number" name="age" placeholder="Age"
                   value="<?= old('age', $old); ?>" readonly>
          </div>

          <button type="submit" name="register">Register</button>
          <p class="asking"> Already have an account?
            <a href="#" onclick="showform('login-form')">Login</a>
          </p>
        </form>
      </div>

    </div>
  </section>

  <script src="script.js"></script>
</body>
</html>
