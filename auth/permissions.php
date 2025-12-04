<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';
?>

<!doctype html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Permissions | Sass Inventory Management System</title>
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

  <!-- Custom -->
  <style>
    .permission-cell:hover:not(.admin-cell) {
      background: #eef7ff;
      cursor: pointer;
    }

    th,
    td {
      white-space: nowrap;
    }

    .sticky-left {
      position: sticky;
      left: 0;
      background: #fff;
      z-index: 3;
    }
  </style>
</head>

<?php
$conn = connectDB();

$result = $conn->query("SELECT * FROM role_permission_matrix");
$matrix = [];

while ($row = $result->fetch_assoc()) {
  $roleId = $row['role_id'];
  $permId = $row['permission_id'];

  $roles[$roleId] = $row['role_name'];
  $permissions[$permId] = $row['permission_name'];

  $matrix[$roleId][$permId] = $row['assigned'];
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
          <h3 class="mb-0" style="font-weight: 800;">All Roles</h3>

          <button
            id="updatePermissionsBtn"
            class="btn btn-sm btn-primary px-3 py-2"
            style="font-size: medium;"
            disabled>
            <i class="bi bi-arrow-repeat me-1"></i> Update Permissions
          </button>
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

      <!-- App Content -->
      <div class="app-content-body mt-3">
        <div class="container-fluid">

          <!-- Table -->
          <div class="table-responsive" style="max-height:500px; overflow:auto;">
            <table class="table table-bordered table-hover">

              <!-- Table Header -->
              <thead class="table-primary text-center">
                <tr>
                  <th class="sticky-left">Permission</th>
                  <?php foreach ($roles as $roleName): ?>
                    <th style="position: sticky; top:0; background:#f8f9fa;"><?= htmlspecialchars($roleName) ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>

              <!-- Table Body -->
              <tbody>
                <?php foreach ($permissions as $permId => $permName): ?>
                  <tr>
                    <td class="sticky-left"><?= htmlspecialchars($permName) ?></td>

                    <?php foreach ($roles as $roleId => $roleName): ?>
                      <td class="text-center permission-cell <?= $roleId == 1 ? 'admin-cell' : '' ?>"
                        data-role="<?= $roleId ?>" data-permission="<?= $permId ?>">

                        <?php if ($matrix[$roleId][$permId]): ?>
                          <i class="bi bi-check-lg text-success"></i>
                        <?php else: ?>
                          <i class="bi bi-x-lg text-danger"></i>
                        <?php endif; ?>
                      </td>
                    <?php endforeach; ?>

                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
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

  <script>
    $(function() {
      const adminRoleId = 1;
      let changes = {};

      // Click toggle
      $(".permission-cell").click(function() {
        let roleId = parseInt($(this).data("role"));
        let permId = parseInt($(this).data("permission"));

        if (roleId === adminRoleId) return;

        let icon = $(this).find("i");

        if (icon.hasClass("bi-check-lg")) {
          icon.removeClass("bi-check-lg text-success").addClass("bi-x-lg text-danger");
          changes[`${roleId}-${permId}`] = 0;
        } else {
          icon.removeClass("bi-x-lg text-danger").addClass("bi-check-lg text-success");
          changes[`${roleId}-${permId}`] = 1;
        }

        $("#updatePermissionsBtn").prop("disabled", Object.keys(changes).length === 0);
      });

      // Save changes AJAX
      $("#updatePermissionsBtn").click(function() {
        $.post("update_permissions.php", {
          changes: changes
        }, function() {
          location.reload();
        });

        $(this).prop("disabled", true);
      });
    });

    // Auto fade messages
    setTimeout(() => {
      $("#successMsg,#failMsg").fadeOut(500, function() {
        $(this).remove();
      });
    }, 3000);
  </script>

</body>

</html>