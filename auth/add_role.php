<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Users | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" />

  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Fonts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="print" onload="this.media='all'" />

  <!-- Overlay Scrollbars -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />

  <!-- AdminLTE -->
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />

  <!-- Apexcharts & VectorMap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css" />
</head>

<?php
$conn = connectDB();

// Fetch permissions
$permissions = [];
$permissionsResult = $conn->query("SELECT * FROM permission ORDER BY permission_name ASC");
while ($perm = $permissionsResult->fetch_assoc()) {
  $permissions[$perm['id']] = $perm['permission_name'];
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $roleName = trim($_POST['role_name']);
  $selectedPermissions = $_POST['permissions'] ?? [];

  if (!empty($roleName)) {
    $stmt = $conn->prepare("INSERT INTO role (role_name) VALUES (?)");
    $stmt->bind_param('s', $roleName);

    if ($stmt->execute()) {
      $roleId = $stmt->insert_id;
      $stmt->close();

      foreach ($selectedPermissions as $permId) {
        $stmt = $conn->prepare("INSERT INTO role_permission (role_id, permission_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $roleId, $permId);
        $stmt->execute();
        $stmt->close();
      }

      $_SESSION['success_message'] = "Role '$roleName' created successfully!";
      header("Location: roles.php");
      exit;
    } else {
      $_SESSION['fail_message'] = "Failed to create role!";
    }
  } else {
    $_SESSION['fail_message'] = "Role name cannot be empty!";
  }
}
?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <!-- Navbar -->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!-- Sidebar -->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!-- App Main -->
    <main class="app-main">

      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <!-- Page Title -->
          <h3 class="mb-0 " style="font-weight: 800;">Add Role</h3>
        </div>
      </div>

      <!-- Messages -->
      <?php if (!empty($_SESSION['success_message'])): ?>
        <div id="successMsg" class="alert alert-success"><?= $_SESSION['success_message']; ?></div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>
      <?php if (!empty($_SESSION['fail_message'])): ?>
        <div id="failMsg" class="alert alert-danger"><?= $_SESSION['fail_message']; ?></div>
        <?php unset($_SESSION['fail_message']); ?>
      <?php endif; ?>


      <!-- Page Content -->
      <div class="app-content-body mt-3">
        <div class="container-fluid">

          <!-- Form -->
          <form method="POST">

            <!-- Role Name -->
            <div class="mb-3">
              <label class="form-label">Role Name</label>
              <input type="text" class="form-control" name="role_name" required>
            </div>

            <!-- Permissions -->
            <div class="mb-3">
              <!-- Assign Permissions -->
              <label class="form-label">Assign Permissions</label>

              <!-- Display Permissions -->
              <div class="row">
                <?php foreach ($permissions as $permId => $permName): ?>
                  <div class="col-md-3">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $permId ?>" id="perm<?= $permId ?>">
                      <label class="form-check-label" for="perm<?= $permId ?>">
                        <?= htmlspecialchars($permName) ?>
                      </label>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Buttons -->
            <div class="mt-4 d-flex gap-2">
              <button type="submit" name="submit" class="btn btn-primary px-4 py-2">
                <i class="bi bi-check2-circle"></i> Save Role
              </button>
              <a href="roles.php" class="btn btn-secondary px-4 py-2">
                <i class="bi bi-x-circle"></i> Cancel
              </a>
            </div>
          </form>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>

  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
  <script src="./js/adminlte.js"></script>

  <!-- Auto Remove Messages -->
  <script>
    setTimeout(() => {
      document.querySelectorAll("#successMsg, #failMsg").forEach(el => {
        el.style.transition = "0.5s";
        el.style.opacity = "0";
        setTimeout(() => el.remove(), 500);
      });
    }, 2500);
  </script>

</body>

</html>