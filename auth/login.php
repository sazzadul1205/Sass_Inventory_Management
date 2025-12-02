<?php include_once __DIR__ . '/../config/db_config.php'; ?>
<!doctype html>
<html lang="en">
<!--begin::Head-->

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Login | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon" />

  <!--begin::Accessibility Meta Tags-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
  <!--end::Accessibility Meta Tags-->

  <!--begin::Primary Meta Tags-->
  <meta name="title" content="Login | Sass Inventory Management System" />
  <meta name="author" content="ColorlibHQ" />
  <meta
    name="description"
    content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS. Fully accessible with WCAG 2.1 AA compliance." />
  <meta
    name="keywords"
    content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant" />
  <!--end::Primary Meta Tags-->

  <!--begin::Accessibility Features-->
  <!-- Skip links will be dynamically added by accessibility.js -->
  <meta name="supported-color-schemes" content="light dark" />
  <link rel="preload" href="../css/adminlte.css" as="style" />
  <!--end::Accessibility Features-->

  <!--begin::Fonts-->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
    crossorigin="anonymous"
    media="print"
    onload="this.media='all'" />
  <!--end::Fonts-->

  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
    crossorigin="anonymous" />
  <!--end::Third Party Plugin(OverlayScrollbars)-->

  <!--begin::Third Party Plugin(Bootstrap Icons)-->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    crossorigin="anonymous" />
  <!--end::Third Party Plugin(Bootstrap Icons)-->

  <!--begin::Required Plugin(AdminLTE)-->
  <link rel="stylesheet" href="<?= $Project_URL ?>css/adminlte.css" />
  <!--end::Required Plugin(AdminLTE)-->
</head>
<!--end::Head-->
<!--begin::Body-->

<?php
// Initialize error message
$loginError = '';

if (isset($_POST['submit'])) {
  extract($_POST);
  $password = md5($password);

  // Connect to the database
  $conn = connectDB();

  $sql = "SELECT * FROM user WHERE email='$email' AND `password`='$password'";
  $result = $conn->query($sql);

  $recode = $result->fetch_assoc();
  if ($result->num_rows > 0) {
    session_start();
    // Set session variables
    $_SESSION['loggedIn'] = true;
    $_SESSION['email'] = $email;
    $_SESSION['role_id '] = $recode['role_id '];
    $_SESSION['username '] = $recode['username '];

    header("Location: " . $Project_URL . "/index.php");
    exit();
  } else {
    // Failed login
    $loginError = "Invalid Email or Password";
  }
}
?>

<body class="login-page bg-body-secondary">
  <div class="login-box">

    <!-- Login Logo -->
    <div class="login-logo">
      <img
        src="<?= $Project_URL ?>assets/Dashboard/Website_logo.png"
        alt="Project Logo"
        width="200px">
    </div>

    <!-- Display Error at Top -->
    <?php if (!empty($loginError)): ?>
      <div id="login-error" class="alert alert-danger text-center mb-3">
        <?= htmlspecialchars($loginError) ?>
      </div>

      <script>
        // Hide error after 3 seconds
        setTimeout(() => {
          const errorDiv = document.getElementById('login-error');
          if (errorDiv) errorDiv.style.display = 'none';
        }, 3000);
      </script>
    <?php endif; ?>

    <!-- Login Card -->
    <div class="card shadow-sm rounded-3">
      <div class="card-body login-card-body p-4">
        <div class="text-center mb-4">
          <h4 class="login-box-msg">Sign in to start your session</h4>
        </div>

        <!-- Login Form -->
        <form action="" method="post" autocomplete="on">
          <!-- Email -->
          <div class="mb-3">
            <label for="email" class="form-label visually-hidden">Email</label>
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
            <label for="password" class="form-label visually-hidden">Password</label>
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

          <!-- Remember Me & Sign In -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                id="rememberMe"
                name="rememberMe" />
              <label class="form-check-label" for="rememberMe">
                Remember Me
              </label>
            </div>
            <button type="submit" name="submit" class="btn btn-primary px-4">Sign In</button>
          </div>

          <!-- Optional Links -->
          <div class="text-center">
            <a href="#" class="text-decoration-none">Forgot Password?</a>
          </div>
        </form>
      </div>
    </div>

  </div>

  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <script
    src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
    crossorigin="anonymous"></script>
  <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
  <script
    src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    crossorigin="anonymous"></script>
  <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
    crossorigin="anonymous"></script>
  <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
  <script src="../js/adminlte.js"></script>
  <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
  <script>
    const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
    const Default = {
      scrollbarTheme: 'os-theme-light',
      scrollbarAutoHide: 'leave',
      scrollbarClickScroll: true,
    };
    document.addEventListener('DOMContentLoaded', function() {
      const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

      // Disable OverlayScrollbars on mobile devices to prevent touch interference
      const isMobile = window.innerWidth <= 992;

      if (
        sidebarWrapper &&
        OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
        !isMobile
      ) {
        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
          scrollbars: {
            theme: Default.scrollbarTheme,
            autoHide: Default.scrollbarAutoHide,
            clickScroll: Default.scrollbarClickScroll,
          },
        });
      }
    });
  </script>
  <!--end::OverlayScrollbars Configure-->
  <!--end::Script-->
</body>

<!--end::Body-->

</html>