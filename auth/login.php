<?php include_once __DIR__ . '/../config/db_config.php'; ?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Login | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon" />

  <!-- Basic viewport for proper scaling -->
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap Icons (used inside form inputs) -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    crossorigin="anonymous" />

  <!-- AdminLTE main stylesheet (layout + colors) -->
  <link rel="stylesheet" href="<?= $Project_URL ?>css/adminlte.css" />

  <!-- Bootstrap 5 CSS (AdminLTE depends on it) -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    crossorigin="anonymous" />

</head>

<?php
// Initialize error message
$loginError = '';

if (isset($_POST['submit'])) {
  extract($_POST);
  $password = md5($password);

  $conn = connectDB();
  $sql = "SELECT * FROM user WHERE email='$email' AND `password`='$password'";
  $result = $conn->query($sql);

  $recode = $result->fetch_assoc();
  if ($result->num_rows > 0) {
    session_start();
    $_SESSION['loggedIn'] = true;
    $_SESSION['email'] = $email;
    $_SESSION['user_id'] = $recode['id'];
    $_SESSION['role_id'] = $recode['role_id'];
    $_SESSION['username'] = $recode['username'];

    header("Location: " . $Project_URL . "/index.php");
    exit();
  } else {
    $loginError = "Invalid Email or Password";
  }
}
?>

<body class="login-page bg-body-secondary">
  <div class="login-box">

    <!-- Logo -->
    <div class="login-logo">
      <img
        src="<?= $Project_URL ?>assets/Dashboard/Website_logo.png"
        alt="Project Logo"
        width="200px">
    </div>

    <!-- Error Message -->
    <?php if (!empty($loginError)): ?>
      <div id="login-error" class="alert alert-danger text-center mb-3">
        <?= htmlspecialchars($loginError) ?>
      </div>

      <script>
        // Auto-hide login error after 3 seconds
        setTimeout(() => {
          const error = document.getElementById('login-error');
          if (error) error.style.display = 'none';
        }, 3000);
      </script>
    <?php endif; ?>

    <!-- Login Card -->
    <div class="card shadow-sm rounded-3">
      <div class="card-body login-card-body p-4">
        <div class="text-center mb-4">
          <h4 class="login-box-msg">Sign in to start your session</h4>
        </div>

        <form action="" method="post" autocomplete="on">
          <!-- Email -->
          <div class="mb-3">
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                placeholder="Email"
                required
                autocomplete="email" />
            </div>
          </div>

          <!-- Password -->
          <div class="mb-3">
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
              <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                placeholder="Password"
                required
                autocomplete="current-password" />
            </div>
          </div>

          <!-- Remember + Submit -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe" />
              <label class="form-check-label" for="rememberMe">Remember Me</label>
            </div>
            <button type="submit" name="submit" class="btn btn-primary px-4">Sign In</button>
          </div>

          <div class="text-center">
            <a href="#" class="text-decoration-none">Forgot Password?</a>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- Bootstrap 5 JS (needed for components like alerts/buttons) -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
    crossorigin="anonymous"></script>

</body>

</html>