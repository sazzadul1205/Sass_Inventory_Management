<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';

// Check login
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

// Validate and get purchase ID
$purchaseId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($purchaseId <= 0) {
  die("Invalid purchase ID.");
}

$conn = connectDB();

// Fetch purchase info with product and supplier details
$sql = "
SELECT 
    p.id AS purchase_id,
    pr.name AS product_name,
    pr.price AS default_price,
    p.quantity,
    p.purchase_price,
    p.purchase_date,
    s.name AS supplier_name
FROM purchase p
LEFT JOIN product pr ON p.product_id = pr.id
LEFT JOIN supplier s ON pr.supplier_id = s.id
WHERE p.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $purchaseId);
$stmt->execute();
$result = $stmt->get_result();
$purchase = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($purchase)) {
  die("Purchase not found.");
}

// Calculate totals
$totalQuantity = 0;
$totalAmount = 0;
foreach ($purchase as $row) {
  $totalQuantity += $row['quantity'];
  $totalAmount += $row['purchase_price'];
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Purchase Receipt | Sass Inventory</title>
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  <style>
    .card-custom {
      border-radius: 12px;
      border: 1px solid #e9ecef;
      padding: 20px;
      margin-top: 20px;
    }

    .card-custom h4 {
      font-weight: 800;
    }

    .table td,
    .table th {
      vertical-align: middle;
    }

    .btn-print {
      border-radius: 8px;
    }
  </style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <?php include_once '../Inc/Navbar.php'; ?>
    <?php include_once '../Inc/Sidebar.php'; ?>

    <main class="app-main">
      <div class="container-fluid">
        <div class="card card-custom shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4>Purchase Receipt</h4>
              <button onclick="window.print()" class="btn btn-primary btn-print">
                <i class="bi bi-printer"></i> Print / PDF
              </button>
            </div>

            <table class="table table-bordered">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Product</th>
                  <th>Supplier</th>
                  <th>Quantity</th>
                  <th>Unit Price</th>
                  <th>Total Price</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($purchase as $i => $row):
                  $unitPrice = $row['purchase_price'] / $row['quantity'];
                ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td>$<?= number_format($unitPrice, 2) ?></td>
                    <td>$<?= number_format($row['purchase_price'], 2) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot class="fw-bold">
                <tr>
                  <td colspan="3" class="text-end">Total</td>
                  <td><?= $totalQuantity ?></td>
                  <td>-</td>
                  <td>$<?= number_format($totalAmount, 2) ?></td>
                </tr>
              </tfoot>
            </table>

            <p class="text-muted mt-4">Purchase Date: <?= date('Y-m-d') ?></p>
          </div>
        </div>
      </div>
    </main>

    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
</body>

</html>