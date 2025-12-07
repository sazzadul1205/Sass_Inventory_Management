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
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Users | Sass Inventory Management System</title>
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

  <!-- DataTables (Needed for user list table) -->
  <link rel="stylesheet"
    href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

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

<!-- Fetch all users -->
<?php
// Fetch all users with roles
$conn = connectDB();
$sql = "SELECT u.*, r.role_name 
        FROM user u 
        LEFT JOIN role r ON u.role_id = r.id
        ORDER BY u.id ASC";
$result = $conn->query($sql);
?>

<!-- Body -->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">

    <!-- Navbar -->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!-- Sidebar -->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <?php
    $hasPermission = can('view_users');
    ?>

    <!-- Later in the HTML, right after your includes -->
    <?php if (!$hasPermission): ?>
      <div class="container mt-5">
        <div class="alert alert-danger">
          You do not have permission to access this page.
        </div>
        <a href="../index.php" class="btn btn-primary mt-3">Go Back</a>
      </div>
      <?php exit; // stop rendering the rest of the page 
      ?>
    <?php endif; ?>


    <!-- App Main -->
    <main class="app-main">
      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <!-- Page Title -->
          <h3 class="mb-0 " style="font-weight: 800;">All Users</h3>

          <!-- Add User Button -->
          <?php if (can('add_user')): ?>
            <!-- Add User Button -->
            <a href="add_user.php" class="btn btn-sm btn-primary px-3 py-2" style="font-size: medium;">
              <i class="bi bi-plus me-1"></i> Add New User
            </a>
          <?php endif; ?>
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


      <!-- App Content Table -->
      <div class="app-content-body mt-3">
        <div class="container-fluid">

          <?php if ($result->num_rows == 0): ?>
            <!-- Empty state -->
            <div class="text-center text-muted py-5">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              <h5>No users found</h5>
            </div>

          <?php else: ?>
            <div class="table-responsive">
              <table id="usersTable" class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-primary">
                  <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                  </tr>
                </thead>

                <tbody>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= $row['id'] ?></td>
                      <td><?= htmlspecialchars($row['username']) ?></td>
                      <td><?= htmlspecialchars($row['email']) ?></td>
                      <td><?= htmlspecialchars($row['role_name'] ?? 'Unknown') ?></td>

                      <td>
                        <?= !empty($row['created_at'])
                          ? date('d M Y h:i A', strtotime($row['created_at']))
                          : '-' ?>
                      </td>

                      <td>
                        <?= !empty($row['updated_at'])
                          ? date('d M Y h:i A', strtotime($row['updated_at']))
                          : '-' ?>
                      </td>

                      <td>
                        <div class="d-flex gap-1">
                          <!-- Edit button -->
                          <?php if (can('edit_user')): ?>
                            <!-- Edit button -->
                            <a href="edit_user.php?id=<?= urlencode($row['id']) ?>" class="btn btn-warning btn-sm flex-fill">
                              <i class="bi bi-pencil-square"></i> Edit
                            </a>
                          <?php endif; ?>

                          <!-- Delete button -->
                          <?php if (can('delete_user')): ?>
                            <a href="delete_user.php?id=<?= urlencode($row['id']) ?>"
                              class="btn btn-danger btn-sm flex-fill"
                              onclick="return confirm('Are you sure you want to delete this user?');">
                              <i class="bi bi-trash"></i> Delete
                            </a>
                          <?php endif; ?>
                        </div>
                      </td>

                    </tr>
                  <?php endwhile; ?>
                </tbody>

              </table>
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

  <!-- DataTables -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- Custom JS -->
  <script>
    $(document).ready(function() {
      $('#usersTable').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false
      });
    });

    setTimeout(() => {
      const msg = document.getElementById('successMsg') || document.getElementById('failMsg');
      if (msg) msg.remove();
    }, 3000);
  </script>

</body>

</html>