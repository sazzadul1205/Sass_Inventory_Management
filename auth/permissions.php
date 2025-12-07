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

    /* Admin cells cursor */
    .admin-cell:hover {
      cursor: not-allowed;
      background: #f5f5f5;
    }

    /* Search input styling */
    #permissionSearch {
      max-width: 300px;
      margin-bottom: 10px;
    }

    /* Pending changes */
    .permission-cell.changed {
      background-color: #fff3cd;
      transition: background-color 0.3s;
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

<!-- Body -->

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
          <h3 class="mb-0" style="font-weight: 800;">Manage Permissions </h3>

          <!-- Update Permissions -->
          <?php if (can('update_permissions')): ?>
            <button
              id="updatePermissionsBtn"
              class="btn btn-sm btn-primary px-3 py-2"
              style="font-size: medium;"
              disabled>
              <i class="bi bi-arrow-repeat me-1"></i> Update Permissions
            </button>
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


      <!-- Toolbar -->
      <div class="table-toolbar p-3 mb-3 rounded shadow-sm bg-white d-flex flex-wrap align-items-end gap-3">
        <!-- Product Search -->
        <div class="d-flex flex-column flex-grow-1" style="min-width: 200px;">
          <label for="permissionSearch" class="form-label fw-semibold mb-1">Search Product</label>
          <input type="text" id="permissionSearch" class="form-control" placeholder="Type to search...">
        </div>
      </div>


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
                    <td class="sticky-left">
                      <?= htmlspecialchars(ucwords(str_replace('_', ' ', $permName))) ?>
                    </td>


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

  <!-- Custom JS -->
  <script>
    $(function() {
      const adminRoleId = 1;
      let changes = {};
      let originalState = {};

      // Initialize original state for each cell
      $(".permission-cell").each(function() {
        const roleId = $(this).data("role");
        const permId = $(this).data("permission");
        const checked = $(this).find("i").hasClass("bi-check-lg") ? 1 : 0;

        originalState[`${roleId}-${permId}`] = checked;
      });

      // Click toggle
      $(".permission-cell").click(function() {
        const roleId = parseInt($(this).data("role"));
        const permId = parseInt($(this).data("permission"));

        if (roleId === adminRoleId) return;

        const key = `${roleId}-${permId}`;
        const icon = $(this).find("i");

        let newState;

        if (icon.hasClass("bi-check-lg")) {
          icon.removeClass("bi-check-lg text-success").addClass("bi-x-lg text-danger");
          newState = 0;
        } else {
          icon.removeClass("bi-x-lg text-danger").addClass("bi-check-lg text-success");
          newState = 1;
        }

        // Compare with original state
        if (newState !== originalState[key]) {
          $(this).addClass("changed");
          changes[key] = newState;
        } else {
          $(this).removeClass("changed");
          delete changes[key];
        }

        // Enable or disable the update button
        $("#updatePermissionsBtn").prop("disabled", Object.keys(changes).length === 0);
      });

      // Save changes AJAX
      $("#updatePermissionsBtn").click(function() {
        const btn = $(this);
        btn.prop("disabled", true);

        $.post("update_permissions.php", {
          changes: changes
        }, function() {
          // After successful save, reset originalState and remove highlights
          for (let key in changes) {
            const [roleId, permId] = key.split("-");
            originalState[key] = changes[key]; // update original state
            $(`.permission-cell[data-role="${roleId}"][data-permission="${permId}"]`).removeClass("changed");
          }
          changes = {};
          location.reload(); // optional
        });
      });
    });
  </script>

  <!-- Permission search filter -->
  <script>
    $("#permissionSearch").on("keyup", function() {
      let value = $(this).val().toLowerCase();

      $("table tbody tr").filter(function() {
        $(this).toggle($(this).find("td:first").text().toLowerCase().indexOf(value) > -1)
      });
    });
  </script>

  <!-- Auto fade messages -->
  <script>
    setTimeout(() => {
      $("#successMsg,#failMsg").fadeOut(500, function() {
        $(this).remove();
      });
    }, 3000);
  </script>

</body>

</html>