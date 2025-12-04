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
    }

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

    .stock-state {
      display: block;
      width: 100%;
      text-align: center;
      font-weight: 600;
      color: #fff;
      padding: 3px 0;
      /* adjust vertical padding */
      border-radius: 5px;
    }

    .bg-primary-soft {
      background-color: #4a90e2 !important;
    }

    /* medium blue */
    .bg-success-soft {
      background-color: #28a745 !important;
    }

    /* medium green */
    .bg-danger-soft {
      background-color: #dc3545 !important;
    }

    /* medium red */
    .bg-warning-soft {
      background-color: #f0ad4e !important;
    }

    /* medium orange */
    .bg-info-soft {
      background-color: #17a2b8 !important;
    }

    /* medium cyan */
    .bg-secondary-soft {
      background-color: #6c757d !important;
    }

    /* medium gray */
  </style>
</head>

<?php
$conn = connectDB();

// Fetch all products from view
$sql = "SELECT * FROM product_with_details ORDER BY name ASC";
$result = $conn->query($sql);

// Stock thresholds
$criticallyLowThreshold = 200;
$lowStockThreshold  = 500;
$overStockThreshold = 1000; // adjust as needed

$totalProducts = $result->num_rows;
$totalStock = 0;
$criticallyLowCount = 0;
$lowStockCount = 0;
$overStockCount = 0;
$emptyStockCount = 0;

$productsData = [];

while ($row = $result->fetch_assoc()) {
  $qty = $row['quantity_in_stock'];
  $totalStock += $qty;

  // Determine stock state
  if ($qty == 0) {
    $state = "Empty";
    $emptyStockCount++;
  } elseif ($qty <= $criticallyLowThreshold) {
    $state = "Critically Low";
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
          <h3 class="mb-0 " style="font-weight: 800;">Product Stock</h3>
        </div>
      </div>

      <!-- Content -->
      <div class="container-fluid mt-4">
        <!-- Stock Cards -->
        <div class="row g-3 mb-4">
          <?php
          $cards = [
            ['count' => $totalProducts, 'title' => 'Total Products', 'desc' => 'All items', 'bg' => 'bg-primary-soft', 'text' => 'text-white', 'icon' => 'bi-box-seam'],
            ['count' => $totalStock, 'title' => 'Total Units', 'desc' => 'Sum of all stock', 'bg' => 'bg-success-soft', 'text' => 'text-white', 'icon' => 'bi-stack'],
            ['count' => $criticallyLowCount, 'title' => 'Critically Low', 'desc' => "Very low stock ({$criticallyLowThreshold} units or fewer)", 'bg' => 'bg-danger-soft', 'text' => 'text-white', 'icon' => 'bi-exclamation-triangle'],
            ['count' => $lowStockCount, 'title' => 'Low Stock', 'desc' => "Low stock ({$lowStockThreshold} units or fewer)", 'bg' => 'bg-warning-soft', 'text' => 'text-dark', 'icon' => 'bi-exclamation-circle'],
            ['count' => $overStockCount, 'title' => 'Overstocked', 'desc' => "High stock (more than {$overStockThreshold} units)", 'bg' => 'bg-info-soft', 'text' => 'text-white', 'icon' => 'bi-arrow-up-circle'],
            ['count' => $emptyStockCount, 'title' => 'Empty', 'desc' => 'No stock available', 'bg' => 'bg-secondary-soft', 'text' => 'text-white', 'icon' => 'bi-x-circle'],
          ];

          foreach ($cards as $card):
          ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
              <div class="card h-100 shadow-sm border-0 <?= $card['bg'] ?> <?= $card['text'] ?> rounded-3">
                <div class="card-body d-flex flex-column justify-content-between">

                  <!-- Card Content -->
                  <div class="d-flex align-items-center mb-2">

                    <!-- Card Text -->
                    <div class="flex-grow-1 d-flex flex-column justify-content-center">
                      <!-- Card Count -->
                      <h4
                        class="card-title  mb-1"
                        style="font-weight: 600; font-size: x-large;"><?= $card['count'] ?></h4>

                      <!-- Card Title -->
                      <small class="card-subtitle"><?= $card['title'] ?></small>
                    </div>

                    <!-- Card Icon -->
                    <i class="bi <?= $card['icon'] ?> fs-2 ms-2"></i>
                  </div>

                  <!-- Card Description -->
                  <p class="card-text small mb-0"><?= $card['desc'] ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Products Table -->
        <?php if ($result->num_rows > 0): ?>
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
                  $stateClass = str_replace(' ', '', $row['stock_state']); // remove spaces for class
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
        <?php else: ?>
          <div class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            <h5>No products found</h5>
          </div>
        <?php endif; ?>
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
      $('#stockTable').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false
      });
    });
  </script>
</body>

</html>