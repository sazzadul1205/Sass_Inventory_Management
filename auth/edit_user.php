<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('view_roles', '../index.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title><?= $pageTitle ?> | Sass Inventory Management</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">

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
$conn = connectDB();
$formError = "";

// Fetch Roles
$roles = $conn->query("SELECT * FROM role ORDER BY role_name ASC");

// Check Edit Mode
$userId = isset($_GET['id']) ? intval($_GET['id']) : null;
$user = null;

if ($userId) {
  $stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
}

$isEdit = $user !== null;
$pageTitle = $isEdit ? "Edit User" : "Add New User";
$formTitle = $isEdit ? "Update User Information" : "Fill Information to Add New User";
$submitText = $isEdit ? "Update User" : "Add User";

// Handle Form Submission
if (isset($_POST['submit'])) {

  $id         = intval($_POST['id']);
  $username   = trim($_POST['username']);
  $email      = trim($_POST['email']);
  $password   = trim($_POST['password']);
  $role_id    = intval($_POST['role_id']);
  $updated_at = date('Y-m-d H:i:s'); // Always use current timestamp

  if (empty($username) || empty($email) || empty($role_id)) {
    $formError = "Please fill in all required fields.";
  } else {

    // Duplicate check
    $check = $conn->prepare("SELECT id FROM user WHERE (username = ? OR email = ?) AND id != ?");
    $check->bind_param("ssi", $username, $email, $id);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
      $formError = "Username or email already exists.";
    } else {

      // Keep old password if unchanged
      if (empty($password)) {
        $p = $conn->prepare("SELECT password FROM user WHERE id = ?");
        $p->bind_param("i", $id);
        $p->execute();
        $password = $p->get_result()->fetch_assoc()['password'];
      } else {
        $password = md5($password);
      }

      // Perform Update
      $update = $conn->prepare(
        "UPDATE user SET username=?, email=?, password=?, role_id=?, updated_at=? WHERE id=?"
      );

      $update->bind_param("sssisi", $username, $email, $password, $role_id, $updated_at, $id);

      if ($update->execute()) {
        $_SESSION['success_message'] = "User updated successfully!";
        header("Location: users.php");
        exit;
      } else {
        $formError = "Update failed: " . $update->error;
      }
    }
  }
}
?>

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
          <h3 class="mb-0 " style="font-weight: 800;"> Update User </h3>
        </div>
      </div>

      <!-- Error -->
      <?php if (!empty($formError)): ?>
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
                Update User Information
              </h4>

              <!-- Form -->
              <form method="post">

                <!-- Username & Email -->
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control"
                      value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                      value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                  </div>
                </div>

                <!-- Password & Role  -->
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">
                      Password
                      <?php if ($isEdit): ?><small class="text-muted">(Leave empty to keep unchanged)</small><?php endif; ?>
                    </label>
                    <input
                      type="password"
                      name="password"
                      class="form-control" <?= $isEdit ? '' : 'required' ?>
                      placeholder="**********">
                  </div>

                  <!-- Role -->
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-select" required>
                      <option value="">Select Role</option>
                      <?php while ($r = $roles->fetch_assoc()): ?>
                        <option value="<?= $r['id'] ?>"
                          <?= ($user['role_id'] ?? null) == $r['id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($r['role_name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                </div>

                <?php if ($isEdit): ?>
                  <input type="hidden" name="id" value="<?= $userId ?>">
                <?php endif; ?>

                <!-- Actions -->
                <div class="mt-4 d-flex gap-2">
                  <button type="submit" name="submit" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-check2-circle"></i> Update User
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
    <?php include_once '../Inc/Footer.php'; ?>

  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlaysscrollbars.browser.es6.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

  <!-- Auto-hide error -->
  <script>
    // Auto-hide error message
    setTimeout(() => {
      const box = document.getElementById("errorBox");
      if (box) {
        box.style.opacity = 0;
        setTimeout(() => box.remove(), 500);
      }
    }, 3000);
  </script>

</body>

</html>