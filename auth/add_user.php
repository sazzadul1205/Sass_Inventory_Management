<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Add User | Sass Inventory System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" />

  <!-- Mobile + Theme -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="color-scheme" content="light dark" />

  <!-- Fonts -->
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    media="print" onload="this.media='all'" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />

  <!-- AdminLTE (Core Theme) -->
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />

  <!-- Custom CSS -->
  <style>
    .card-custom {
      border-radius: 12px;
      border: 1px solid #e9ecef;
      transition: 0.2s ease;
    }

    .card-custom:hover {
      border-color: #cbd3da;
    }

    .form-label {
      font-weight: 600;
    }

    .form-control,
    .form-select {
      padding: 10px 14px;
      border-radius: 8px;
    }

    .btn-primary {
      border-radius: 8px;
      font-weight: 600;
    }

    .btn-secondary {
      border-radius: 8px;
    }
  </style>
</head>


<?php
$formError = "";

// Fetch roles
$conn = connectDB();
$rolesResult = $conn->query("SELECT * FROM role ORDER BY role_name ASC");

// Handle form submit
if (isset($_POST['submit'])) {
  $username   = $_POST['username'];
  $email      = $_POST['email'];
  $password   = $_POST['password'];
  $role_id    = $_POST['role_id'];

  $hashed_password = md5($password);

  // Check for duplicate username
  $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    $formError = "Username already exists. Choose another.";
  } else {

    $sql = "INSERT INTO user (username, email, password, role_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())";

    $stmt2 = $conn->prepare($sql);
    $stmt2->bind_param("sssi", $username, $email, $hashed_password, $role_id);

    if ($stmt2->execute()) {
      $_SESSION['success_message'] = "User added successfully!";
      header("Location: users.php");
      exit;
    } else {
      $formError = "Failed to add user: " . $conn->error;
    }
  }
}
?>

<!-- Body -->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <!--  Navbar -->
    <?php include '../Inc/Navbar.php'; ?>

    <!-- Sidebar -->
    <?php include '../Inc/Sidebar.php'; ?>

    <!-- App Main -->
    <main class="app-main">

      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <!-- Page Title -->
          <h3 class="mb-0 " style="font-weight: 800;">Add New User </h3>
        </div>
      </div>

      <!-- Error -->
      <?php if ($formError): ?>
        <div id="errorBox" class="alert alert-danger text-center">
          <?= htmlspecialchars($formError) ?>
        </div>
      <?php endif; ?>

      <!-- Body -->
      <div class="app-content-body mt-4">
        <div class="container-fluid">
          <div class="card card-custom shadow-sm">
            <div class="card-body p-4">

              <!-- Header -->
              <h4 class="mb-4 fw-bold text-secondary border-bottom pb-2">
                Add User Information
              </h4>

              <!-- Form -->
              <form method="post" autocomplete="on">

                <!-- Username & Email -->
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter email address" required>
                  </div>
                </div>

                <!-- Password & Role -->
                <div class="row mt-3 g-3">
                  <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Create a strong password" required>
                  </div>

                  <!-- Role -->
                  <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-select" required>
                      <option value="">Select Role</option>
                      <?php while ($role = $rolesResult->fetch_assoc()): ?>
                        <option value="<?= $role['id'] ?>">
                          <?= htmlspecialchars($role['role_name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                </div>

                <!-- Actions -->
                <div class="mt-4 d-flex gap-2">
                  <button type="submit" name="submit" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-check2-circle"></i> Add User
                  </button>
                  <a href="users.php" class="btn btn-secondary px-4 py-2">
                    <i class="bi bi-x-circle"></i> Cancel
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <?php include '../Inc/Footer.php'; ?>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlaysscrollbars.browser.es6.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

  <!-- Auto-hide error -->
  <script>
    setTimeout(() => {
      const box = document.getElementById("errorBox");
      if (box) {
        box.style.opacity = "0";
        setTimeout(() => box.remove(), 500);
      }
    }, 3000);
  </script>

</body>

</html>