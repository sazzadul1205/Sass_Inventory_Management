<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';
?>

<!doctype html>
<html lang="en">
<!--begin::Head-->

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Users | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon" />

  <!--begin::Accessibility Meta Tags-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
  <!--end::Accessibility Meta Tags-->

  <!--begin::Primary Meta Tags-->
  <meta name="title" content="Admin Home | Sass Inventory Management System" />
  <meta name="author" content="ColorlibHQ" />
  <meta name="description"
    content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS. Fully accessible with WCAG 2.1 AA compliance." />
  <meta name="keywords"
    content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant" />
  <!--end::Primary Meta Tags-->

  <!--begin::Accessibility Features-->
  <!-- Skip links will be dynamically added by accessibility.js -->
  <meta name="supported-color-schemes" content="light dark" />
  <link rel="preload" href="./css/adminlte.css" as="style" />
  <!--end::Accessibility Features-->

  <!--begin::Fonts-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" media="print"
    onload="this.media='all'" />
  <!--end::Fonts-->

  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
    crossorigin="anonymous" />
  <!--end::Third Party Plugin(OverlayScrollbars)-->

  <!--begin::Third Party Plugin(Bootstrap Icons)-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    crossorigin="anonymous" />
  <!--end::Third Party Plugin(Bootstrap Icons)-->

  <!--begin::Required Plugin(AdminLTE)-->
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />
  <!--end::Required Plugin(AdminLTE)-->

  <!-- apexcharts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
    integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0=" crossorigin="anonymous" />

  <!-- jsvectormap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
    integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4=" crossorigin="anonymous" />

  <style>
    .role-card {
      background-color: #e9f5ff;
      /* soft light blue */
      border-radius: 0.75rem;
      /* slightly rounded corners */
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .role-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 1rem 1rem rgba(0, 0, 0, 0.15);
      cursor: pointer;
    }
  </style>
</head>
<!--end::Head-->
<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

  <?php
  $conn = connectDB();

  // Fetch roles
  $rolesResult = $conn->query("SELECT * FROM role ORDER BY role_name ASC");
  // Fetch permissions
  $permissionsResult = $conn->query("SELECT * FROM permission ORDER BY permission_name ASC");
  // Fetch role-permission relationships
  $rolePermissionsResult = $conn->query("SELECT * FROM role_permission");

  // Arrays
  $roles = [];
  $permissions = [];
  $matrix = [];

  // Store roles
  while ($role = $rolesResult->fetch_assoc()) {
    $roles[$role['id']] = $role['role_name'];
  }

  // Store permissions
  while ($perm = $permissionsResult->fetch_assoc()) {
    $permissions[$perm['id']] = $perm['permission_name'];
  }

  // Initialize matrix
  foreach ($roles as $roleId => $roleName) {
    foreach ($permissions as $permId => $permName) {
      $matrix[$roleId][$permId] = 0;
    }
  }

  // Fill assigned permissions
  while ($rp = $rolePermissionsResult->fetch_assoc()) {
    $matrix[$rp['role_id']][$rp['permission_id']] = 1;
  }
  ?>

  <!--begin::App Wrapper-->
  <div class="app-wrapper">
    <!--Header-->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!--Sidebar-->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!--App Main-->
    <main class="app-main">
      <!-- App Content Header -->
      <div class="app-content-header py-3 border-bottom shadow-sm bg-light">
        <div class="container-fluid">
          <div class="d-flex justify-content-between align-items-center flex-wrap">

            <!-- Page Title -->
            <h3 class="mb-0">Permissions</h3>
          </div>
        </div>
      </div>

      <!-- Success/Failure Message -->
      <?php
      if (!empty($_SESSION['success_message'])) {
        echo "
        <div id='successMsg' class='alert alert-success' style='position:relative; z-index:9999;'>
          {$_SESSION['success_message']}
        </div>";
        unset($_SESSION['success_message']);
      }

      if (!empty($_SESSION['fail_message'])) {
        echo "
        <div id='failMsg' class='alert alert-danger' style='position:relative; z-index:9999;'>
          {$_SESSION['fail_message']}
        </div>";
        unset($_SESSION['fail_message']);
      }
      ?>


      <!-- App Content Body -->
      <div class="app-content-body mt-3">
        <div class="container-fluid">

          <div class="table-responsive" style="max-height:500px; overflow:auto;">
            <table class="table table-bordered table-striped table-hover">
              <thead class="table-primary text-center">
                <tr>
                  <th style="position: sticky; top: 0; left: 0; z-index: 3; background:#f8f9fa;">Permission Name</th>
                  <?php foreach ($roles as $roleId => $roleName): ?>
                    <th style="position: sticky; top:0; z-index:2; background:#f8f9fa;"><?= htmlspecialchars($roleName) ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($permissions as $permId => $permName): ?>
                  <tr>
                    <td style="position: sticky; left: 0; background:#fff; z-index:1;"><?= htmlspecialchars($permName) ?></td>
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

          <button id="updatePermissionsBtn" class="btn btn-primary mt-3" disabled>Update Permissions</button>
        </div>
      </div>

  </div>
  </div>
  </main>

  <!--Footer-->
  <?php include_once '../Inc/Footer.php'; ?>
  </div>


  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script>
    $(document).ready(function() {
      const adminRoleId = 1; // Admin role ID
      let changes = {};

      // Toggle permissions on cell click
      $(document).on('click', '.permission-cell', function() {
        const roleId = parseInt($(this).data('role'));
        const permId = parseInt($(this).data('permission'));

        // Do not allow toggling admin role
        if (roleId === adminRoleId) return;

        const icon = $(this).find('i');

        // Toggle icon
        if (icon.hasClass('bi-check-lg')) {
          icon.removeClass('bi-check-lg text-success').addClass('bi-x-lg text-danger');
          changes[roleId + '-' + permId] = 0;
        } else {
          icon.removeClass('bi-x-lg text-danger').addClass('bi-check-lg text-success');
          changes[roleId + '-' + permId] = 1;
        }

        // Enable or disable update button
        $('#updatePermissionsBtn').prop('disabled', Object.keys(changes).length === 0);
      });

      // AJAX update
      $('#updatePermissionsBtn').click(function() {
        if (Object.keys(changes).length === 0) return;

        $.ajax({
          url: 'update_permissions.php',
          type: 'POST',
          data: {
            changes: changes
          },
          success: function() {
            // Reload page after successful update to show PHP session messages
            location.reload();
          },
          error: function(xhr, status, error) {
            // On error, also reload page (optional: set a fail session message in PHP)
            location.reload();
          }
        });

        // Disable update button immediately
        $('#updatePermissionsBtn').prop('disabled', true);
      });
    });
  </script>

  <script>
    setTimeout(() => {
      const msg = document.getElementById('successMsg');
      if (msg) {
        msg.style.transition = "opacity 0.5s";
        msg.style.opacity = "0";
        setTimeout(() => msg.remove(), 500);
      }
    }, 3000); // 3 seconds
  </script>
</body>

</html>