<?php
include_once __DIR__ . '/config/auth_guard.php';

// Check if user is logged in
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
  // Redirect to login page
  header("Location: ./auth/login.php");
  exit();
}

$conn = connectDB();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Admin Home | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Fonts & CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="./css/adminlte.css" />

  <style>
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .card-footer-custom {
      background-color: rgba(0, 0, 0, 0.15);
      transition: background-color 0.3s, transform 0.3s;
      border-top: 1px solid rgba(0, 0, 0, 0.1);
    }

    .card-footer-custom:hover {
      background-color: rgba(0, 0, 0, 0.25);
      transform: translateY(-2px);
    }
  </style>
</head>

<?php
// Dashboard cards
$dashboardCards = [
  [
    "title" => "Total Users",
    "icon" => "bi bi-people fs-2",
    "bg" => "bg-success",
    "query" => "SELECT COUNT(*) AS value FROM user",
    "link" => "./auth/users.php",
    "format" => "number",
    "permission" => "view_users"
  ],
  [
    "title" => "Total Products",
    "icon" => "bi bi-box-seam fs-2",
    "bg" => "bg-primary",
    "query" => "SELECT COUNT(*) AS value FROM product",
    "link" => "./product/index.php",
    "format" => "number",
    "permission" => "view_products"
  ],
  [
    "title" => "Total Stock",
    "icon" => "bi bi-stack fs-2",
    "bg" => "bg-info",
    "query" => "SELECT SUM(quantity_in_stock) AS value FROM product",
    "link" => "./product/stock.php",
    "format" => "number",
    "permission" => "view_stock_overview"
  ],
  [
    "title" => "Total Purchased Qua",
    "icon" => "bi bi-cart-plus fs-2",
    "bg" => "bg-secondary",
    "query" => "SELECT SUM(quantity) AS value FROM purchase",
    "link" => "./purchase/index.php",
    "format" => "number",
    "permission" => "view_all_purchases"
  ],
  [
    "title" => "Total Sold Qua",
    "icon" => "bi bi-cart-check fs-2",
    "bg" => "bg-danger",
    "query" => "SELECT SUM(quantity) AS value FROM sale",
    "link" => "./sales/index.php",
    "format" => "number",
    "permission" => "view_all_sales"
  ],
  [
    "title" => "Total Sales ($)",
    "icon" => "bi bi-currency-dollar fs-2",
    "bg" => "bg-primary",
    "query" => "SELECT SUM(sale_price) AS value FROM sale",
    "link" => "./sales/index.php",
    "format" => "currency",
    "permission" => "view_all_sales"
  ],
  [
    "title" => "Total Purchase ($)",
    "icon" => "bi bi-receipt fs-2",
    "bg" => "bg-dark",
    "query" => "SELECT SUM(purchase_price) AS value FROM purchase",
    "link" => "./purchase/index.php",
    "format" => "currency",
    "permission" => "view_all_purchases"
  ],
  [
    "title" => "Low Stock Products",
    "icon" => "bi bi-exclamation-triangle fs-2",
    "bg" => "bg-danger",
    "query" => "SELECT COUNT(*) AS value FROM product WHERE quantity_in_stock < 100",
    "link" => "./reports/low_stock.php",
    "format" => "number",
    "permission" => "view_low_stock"
  ],

  // ---- My Part ----
  [
    "title" => "My Purchases",
    "icon" => "bi bi-cart-plus-fill fs-2",
    "bg" => "bg-warning",
    "query" => "SELECT SUM(quantity) AS value FROM purchase WHERE purchased_by = '{$_SESSION['user_id']}'",
    "link" => "./purchase/my_purchases.php",
    "format" => "number",
    "permission" => "view_my_purchases"
  ],
  [
    "title" => "My Sales",
    "icon" => "bi bi-cart-check-fill fs-2",
    "bg" => "bg-success",
    "query" => "SELECT SUM(quantity) AS value FROM sale WHERE sold_by = '{$_SESSION['user_id']}'",
    "link" => "./sales/my_sales.php",
    "format" => "number",
    "permission" => "view_my_sales"
  ],
  [
    "title" => "My Purchase Amount",
    "icon" => "bi bi-cash-coin fs-2",
    "bg" => "bg-dark",
    "query" => "SELECT COALESCE(SUM(purchase_price ), 0) AS value 
              FROM purchase 
              WHERE purchased_by = '{$_SESSION['user_id']}'",
    "link" => "./purchase/my_purchases.php",
    "format" => "currency",
    "permission" => "view_my_purchases"
  ],
  [
    "title" => "My Sales Amount",
    "icon" => "bi bi-currency-dollar fs-2",
    "bg" => "bg-success",
    "query" => "SELECT COALESCE(SUM(sale_price), 0) AS value 
              FROM sale 
              WHERE sold_by = '{$_SESSION['user_id']}'",
    "link" => "./sales/my_sales.php",
    "format" => "currency",
    "permission" => "view_my_sales"
  ],

];

// Fetch purchase quantity grouped by date
$query = "
    SELECT 
        DATE(purchase_date) AS date,
        SUM(quantity) AS total_quantity
    FROM purchase
    GROUP BY DATE(purchase_date)
    ORDER BY DATE(purchase_date)
";

$result = $conn->query($query);

$dates = [];
$quantities = [];

while ($row = $result->fetch_assoc()) {
  $dates[] = $row['date'];
  $quantities[] = (int)$row['total_quantity'];
}
?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <!-- Header -->
    <?php include_once './Inc/Navbar.php'; ?>

    <!-- Sidebar -->
    <?php include_once './Inc/Sidebar.php'; ?>

    <!-- Main -->
    <main class="app-main">

      <!-- Header -->
      <h3 class="my-3 text-center fw-bold position-relative d-inline-block mx-auto">
        Admin Dashboard
        <span class="d-block mx-auto mt-1"
          style="width: 60px; height: 3px; background-color: #0d6efd; border-radius: 2px;"></span>
      </h3>

      <!-- Content -->
      <div class="content-header pt-1">
        <div class="container-fluid px-4">
          <!-- Page Title -->
          <h3 class="mb-3">Dashboard Overview</h3>

          <!-- Dashboard Cards -->
          <div class="row">
            <?php foreach ($dashboardCards as $card):
              if (!hasPermission($card['permission'])) continue;

              $result = $conn->query($card['query']);
              $row = $result->fetch_assoc();
              $value = $row['value'] ?? 0;
              if ($card['format'] === "currency") $value = '$' . number_format($value, 2);
            ?>
              <div class="col-xl-3 col-sm-6 mb-4">
                <div class="card text-white <?= $card['bg'] ?> o-hidden h-100 shadow-sm" style="border-radius: 10px; transition: transform 0.2s;">
                  <!-- Card Body -->
                  <div class="card-body d-flex align-items-center">
                    <!-- Card Icon -->
                    <div class="card-body-icon me-3"><i class="<?= $card['icon'] ?> fs-2"></i></div>

                    <!-- Card Content -->
                    <div>
                      <div class="h5" style="font-weight: 600; font-size: medium;"><?= $card['title'] ?></div>
                      <div class="fw-bold fs-5"><?= $value ?></div>
                    </div>
                  </div>

                  <!-- Footer -->
                  <a class="card-footer text-white clearfix small z-1" href="<?= $card['link'] ?>">
                    <span class="float-start">View Details</span>
                    <span class="float-end"><i class="fas fa-angle-right"></i></span>
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="container-fluid px-4">
        <?php include_once './Inc/Admin/all_purchase_quantity.php'; ?>
      </div>

      <div class="container-fluid px-4">
        <?php include_once './Inc/Admin/my_purchase_quantity.php'; ?>
      </div>

      <div class="container-fluid px-4">
        <?php include_once './Inc/Admin/all_sale_quantity.php'; ?>
      </div>

      <div class="container-fluid px-4">
        <?php include_once './Inc/Admin/my_sale_quantity.php'; ?>
      </div>
    </main>

    <!-- Footer -->
    <?php include_once './Inc/Footer.php'; ?>
  </div>

  <!-- JS Plugins -->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
  <script src="./js/adminlte.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const sidebarWrapper = document.querySelector('.sidebar-wrapper');
      const isMobile = window.innerWidth <= 992;
      if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined && !isMobile) {
        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
          scrollbars: {
            theme: 'os-theme-light',
            autoHide: 'leave',
            clickScroll: true
          },
        });
      }
    });
  </script>
</body>

</html>