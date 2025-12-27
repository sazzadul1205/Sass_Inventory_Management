<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('view_categories', '../index.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Categories | Sass Inventory Management System</title>
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

<?php
$conn = connectDB();

// Fetch all categories
$sql = "SELECT * FROM category WHERE parent_id IS NULL ORDER BY id ASC";
$mainCategories = $conn->query($sql);
?>

<!-- Body -->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">

    <!--Header-->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!--Sidebar-->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!-- Main -->
    <main class="app-main">

      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <!-- Page Title -->
          <h3 class="mb-0 " style="font-weight: 800;">All Categories</h3>

          <!-- Add User Button -->
          <?php if (can('add_category')): ?>
            <a href="add.php" class="btn btn-sm btn-primary px-3 py-2" style=" font-size: medium; ">
              <i class="bi bi-plus me-1"></i> Add New Category
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


      <!-- Toolbar: Product Search + Category Filter -->
      <div class="app-content-body mt-3 container-fluid">

        <!-- Toolbar -->
        <div class="table-toolbar p-3 mb-3 rounded shadow-sm bg-white d-flex flex-wrap align-items-end gap-3">

          <!-- Supplier Search -->
          <div class="d-flex flex-column flex-grow-1" style="min-width: 200px;">
            <label for="supplierSearch" class="form-label fw-semibold mb-1">Search Supplier</label>
            <input type="text" id="supplierSearch" class="form-control" placeholder="Type supplier name...">
          </div>

          <!-- Toggle: Include/Exclude Categories with Subcategories -->
          <div class="d-flex flex-column justify-content-end" style="min-width: 200px;">
            <label class="form-label fw-semibold mb-1">Exclude Categories with Subcategories</label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="excludeSubCategories">
            </div>
          </div>

          <!-- Reset Filters -->
          <div class="d-flex flex-column align-items-start" style="min-width: 120px;">
            <label class="form-label mb-1">&nbsp;</label>
            <button id="resetFilters" class=" btn btn-secondary w-100 d-flex align-items-center justify-content-center gap-2">
              <i class="bi bi-arrow-counterclockwise reset-icon"></i> Reset
            </button>
          </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
          <?php if ($mainCategories->num_rows > 0): ?>
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
                <?php while ($main = $mainCategories->fetch_assoc()): ?>
                  <!-- Main Category Row -->
                  <?php
                  $mainId = $main['id'];
                  $stmtSub = $conn->prepare("SELECT * FROM category WHERE parent_id = ? ORDER BY id ASC");
                  $stmtSub->bind_param("i", $mainId);
                  $stmtSub->execute();
                  $subCategories = $stmtSub->get_result();
                  $hasSub = $subCategories->num_rows > 0;
                  ?>

                  <tr class="table-primary"
                    data-id="<?= $mainId ?>"
                    data-name="<?= htmlspecialchars(strtolower($main['name'])) ?>"
                    data-hassub="<?= $hasSub ? 1 : 0 ?>">
                    <td><?= htmlspecialchars($mainId) ?></td>
                    <td><?= htmlspecialchars($main['name']) ?></td>
                    <td><?= htmlspecialchars($main['description']) ?></td>
                    <td><?= !empty($main['created_at']) ? date('d M Y h:i A', strtotime($main['created_at'])) : '' ?></td>
                    <td><?= !empty($main['updated_at']) ? date('d M Y h:i A', strtotime($main['updated_at'])) : '' ?></td>
                    <td>
                      <div class="d-flex gap-1">
                        <?php if (can('edit_category')): ?>
                          <a href="edit.php?id=<?= urlencode($mainId) ?>" class="btn btn-warning btn-sm flex-fill">
                            <i class="bi bi-pencil-square"></i> Edit
                          </a>
                        <?php endif; ?>
                        <?php if (can('delete_category')): ?>
                          <a href="delete.php?id=<?= urlencode($mainId) ?>" class="btn btn-danger btn-sm flex-fill"
                            onclick="return confirm('Are you sure you want to delete this category?');">
                            <i class="bi bi-trash"></i> Delete
                          </a>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>

                  <!-- Subcategory Rows -->
                  <?php while ($sub = $subCategories->fetch_assoc()): ?>
                    <tr class="table-light"
                      data-id="<?= $sub['id'] ?>"
                      data-name="<?= htmlspecialchars(strtolower($sub['name'])) ?>"
                      data-parent="<?= $mainId ?>">
                      <td><?= htmlspecialchars($sub['id']) ?></td>
                      <td>&nbsp;&nbsp;&nbsp;↳ <?= htmlspecialchars($sub['name']) ?></td>
                      <td><?= htmlspecialchars($sub['description']) ?></td>
                      <td><?= !empty($sub['created_at']) ? date('d M Y h:i A', strtotime($sub['created_at'])) : '' ?></td>
                      <td><?= !empty($sub['updated_at']) ? date('d M Y h:i A', strtotime($sub['updated_at'])) : '' ?></td>
                      <td>
                        <div class="d-flex gap-1">
                          <?php if (can('edit_category')): ?>
                            <a href="edit.php?id=<?= urlencode($sub['id']) ?>" class="btn btn-warning btn-sm flex-fill">
                              <i class="bi bi-pencil-square"></i> Edit
                            </a>
                          <?php endif; ?>
                          <?php if (can('delete_category')): ?>
                            <a href="delete.php?id=<?= urlencode($sub['id']) ?>" class="btn btn-danger btn-sm flex-fill"
                              onclick="return confirm('Are you sure you want to delete this subcategory?');">
                              <i class="bi bi-trash"></i> Delete
                            </a>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endwhile;
                  $stmtSub->close(); ?>

                <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              <h5>No categories found</h5>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </main>

    <!--Footer-->
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
      $('#categoriesTable').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false,
        dom: '<"top-pagination d-flex justify-content-between mb-2"lp>rt<"bottom-pagination"ip>',
        language: {
          search: "" // Remove default search
        }
      });
    });

    // Remove success/failure message
    setTimeout(() => {
      const msg = document.getElementById('successMsg') || document.getElementById('failMsg');
      if (msg) msg.remove();
    }, 3000);
  </script>

  <!-- Filter JS -->
  <script>
    $(document).ready(function() {
      const $categoryFilter = $('#categoryFilter');
      const $excludeToggle = $('#excludeSubCategories');
      const $supplierSearch = $('#supplierSearch');
      const $tableRows = $('#categoriesTable tbody tr');

      function filterTable() {
        const searchText = $supplierSearch.val().toLowerCase();
        const selectedCat = $categoryFilter.val();
        const excludeSub = $excludeToggle.is(':checked');

        $tableRows.each(function() {
          const $row = $(this);
          const name = $row.data('name') || '';
          const rowId = $row.data('id');
          const parentId = $row.data('parent') || null;

          // Search filter
          let matchesSearch = name.includes(searchText);

          // Category filter
          let matchesCategory = true;
          if (selectedCat) {
            if (parentId) {
              matchesCategory = parentId == selectedCat;
            } else {
              matchesCategory = rowId == selectedCat;
            }
          }

          // Exclude subcategories
          let matchesExcludeSub = true;
          if (excludeSub) {
            matchesExcludeSub = !$row.hasClass('table-primary') || $row.data('hassub') == 0;
          }

          if (matchesSearch && matchesCategory && matchesExcludeSub) {
            $row.show();
          } else {
            $row.hide();
          }
        });
      }

      // Trigger filter on change
      $supplierSearch.on('input', filterTable);
      $categoryFilter.on('change', filterTable);
      $excludeToggle.on('change', filterTable);

      // Reset filters
      $('#resetFilters').on('click', function() {
        $supplierSearch.val('');
        $categoryFilter.prop('selectedIndex', 0);
        $excludeToggle.prop('checked', false);
        $tableRows.show();
      });
    });
  </script>


</body>

</html>