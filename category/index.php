<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>Categories | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">

  <!-- Fonts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" media="print" onload="this.media='all'">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

  <!-- AdminLTE -->
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css">

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>


<!-- Fetch all categories -->
<?php
$conn = connectDB();

// Fetch all categories
$sql = "SELECT * FROM category ORDER BY id ASC";
$result = $conn->query($sql);
?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

  <!-- App Wrapper -->
  <div class="app-wrapper">
    <!--Header-->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!--Sidebar-->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!--App Main-->
    <main class="app-main">
      <!-- App Content Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid">
          <div class="d-flex justify-content-between align-items-center flex-wrap">

            <!-- Page Title -->
            <h3 class="mb-0">All Categories</h3>

            <!-- Breadcrumb + Action -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <!-- Add New User Button -->
              <a
                href="add_user.php"
                class="btn btn-sm btn-primary d-flex align-items-center px-3 py-2">
                <i class=" bi bi-plus me-1"></i> Add New Category
              </a>
            </div>

          </div>
        </div>
      </div>

      <!-- Success Message -->
      <?php
      if (!empty($_SESSION['success_message'])) {
        echo "
        <div id='successMsg' class='alert alert-success' style='position:relative; z-index:9999;'>
          {$_SESSION['success_message']}
        </div>";

        unset($_SESSION['success_message']); // Remove so it shows only once
      }

      if (!empty($_SESSION['fail_message'])) {
        echo "
        <div id='failMsg' class='alert alert-danger' style='position:relative; z-index:9999;'>
          {$_SESSION['fail_message']}
        </div>";

        unset($_SESSION['fail_message']); // Remove so it shows only once
      }
      ?>

      <!-- App Content Table -->
      <div class="app-content-body mt-3">
        <div class="table-responsive container-fluid">
          <table id="categoriesTable" class="table table-bordered table-hover table-striped align-middle">
            <thead class="table-primary">
              <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Description</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>
                      <?= !empty($row['created_at']) ? date('d M Y h:i A', strtotime($row['created_at'])) : '' ?>
                    </td>
                    <td>
                      <?= !empty($row['updated_at']) ? date('d M Y h:i A', strtotime($row['updated_at'])) : '' ?>
                    </td>
                    <td>
                      <div class="d-flex gap-1">
                        <a href="edit_category.php?id=<?= urlencode($row['id']) ?>" class="btn btn-warning btn-sm flex-fill">
                          <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <a href="delete_category.php?id=<?= urlencode($row['id']) ?>" class="btn btn-danger btn-sm flex-fill"
                          onclick="return confirm('Are you sure you want to delete this category?');">
                          <i class="bi bi-trash"></i> Delete
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center">No categories found</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </main>

    <!--Footer-->
    <?php include_once '../Inc/Footer.php'; ?>


  </div>


  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

  <!-- AdminLTE JS -->
  <script src="./js/adminlte.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- Table Initialization -->
  <script>
    $(document).ready(function() {
      $('#categoriesTable').DataTable({
        "paging": true,
        "pageLength": 10,
        "lengthChange": true,
        "ordering": true,
        "order": [],
        "info": true,
        "autoWidth": false,
        "columnDefs": [{
            "orderable": false,
            "targets": 5
          } // Disable sorting for Actions column
        ]
      });

      // Auto hide success/fail messages
      setTimeout(() => {
        const msg = document.getElementById('successMsg');
        if (msg) {
          msg.style.transition = "opacity 0.5s";
          msg.style.opacity = "0";
          setTimeout(() => msg.remove(), 500);
        }
        const failMsg = document.getElementById('failMsg');
        if (failMsg) {
          failMsg.style.transition = "opacity 0.5s";
          failMsg.style.opacity = "0";
          setTimeout(() => failMsg.remove(), 500);
        }
      }, 3000);
    });
  </script>
</body>

</html>