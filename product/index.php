<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('view_products', '../index.php');

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
  <title>All Products | Sass Inventory Management System</title>
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

  <!-- DataTables -->
  <link rel="stylesheet"
    href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <!-- Custom CSS -->
  <style>
    /* Buttons hover */
    .btn-warning:hover {
      background-color: #d39e00 !important;
      border-color: #c99700 !important;
    }

    .btn-danger:hover {
      background-color: #bb2d3b !important;
      border-color: #b02a37 !important;
    }

    /* Select2 - match Bootstrap height & fix jump */
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

    /* Toolbar container for better visual separation */
    .table-toolbar {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-start;
      gap: 10px;
      margin-bottom: 1rem;
      align-items: center;
    }

    .table-toolbar .form-control,
    .table-toolbar .form-select {
      min-width: 200px;
    }

    /* Spin animation */
    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    /* Apply spin when .spinning class is added */
    .reset-icon.spinning {
      animation: spin 0.8s linear infinite;
    }
  </style>
</head>

<?php
$conn = connectDB();


// Fetch all products with category & supplier names
$sql = "
SELECT 
    p.id, p.name, p.sku, p.status, p.price AS selling_price, p.quantity_in_stock, p.image,
    c.name AS category_name,
    s.name AS supplier_name
FROM product p
LEFT JOIN category c ON p.category_id = c.id
LEFT JOIN supplier s ON p.supplier_id = s.id
ORDER BY p.id ASC
";
$result = $conn->query($sql);
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
          <h3 class="mb-0 " style="font-weight: 800;">All Products</h3>

          <!-- Add Product Button -->
          <?php if (can('add_product')): ?>
            <a href="add.php" class="btn btn-sm btn-primary px-3 py-2" style=" font-size: medium; ">
              <i class="bi bi-plus me-1"></i> Add New Product
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

          <!-- Category Filter -->
          <div class="d-flex flex-column" style="min-width: 200px;">
            <label for="categoryFilter" class="form-label fw-semibold mb-1">Filter by Category</label>
            <select id="categoryFilter" class="form-select">
              <option value="">All Categories</option>
              <?php
              // Only categories that have products
              $catResult = $conn->query("
              SELECT DISTINCT c.name
              FROM category c
              JOIN product p ON p.category_id = c.id
              ORDER BY c.name ASC
              ");
              while ($cat = $catResult->fetch_assoc()) {
                echo "<option value=\"{$cat['name']}\">{$cat['name']}</option>";
              }
              ?>
            </select>
          </div>

          <!-- Supplier Filter -->
          <div class="d-flex flex-column" style="min-width: 200px;">
            <label for="supplierFilter" class="form-label fw-semibold mb-1">Filter by Supplier</label>
            <select id="supplierFilter" class="form-select">
              <option value="">All Suppliers</option>
              <?php
              // Only suppliers that have products
              $supResult = $conn->query("
              SELECT DISTINCT s.name
              FROM supplier s
              JOIN product p ON p.supplier_id = s.id
              ORDER BY s.name ASC
              ");
              while ($sup = $supResult->fetch_assoc()) {
                echo "<option value=\"{$sup['name']}\">{$sup['name']}</option>";
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
          <table id="productsTable" class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-primary">
              <tr>
                <th>Image</th>
                <th>Name</th>
                <th>SKU</th>
                <th>Status</th>
                <th>Category</th>
                <th>Supplier</th>
                <th>Selling Price</th>
                <th>Stock</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td>
                    <img
                      src="<?= !empty($row['image'])
                              ? htmlspecialchars($Project_URL . 'assets/products/' . $row['image'])
                              : 'https://community.softr.io/uploads/db9110/original/2X/7/74e6e7e382d0ff5d7773ca9a87e6f6f8817a68a6.jpeg' ?>"
                      alt="<?= htmlspecialchars($row['name']) ?>"
                      class="img-thumbnail"
                      style="max-width:50px; max-height:50px;">
                  </td>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= htmlspecialchars($row['sku']) ?: "—" ?></td>
                  <td><?= ucfirst($row['status']) ?></td>
                  <td><?= htmlspecialchars($row['category_name']) ?: "—" ?></td>
                  <td><?= htmlspecialchars($row['supplier_name']) ?: "—" ?></td>
                  <td><?= number_format($row['selling_price'], 2) ?></td>
                  <td><?= $row['quantity_in_stock'] ?></td>
                  <td>
                    <div class="d-flex gap-1">
                      <!-- View -->
                      <button
                        class="btn btn-info btn-sm flex-fill view-product-btn"
                        data-id="<?= $row['id'] ?>"
                        title="View">
                        <i class="bi bi-eye"></i>
                      </button>

                      <!-- Edit -->
                      <?php if (can('edit_product')): ?>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm flex-fill" title="Edit">
                          <i class="bi bi-pencil-square"></i>
                        </a>
                      <?php endif; ?>

                      <!-- Delete -->
                      <?php if (can('delete_product')): ?>
                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm flex-fill" title="Delete" onclick="return confirm('Delete this product?');">
                          <i class="bi bi-trash"></i>
                        </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Modal -->
    <div class="modal fade" id="viewProductModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">
              <i class="bi bi-box-seam me-1"></i> Product Details
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div id="productModalLoader" class="text-center py-5">
              <div class="spinner-border text-primary"></div>
            </div>

            <div id="productModalContent" class="d-none">
              <div class="row g-3">
                <div class="col-md-4 text-center">
                  <img id="pm_image" class="img-fluid rounded border" />
                </div>

                <div class="col-md-8">
                  <table class="table table-sm table-bordered">
                    <tbody>
                      <tr>
                        <th>Name</th>
                        <td id="pm_name"></td>
                      </tr>
                      <tr>
                        <th>SKU</th>
                        <td id="pm_sku"></td>
                      </tr>
                      <tr>
                        <th>Status</th>
                        <td id="pm_status"></td>
                      </tr>
                      <tr>
                        <th>Category</th>
                        <td id="pm_category"></td>
                      </tr>
                      <tr>
                        <th>Subcategory</th>
                        <td id="pm_subcategory"></td>
                      </tr>
                      <tr>
                        <th>Supplier</th>
                        <td id="pm_supplier"></td>
                      </tr>
                      <tr>
                        <th>Cost Price</th>
                        <td id="pm_cost"></td>
                      </tr>
                      <tr>
                        <th>Selling Price</th>
                        <td id="pm_selling"></td>
                      </tr>
                      <tr>
                        <th>VAT</th>
                        <td id="pm_vat"></td>
                      </tr>
                      <tr>
                        <th>Final Price</th>
                        <td id="pm_price"></td>
                      </tr>
                      <tr>
                        <th>Stock</th>
                        <td id="pm_stock"></td>
                      </tr>
                      <tr>
                        <th>Low Stock Limit</th>
                        <td id="pm_low_stock"></td>
                      </tr>
                      <tr>
                        <th>Created</th>
                        <td id="pm_created"></td>
                      </tr>
                      <tr>
                        <th>Updated</th>
                        <td id="pm_updated"></td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div class="col-12">
                  <h6>Description</h6>
                  <p id="pm_description" class="border rounded p-2 bg-light"></p>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>

        </div>
      </div>
    </div>


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

  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- Custom JS -->
  <script>
    /* 
   GLOBAL DATA TABLE INSTANCE
   - Needed so filters & reset can access it
   */
    let table;

    $(document).ready(function() {

      /* 
         DATA TABLE INITIALIZATION
          */
      table = $('#productsTable').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false,
        dom: '<"top-pagination d-flex justify-content-between mb-2"lp>rt<"bottom-pagination"ip>',
        language: {
          search: "" // remove default search box
        }
      });

      /* 
         AUTO-HIDE SUCCESS / FAIL MESSAGES
          */
      setTimeout(() => {
        const msg = document.getElementById('successMsg') || document.getElementById('failMsg');
        if (msg) msg.remove();
      }, 3000);

      /* 
         SELECT2 INITIALIZATION (FILTERS)
          */
      $('#categoryFilter, #supplierFilter').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%',
        dropdownParent: $('body')
      });

      /* 
         TEXT SEARCH (PRODUCT NAME, SKU, ETC.)
          */
      $('#productSearch').on('keyup', function() {
        table.search(this.value).draw();
      });

      /* 
         CATEGORY FILTER
         Column index 4 = Category
          */
      $('#categoryFilter').on('change', function() {
        const val = $(this).val();
        table.column(4).search(val || '').draw();
      });

      /* 
         SUPPLIER FILTER
         Column index 5 = Supplier
          */
      $('#supplierFilter').on('change', function() {
        const val = $(this).val();
        table.column(5).search(val || '').draw();
      });

      /* 
         RESET FILTERS + SEARCH (WITH SPIN)
          */
      $('#resetFilters').on('click', function() {

        const icon = $(this).find('.reset-icon');
        icon.addClass('spinning');

        // Clear inputs
        $('#productSearch').val('');
        $('#categoryFilter').val('').trigger('change');
        $('#supplierFilter').val('').trigger('change');

        // Reset DataTable
        table.search('').columns().search('').draw();

        // Stop animation
        setTimeout(() => {
          icon.removeClass('spinning');
        }, 800);
      });

    });
  </script>

  <!-- Modal JS -->
  <script>
    $(document).ready(function() {

      // Handle View Product button click
      $(document).on('click', '.view-product-btn', function() {

        const productId = $(this).data('id');

        // Show modal loader
        $('#viewProductModal').modal('show');
        $('#productModalLoader').show();
        $('#productModalContent').addClass('d-none');

        // Fetch product data via AJAX
        $.getJSON('<?= $Project_URL ?>product/get_product.php', {
            id: productId
          })
          .done(function(d) {
            // Fallbacks for missing data
            const imageSrc = d.image ?
              '<?= $Project_URL ?>/assets/products/' + d.image :
              'https://community.softr.io/uploads/db9110/original/2X/7/74e6e7e382d0ff5d7773ca9a87e6f6f8817a68a6.jpeg';

            $('#pm_image').attr('src', imageSrc);
            $('#pm_name').text(d.name || '—');
            $('#pm_sku').text(d.sku || '—');
            $('#pm_status').text(d.status || '—');
            $('#pm_category').text(d.category_name || '—');
            $('#pm_subcategory').text(d.subcategory_name || '—');
            $('#pm_supplier').text(d.supplier_name || '—');
            $('#pm_cost').text(d.cost_price != null ? d.cost_price : '0');
            $('#pm_selling').text(d.selling_price != null ? d.selling_price : '0');
            $('#pm_vat').text(d.vat != null ? d.vat + '%' : '0%');
            $('#pm_price').text(d.price != null ? d.price : '0');
            $('#pm_stock').text(d.quantity_in_stock != null ? d.quantity_in_stock : '0');
            $('#pm_low_stock').text(d.low_stock_limit != null ? d.low_stock_limit : '0');
            $('#pm_description').text(d.description && d.description !== '0' ? d.description : '—');
            $('#pm_created').text(d.created_at || '—');
            $('#pm_updated').text(d.updated_at || '—');

            // Hide loader and show content
            $('#productModalLoader').hide();
            $('#productModalContent').removeClass('d-none');
          })
          .fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Failed to fetch product:', textStatus, errorThrown);
            alert('Failed to load product details. Please try again.');
            $('#viewProductModal').modal('hide');
          });

      });

    });
  </script>


</body>

</html>