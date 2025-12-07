<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$conn = connectDB();

// Get role ID from query string
if (!isset($_GET['id']) || empty($_GET['id'])) {
  $_SESSION['fail_message'] = "Invalid role ID!";
  header("Location: roles.php");
  exit;
}

$roleId = intval($_GET['id']);

// Fetch role info
$stmt = $conn->prepare("SELECT role_name FROM role WHERE id = ?");
$stmt->bind_param('i', $roleId);
$stmt->execute();
$stmt->bind_result($roleNameFromDB);
if (!$stmt->fetch()) {
  $_SESSION['fail_message'] = "Role not found!";
  header("Location: roles.php");
  exit;
}
$stmt->close();

// Fetch permissions and assigned flags
$result = $conn->query("SELECT permission_id, permission_name, assigned FROM role_permission_matrix WHERE role_id = $roleId ORDER BY permission_name ASC");

$permissions = [];
$assignedPermissions = [];

while ($row = $result->fetch_assoc()) {
  $permissions[$row['permission_id']] = $row['permission_name'];
  if ($row['assigned']) {
    $assignedPermissions[] = $row['permission_id'];
  }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $roleName = trim($_POST['role_name']);
  $selectedPermissions = $_POST['permissions'] ?? [];

  if (!empty($roleName)) {
    // Update role name
    $stmt = $conn->prepare("UPDATE role SET role_name = ? WHERE id = ?");
    $stmt->bind_param('si', $roleName, $roleId);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
      // Remove old permissions
      $stmt = $conn->prepare("DELETE FROM role_permission WHERE role_id = ?");
      $stmt->bind_param('i', $roleId);
      $stmt->execute();
      $stmt->close();

      // Insert selected permissions
      foreach ($selectedPermissions as $permId) {
        $stmt = $conn->prepare("INSERT INTO role_permission (role_id, permission_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $roleId, $permId);
        $stmt->execute();
        $stmt->close();
      }

      $_SESSION['success_message'] = "Role '$roleName' updated successfully!";
      header("Location: roles.php");
      exit;
    } else {
      $_SESSION['fail_message'] = "Failed to update role!";
    }
  } else {
    $_SESSION['fail_message'] = "Role name cannot be empty!";
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Edit Role | Sass Inventory Management System</title>
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

  <!-- Permission card CSS -->
  <style>
    .permission-card {
      transition: 0.2s;
      cursor: pointer;
      user-select: none;
      text-align: center;
      padding: 1rem;
      border-radius: 0.5rem;
    }

    .permission-card:hover {
      background: #f0f8ff;
      transform: translateY(-2px);
    }

    .permission-card.selected {
      background: #007bff;
      color: #fff;
      font-weight: 600;
    }
  </style>
</head>

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
          <h3 class="mb-0" style="font-weight: 800;">Edit Role</h3>
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

      <!-- Form -->
      <div class="app-content-body mt-3">
        <div class="container-fluid">
          <form method="POST">
            <!-- Role Name -->
            <div class="mb-3">
              <label class="form-label" style="font-weight: 700;">Role Name</label>
              <input type="text" class="form-control" name="role_name" required
                value="<?= htmlspecialchars($roleNameFromDB) ?>">
            </div>

            <!-- Permissions -->
            <div class="mb-3">
              <label class="form-label" style="font-weight: 700;">Assign Permissions</label>
              <div class="row g-3">
                <?php foreach ($permissions as $permId => $permName): ?>
                  <div class="col-md-3 col-sm-6">
                    <div class="permission-card card p-2 h-100 shadow-sm border-0
                        <?= in_array($permId, $assignedPermissions) ? 'selected' : '' ?>"
                      data-perm-id="<?= $permId ?>">
                      <?= htmlspecialchars(ucwords(str_replace('_', ' ', $permName))) ?>
                      <input type="checkbox" name="permissions[]" value="<?= $permId ?>" class="d-none"
                        <?= in_array($permId, $assignedPermissions) ? 'checked' : '' ?>>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Buttons -->
            <div class="mt-4 d-flex gap-2">
              <button type="submit" name="submit" class="btn btn-primary px-4 py-2">
                <i class="bi bi-check2-circle"></i> Update Role
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

  <!-- Permission card toggle -->
  <script>
    document.querySelectorAll('.permission-card').forEach(card => {
      card.addEventListener('click', function() {
        const checkbox = this.querySelector('input[type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        this.classList.toggle('selected', checkbox.checked);
      });
    });

    // Auto remove messages
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