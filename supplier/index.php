<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('view_suppliers', '../index.php');

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
  <title>All Suppliers | Sass Inventory Management System</title>
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

  <!-- select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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

  <!-- Your custom Select2 CSS -->
  <style>
    .select2-container--default .select2-selection--single {
      background-color: #fff;
      color: #000;
      border: 1px solid #ced4da;
      border-radius: 0.25rem;
      height: calc(1.5em + 0.75rem + 2px);
      line-height: 1.5;
      padding: 0.375rem 0.75rem;
      box-sizing: border-box;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #000;
      line-height: 1.5;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
      border-color: #000 transparent transparent transparent;
    }

    .select2-container--default .select2-dropdown {
      background-color: #fff;
      color: #000;
    }

    .select2-container--default .select2-results__option {
      color: #000;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
      background-color: #fff;
      color: #000;
      height: auto;
      line-height: 1.5;
    }
  </style>
</head>

<?php
$conn = connectDB();

// Fetch all suppliers
$sql = " 
SELECT  
  s.id, 
  s.name, 
  s.phone, 
  s.email, 
  s.type, 
  s.created_at, 
  s.updated_at, 
  GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ', ') AS categories 
FROM supplier s 
LEFT JOIN supplier_category sc ON sc.supplier_id = s.id 
LEFT JOIN category c ON c.id = sc.category_id 
GROUP BY s.id 
ORDER BY s.id ASC 
";
$result = $conn->query($sql);
?>

<!-- Body -->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">

    <!-- Navbar -->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!-- Sidebar -->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!-- Main -->
    <main class="app-main">

      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <!-- Page Title -->
          <h3 class="mb-0 " style="font-weight: 800;">All Suppliers</h3>

          <!-- Add User Button -->
          <?php if (can('add_supplier')): ?>
            <a href="add.php" class="btn btn-sm btn-primary px-3 py-2" style=" font-size: medium; ">
              <i class="bi bi-plus me-1"></i> Add New Supplier
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
          <!-- Product Search -->
          <div class="d-flex flex-column flex-grow-1" style="min-width: 200px;">
            <label for="productSearch" class="form-label fw-semibold mb-1">Search Product</label>
            <input type="text" id="productSearch" class="form-control" placeholder="Type to search...">
          </div>

          <!-- Type Filter -->
          <div class="d-flex flex-column" style="min-width: 150px;">
            <label for="typeFilter" class="form-label fw-semibold mb-1">Type</label>
            <select id="typeFilter" class="form-select">
              <option value="">All Types</option>
              <?php
              // Fetch unique types
              $typeResult = $conn->query("SELECT DISTINCT type FROM supplier ORDER BY type ASC");
              while ($typeRow = $typeResult->fetch_assoc()):
                if (!empty($typeRow['type'])):
              ?>
                  <option value="<?= htmlspecialchars($typeRow['type']) ?>"><?= ucfirst(htmlspecialchars($typeRow['type'])) ?></option>
              <?php
                endif;
              endwhile;
              ?>
            </select>
          </div>

          <!-- Category Filter -->
          <div class="d-flex flex-column" style="min-width: 200px;">
            <label for="categoryFilter" class="form-label fw-semibold mb-1">Filter by Category</label>
            <select id="categoryFilter" class="form-select">
              <option value="">All Categories</option>
              <?php
              $catResult = $conn->query("
              SELECT c.name 
              FROM category c
              JOIN supplier_category sc ON sc.category_id = c.id
              GROUP BY c.name
              ORDER BY c.name ASC
              ");
              while ($cat = $catResult->fetch_assoc()) {
                echo "<option value=\"{$cat['name']}\">{$cat['name']}</option>";
              }
              ?>
            </select>
          </div>

          <!-- Reset Button -->
          <div class="d-flex flex-column align-items-start" style="min-width: 120px;">
            <label class="form-label mb-1">&nbsp;</label> <!-- Empty label for alignment -->
            <button id="resetFilters" class="btn btn-secondary w-100 d-flex align-items-center justify-content-center gap-2">
              <i class="bi bi-arrow-counterclockwise reset-icon"></i> Reset
            </button>
          </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
          <?php if ($result->num_rows > 0): ?>
            <table id="suppliersTable" class="table table-bordered table-striped table-hover align-middle">
              <thead class="table-primary">
                <tr>
                  <th>ID</th>
                  <th>Supplier</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>Type</th>
                  <th>Categories</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td><?= $row['id'] ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                      <?= $row['type']
                        ? ucfirst(htmlspecialchars($row['type']))
                        : '<span class="text-muted">—</span>' ?>
                    </td>

                    <!-- Categories -->
                    <td>
                      <?php if (!empty($row['categories'])): ?>
                        <?php foreach (explode(',', $row['categories']) as $cat): ?>
                          <span class="badge bg-secondary me-1"><?= trim($cat) ?></span>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
                    </td>

                    <!-- Created -->
                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>

                    <!-- Actions -->
                    <td>

                      <!-- Edit -->
                      <div class="d-flex gap-1">
                        <?php if (can('edit_supplier')): ?>
                          <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i>
                          </a>
                        <?php endif; ?>

                        <!-- Delete -->
                        <?php if (can('delete_supplier')): ?>
                          <a href="delete.php?id=<?= $row['id'] ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete this supplier?');">
                            <i class="bi bi-trash"></i>
                          </a>
                        <?php endif; ?>

                        <!-- View -->
                        <?php if (can('view_suppliers')): ?>
                          <button
                            class="btn btn-info btn-sm viewSupplierBtn"
                            data-name="<?= htmlspecialchars($row['name'] ?? '—') ?>"
                            data-email="<?= htmlspecialchars($row['email'] ?? '—') ?>"
                            data-phone="<?= htmlspecialchars($row['phone'] ?? '—') ?>"
                            data-address="<?= htmlspecialchars($row['address'] ?? 'Not provided') ?>"
                            data-contact="<?= htmlspecialchars($row['contact_person'] ?? 'Not provided') ?>"
                            data-type="<?= htmlspecialchars($row['type'] ?? 'Not specified') ?>"
                            data-categories="<?= htmlspecialchars($row['categories'] ?? 'None') ?>"
                            data-created="<?= !empty($row['created_at']) ? date('d M Y h:i A', strtotime($row['created_at'])) : '—' ?>"
                            data-updated="<?= !empty($row['updated_at']) ? date('d M Y h:i A', strtotime($row['updated_at'])) : '—' ?>">
                            <i class="bi bi-eye"></i>
                          </button>
                        <?php endif; ?>

                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>

          <?php else: ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              <h5>No suppliers found</h5>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <!-- View Supplier Modal -->
  <div class="modal fade" id="viewSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-building"></i> Supplier Details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">

            <div class="col-md-6">
              <strong>Name:</strong>
              <div id="view_name" class="text-muted">—</div>
            </div>

            <div class="col-md-6">
              <strong>Type:</strong>
              <div id="view_type" class="text-muted">—</div>
            </div>

            <div class="col-md-6">
              <strong>Email:</strong>
              <div id="view_email" class="text-muted">—</div>
            </div>

            <div class="col-md-6">
              <strong>Phone:</strong>
              <div id="view_phone" class="text-muted">—</div>
            </div>

            <div class="col-md-12">
              <strong>Address:</strong>
              <div id="view_address" class="text-muted">Not provided</div>
            </div>

            <div class="col-md-6">
              <strong>Contact Person:</strong>
              <div id="view_contact" class="text-muted">Not provided</div>
            </div>

            <div class="col-md-6">
              <strong>Categories:</strong>
              <div id="view_categories" class="text-muted">None</div>
            </div>

            <div class="col-md-6">
              <strong>Created At:</strong>
              <div id="view_created" class="text-muted">—</div>
            </div>

            <div class="col-md-6">
              <strong>Updated At:</strong>
              <div id="view_updated" class="text-muted">—</div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">
            Close
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- JS Dependencies -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
  <script src="<?= $Project_URL ?>/js/adminlte.js"></script>

  <!-- DataTables -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- Select2 -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    $(document).ready(function() {
      // Initialize Select2 for categories
      $('#categoryFilter').select2({
        placeholder: "Select Category",
        allowClear: true,
        width: '100%'
      });

      // Initialize DataTable
      const table = $('#suppliersTable').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false,
        dom: '<"top-pagination d-flex justify-content-between mb-2"lp>rt<"bottom-pagination"ip>',
        language: {
          search: ""
        }
      });

      // Custom search for Name, Phone, Email, Type, Category
      $.fn.dataTable.ext.search.push(function(settings, data) {
        const searchTerm = $('#productSearch').val().toLowerCase();
        const typeTerm = $('#typeFilter').val().toLowerCase();
        const categoryTerm = $('#categoryFilter').val().toLowerCase();

        const name = data[1].toLowerCase(); // Name column
        const phone = data[2].toLowerCase(); // Phone column
        const email = data[3].toLowerCase(); // Email column
        const type = data[4].toLowerCase(); // Type column
        const categoriesRaw = data[5].toLowerCase(); // Comma-separated string like "Cat1, Cat2, Cat3"

        const categories = categoriesRaw.split(',').map(c => c.trim()); // Split & trim

        const matchesSearch = name.includes(searchTerm) || phone.includes(searchTerm) || email.includes(searchTerm);
        const matchesType = typeTerm === "" || type === typeTerm;
        const matchesCategory = categoryTerm === "" || categories.includes(categoryTerm);

        return matchesSearch && matchesType && matchesCategory;
      });


      // Trigger table redraw on search input or filter change
      $('#productSearch, #typeFilter, #categoryFilter').on('keyup change', function() {
        table.draw();
      });

      // Reset button
      $('#resetFilters').on('click', function() {
        $('#productSearch').val('');
        $('#typeFilter').val('');
        $('#categoryFilter').val(null).trigger('change'); // Reset Select2
        table.draw();
      });
    });
  </script>

  <!-- View Supplier -->
  <script>
    $(document).on('click', '.viewSupplierBtn', function() {
      const btn = $(this);

      $('#view_name').text(btn.data('name') || '—');
      $('#view_email').text(btn.data('email') || '—');
      $('#view_phone').text(btn.data('phone') || '—');
      $('#view_address').text(btn.data('address') || 'Not provided');
      $('#view_contact').text(btn.data('contact') || 'Not provided');
      $('#view_type').text(btn.data('type') || 'Not specified');
      $('#view_categories').text(btn.data('categories') || 'None');
      $('#view_created').text(btn.data('created') || '—');
      $('#view_updated').text(btn.data('updated') || '—');

      new bootstrap.Modal(document.getElementById('viewSupplierModal')).show();
    });
  </script>



</body>

</html>