<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$conn = connectDB();

// Fetch purchase data from view_purchase_report
$sql = "SELECT * FROM view_purchase_report";
$result = $conn->query($sql);
$purchases = $result->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Purchase Report | Sass Inventory</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Favicon -->
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">

  <!-- Source Sans 3 Font -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="print" onload="this.media='all'" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />

  <!-- AdminLTE Styles -->
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />

  <!-- DataTables Bootstrap 5 CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <?php include_once '../Inc/Navbar.php'; ?>
    <?php include_once '../Inc/Sidebar.php'; ?>

    <main class="app-main">

    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <!-- jQuery Library -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- Popper.js for Bootstrap tooltips & popovers -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

  <!-- AdminLTE Dashboard JS -->
  <script src="<?= $Project_URL ?>/js/adminlte.js"></script>

  <!-- DataTables Core JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <!-- DataTables Bootstrap 5 Integration -->
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

  <!-- ApexCharts Library -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <!-- Select2 Library for enhanced select dropdowns -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


</body>

</html>