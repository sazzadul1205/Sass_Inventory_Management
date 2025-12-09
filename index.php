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

  // Total Suppliers
  [
    "title" => "Total Suppliers",
    "icon" => "bi bi-truck fs-2",
    "bg" => "bg-info",
    "query" => "SELECT COUNT(*) AS value FROM supplier",
    "link" => "./supplier/index.php",
    "format" => "number",
    "permission" => "view_suppliers"
  ],

  // Total Categories
  [
    "title" => "Total Categories",
    "icon" => "bi bi-tags fs-2",
    "bg" => "bg-primary",
    "query" => "SELECT COUNT(*) AS value FROM category",
    "link" => "./category/index.php",
    "format" => "number",
    "permission" => "view_categories"
  ],

  // My Sales Receipts
  [
    "title" => "My Sales Receipts",
    "icon" => "bi bi-person-lines-fill fs-2",
    "bg" => "bg-success",
    "query" => "SELECT COUNT(*) AS value FROM receipt WHERE created_by  = '{$_SESSION['user_id']}' AND type = 'sale'",
    "link" => "./sales/my-receipts.php",
    "format" => "number",
    "permission" => "view_my_sales_receipts"
  ],

  // My Purchase Receipts
  [
    "title" => "My Purchase Receipts",
    "icon" => "bi bi-person-lines-fill fs-2",
    "bg" => "bg-warning",
    "query" => "SELECT COUNT(*) AS value FROM receipt WHERE created_by  = '{$_SESSION['user_id']}' AND type = 'purchase'",
    "link" => "./purchase/my-receipts.php",
    "format" => "number",
    "permission" => "view_my_purchase_receipts"
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

$roleName = "Unknown Role";

if (isset($_SESSION['role_id'])) {
  $rid = intval($_SESSION['role_id']);
  $sql = "SELECT role_name FROM role WHERE id = $rid LIMIT 1";
  $res = $conn->query($sql);

  if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $roleName = $row['role_name'];
  }
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
        <?php echo htmlspecialchars($roleName); ?> Dashboard

        <span class="d-block mx-auto mt-1"
          style="width: 60px; height: 3px; background-color: #0d6efd; border-radius: 2px;">
        </span>
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

      <!-- Top Navbar -->
      <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow-sm mb-4">
        <div class="container-fluid px-4">
          <a class="navbar-brand fw-bold" href="#">Dashboard Charts</a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#chartNavbar" aria-controls="chartNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="chartNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
              <?php if (hasPermission("view_all_purchases")): ?>
                <li class="nav-item">
                  <a class="nav-link" href="#allPurchase">All Purchase</a>
                </li>
              <?php endif; ?>

              <?php if (hasPermission("view_my_purchases")): ?>
                <li class="nav-item">
                  <a class="nav-link" href="#myPurchase">My Purchase</a>
                </li>
              <?php endif; ?>

              <?php if (hasPermission("view_all_sales")): ?>
                <li class="nav-item">
                  <a class="nav-link" href="#allSale">All Sale</a>
                </li>
              <?php endif; ?>

              <?php if (hasPermission("view_my_sales")): ?>
                <li class="nav-item">
                  <a class="nav-link" href="#mySale">My Sale</a>
                </li>
              <?php endif; ?>

            </ul>
          </div>
        </div>
      </nav>

      <!-- Chart Sections -->
      <?php if (hasPermission("view_all_purchases")): ?>
        <div class="container-fluid px-4" id="allPurchase">
          <?php include_once './Inc/Admin/all_purchase_quantity.php'; ?>
        </div>
      <?php endif; ?>

      <?php if (hasPermission("view_my_purchases")): ?>
        <div class="container-fluid px-4" id="myPurchase">
          <?php include_once './Inc/Admin/my_purchase_quantity.php'; ?>
        </div>
      <?php endif; ?>

      <?php if (hasPermission("view_all_sales")): ?>
        <div class="container-fluid px-4" id="allSale">
          <?php include_once './Inc/Admin/all_sale_quantity.php'; ?>
        </div>
      <?php endif; ?>

      <?php if (hasPermission("view_my_sales")): ?>
        <div class="container-fluid px-4" id="mySale">
          <?php include_once './Inc/Admin/my_sale_quantity.php'; ?>
        </div>
      <?php endif; ?>

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