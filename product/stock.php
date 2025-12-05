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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stock Overview | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">

  <!-- Fonts -->
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    media="print" onload="this.media='all'" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />

  <!-- AdminLTE -->
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />

  <!-- DataTables -->
  <link rel="stylesheet"
    href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <!-- Custom CSS -->
  <style>
    /* Stock Cards */
    .stock-card {
      border-radius: 12px;
      padding: 20px;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .stock-card h3 {
      font-weight: 700;
    }

    .stock-state {
      font-weight: 600;
      color: #fff;
      padding: 3px 6px;
      border-radius: 5px;
      display: inline-block;
      text-align: center;
      width: 100%;
    }

    /* Stock States */
    .Empty {
      background-color: #6c757d;
    }

    .CriticallyLow {
      background-color: #dc3545;
    }

    .Low {
      background-color: #fd7e14;
    }

    .OK {
      background-color: #28a745;
    }

    .Overstocked {
      background-color: #007bff;
    }

    /* Table Row Colors by Stock */
    table tbody tr.Empty {
      background-color: #f8f9fa;
    }

    table tbody tr.CriticallyLow {
      background-color: #f8d7da;
    }

    table tbody tr.Low {
      background-color: #fff3cd;
    }

    table tbody tr.OK {
      background-color: #d4edda;
    }

    table tbody tr.Overstocked {
      background-color: #cce5ff;
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

// Fetch all products with details
$sql = "SELECT * FROM product_with_details ORDER BY name ASC";
$result = $conn->query($sql);

// Stock thresholds
$criticallyLowThreshold = 200;
$lowStockThreshold = 500;
$overStockThreshold = 1000;

// Counters for stock cards
$totalProducts = $result->num_rows;
$totalStock = 0;
$criticallyLowCount = 0;
$lowStockCount = 0;
$overStockCount = 0;
$emptyStockCount = 0;

$productsData = [];

// Process each product for stock state
while ($row = $result->fetch_assoc()) {
  $qty = $row['quantity_in_stock'];
  $totalStock += $qty;

  // Determine stock state
  if ($qty == 0) {
    $state = "Empty";
    $emptyStockCount++;
  } elseif ($qty <= $criticallyLowThreshold) {
    $state = "CriticallyLow";
    $criticallyLowCount++;
  } elseif ($qty <= $lowStockThreshold) {
    $state = "Low";
    $lowStockCount++;
  } elseif ($qty > $overStockThreshold) {
    $state = "Overstocked";
    $overStockCount++;
  } else {
    $state = "OK";
  }

  $row['stock_state'] = $state;
  $productsData[] = $row;
}
?>

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
          <h3 class="mb-0" style="font-weight: 800;">Product Stock Overview</h3>
        </div>
      </div>

      <div class="container-fluid mt-4">

        <!-- Stock Cards -->
        <div class="row g-3 mb-4">
          <?php
          $cards = [
            ['count' => $totalProducts, 'title' => 'Total Products', 'desc' => 'All items', 'bg' => 'bg-primary', 'text' => 'text-white', 'icon' => 'bi-box-seam'],
            ['count' => $totalStock, 'title' => 'Total Units', 'desc' => 'Sum of all stock', 'bg' => 'bg-success', 'text' => 'text-white', 'icon' => 'bi-stack'],
            ['count' => $criticallyLowCount, 'title' => 'Critically Low', 'desc' => "Very low stock ({$criticallyLowThreshold} or less)", 'bg' => 'bg-danger', 'text' => 'text-white', 'icon' => 'bi-exclamation-triangle'],
            ['count' => $lowStockCount, 'title' => 'Low Stock', 'desc' => "Low stock ({$lowStockThreshold} or less)", 'bg' => 'bg-warning', 'text' => 'text-dark', 'icon' => 'bi-exclamation-circle'],
            ['count' => $overStockCount, 'title' => 'Overstocked', 'desc' => "High stock (more than {$overStockThreshold})", 'bg' => 'bg-info', 'text' => 'text-white', 'icon' => 'bi-arrow-up-circle'],
            ['count' => $emptyStockCount, 'title' => 'Empty', 'desc' => 'No stock available', 'bg' => 'bg-secondary', 'text' => 'text-white', 'icon' => 'bi-x-circle'],
          ];
          foreach ($cards as $card):
          ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
              <div class="card h-100 shadow-sm border-0 <?= $card['bg'] ?> <?= $card['text'] ?> rounded-3">
                <div class="card-body d-flex flex-column justify-content-between">
                  <div class="d-flex align-items-center mb-2">
                    <div class="flex-grow-1 d-flex flex-column justify-content-center">
                      <h4 class="card-title mb-1" style="font-weight:600;font-size:x-large;"><?= $card['count'] ?></h4>
                      <small class="card-subtitle"><?= $card['title'] ?></small>
                    </div>
                    <i class="bi <?= $card['icon'] ?> fs-2 ms-2"></i>
                  </div>
                  <p class="card-text small mb-0"><?= $card['desc'] ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

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
              SELECT c.name 
              FROM category c
              JOIN product_with_details p ON p.category_name = c.name
              GROUP BY c.name
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
              SELECT s.name 
              FROM supplier s
              JOIN product_with_details p ON p.supplier_name = s.name
              GROUP BY s.name
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

        <!-- Stock Table -->
        <div class="table-responsive">
          <table id="stockTable" class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-primary">
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Supplier</th>
                <th>Price</th>
                <th>Stock</th>
                <th>State</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($productsData as $row):
                $stateClass = $row['stock_state'];
              ?>
                <tr class="<?= $stateClass ?>">
                  <td><?= $row['id'] ?></td>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= $row['category_name'] ?: '—' ?></td>
                  <td><?= $row['supplier_name'] ?: '—' ?></td>
                  <td><?= number_format($row['price'], 2) ?></td>
                  <td><?= $row['quantity_in_stock'] ?></td>
                  <td><span class="stock-state <?= $stateClass ?>"><?= $row['stock_state'] ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
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

  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- Custom JS -->
  <script>
    $(document).ready(function() {
      // Initialize DataTable
      var table = $('#stockTable').DataTable({
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

      // Reset all filters and search with animation
      $('#resetFilters').on('click', function() {
        var icon = $(this).find('.reset-icon');

        // Add spin class
        icon.addClass('spinning');

        // Clear DataTable search
        $('#productSearch').val('');
        table.search('').draw();

        // Reset Select2 filters
        $('#categoryFilter').val('').trigger('change');
        $('#supplierFilter').val('').trigger('change');

        // Remove spin after 800ms
        setTimeout(function() {
          icon.removeClass('spinning');
        }, 800);
      });

      // Initialize Select2 for category and supplier filters
      $('#categoryFilter, #supplierFilter').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%',
        dropdownParent: $('body'),
        // Custom matcher to always show "All" option
        matcher: function(params, data) {
          // Always show empty option (value="")
          if (data.id === "") {
            return data;
          }

          // Default matching
          if ($.trim(params.term) === '') {
            return data;
          }

          if (typeof data.text === 'undefined') {
            return null;
          }

          if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
            return data;
          }

          // Return null if no match
          return null;
        }
      });

      // Filter table by category
      $('#categoryFilter').on('change', function() {
        var selectedCategory = $(this).val();
        if (!selectedCategory) {
          // Reset filter when "All Categories" is selected
          table.column(2).search('').draw();
        } else {
          table.column(2).search(selectedCategory).draw(); // column 2 = Category
        }
      });

      // Filter table by supplier
      $('#supplierFilter').on('change', function() {
        var selectedSupplier = $(this).val();
        if (!selectedSupplier) {
          // Reset filter when "All Suppliers" is selected
          table.column(3).search('').draw();
        } else {
          table.column(3).search(selectedSupplier).draw(); // column 3 = Supplier
        }
      });

      // Search products by name
      $('#productSearch').on('keyup', function() {
        table.search(this.value).draw();
      });

    });
  </script>

</body>

</html>