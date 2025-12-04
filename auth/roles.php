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
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Roles | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon" />

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

  <style>
    .role-card {
      background-color: #e9f5ff;
      border-radius: 0.75rem;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .role-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 1rem 1rem rgba(0, 0, 0, 0.15);
      cursor: pointer;
    }
  </style>

  <!-- Custom CSS -->
  <style>
    .btn-warning:hover {
      background-color: #d39e00 !important;
      border-color: #c99700 !important;
    }

    .btn-danger:hover {
      background-color: #bb2d3b !important;
      border-color: #b02a37 !important;
    }
  </style>
</head>

<?php
$conn = connectDB();
$sql = "SELECT * FROM role ORDER BY id ASC";
$result = $conn->query($sql);
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
          <h3 class="mb-0" style="font-weight: 800;">All Roles</h3>

          <a href="add_role.php" class="btn btn-sm btn-primary px-3 py-2" style="font-size: medium;">
            <i class="bi bi-plus me-1"></i> Add New Role
          </a>
        </div>
      </div>

      <!-- Success/Fail Messages -->
      <?php if (!empty($_SESSION['success_message'])): ?>
        <div id="successMsg" class="alert alert-success mt-3"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>

      <?php if (!empty($_SESSION['fail_message'])): ?>
        <div id="failMsg" class="alert alert-danger mt-3"><?= $_SESSION['fail_message'] ?></div>
        <?php unset($_SESSION['fail_message']); ?>
      <?php endif; ?>

      <!-- Page Content -->
      <div class="app-content-body mt-3">
        <div class="container-fluid">

          <?php if ($result->num_rows == 0): ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              <h5>No roles found</h5>
            </div>

          <?php else: ?>
            <div class="row g-3">
              <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-3 col-sm-6 mb-3">
                  <div class="card h-100 shadow-sm border-0 role-card">
                    <div class="card-body">
                      <h5 class="card-title mb-2">
                        <i class="bi bi-shield-check text-primary"></i>
                        <?= htmlspecialchars($row['role_name']) ?>
                      </h5>
                    </div>

                    <div class="card-footer bg-white border-0 d-flex gap-2">
                      <a href="edit_role.php?id=<?= urlencode($row['id']) ?>"
                        class="btn btn-warning btn-sm w-50">
                        <i class="bi bi-pencil"></i> Edit
                      </a>

                      <a href="delete_role.php?id=<?= urlencode($row['id']) ?>"
                        onclick="return confirm('Delete this role?')"
                        class="btn btn-danger btn-sm w-50">
                        <i class="bi bi-trash"></i> Delete
                      </a>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>

          <?php endif; ?>

        </div>
      </div>

    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>

  </div>

  <!-- JS Dependencies -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
  <script src="<?= $Project_URL ?>/js/adminlte.js"></script>

  <!-- Auto-hide messages -->
  <script>
    setTimeout(() => {
      const msg = document.getElementById('successMsg') || document.getElementById('failMsg');
      if (msg) msg.remove();
    }, 3000);
  </script>

</body>

</html>