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

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <!-- Header -->
    <?php include_once './Inc/Navbar.php'; ?>
    <!-- Sidebar -->
    <?php include_once './Inc/Sidebar.php'; ?>

    <main class="app-main">
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
          "title" => "Total Purchased Quantity",
          "icon" => "bi bi-cart-plus fs-2",
          "bg" => "bg-secondary",
          "query" => "SELECT SUM(quantity) AS value FROM purchase",
          "link" => "./purchase/index.php",
          "format" => "number",
          "permission" => "view_all_purchases"
        ],
        [
          "title" => "Total Sold Quantity",
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
          "title" => "My Pending Requests",
          "icon" => "bi bi-clock fs-2",
          "bg" => "bg-secondary",
          "query" => "SELECT COUNT(*) AS value FROM requests WHERE requested_by = '{$_SESSION['user_id']}' AND status = 'pending'",
          "link" => "./requests/my_pending.php",
          "format" => "number",
          "permission" => "view_my_requests"
        ],
        [
          "title" => "My Tasks",
          "icon" => "bi bi-list-task fs-2",
          "bg" => "bg-info",
          "query" => "SELECT COUNT(*) AS value FROM tasks WHERE assigned_to = '{$_SESSION['user_id']}' AND status != 'completed'",
          "link" => "./tasks/my_tasks.php",
          "format" => "number",
          "permission" => "view_my_tasks"
        ],
      ];

      ?>

      <div class="content-header pt-5">
        <div class="container-fluid px-4">
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
                  <div class="card-body d-flex align-items-center">
                    <div class="card-body-icon me-3"><i class="<?= $card['icon'] ?> fs-2"></i></div>
                    <div>
                      <div class="h5"><?= $card['title'] ?></div>
                      <div class="fw-bold fs-5"><?= $value ?></div>
                    </div>
                  </div>
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