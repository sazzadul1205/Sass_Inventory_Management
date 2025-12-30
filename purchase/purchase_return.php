<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('product_return', '../index.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Purchase | Sass Inventory Management System</title>
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

</head>

<?php
$conn = connectDB();

// Get lot_number from query string safely
$lot_number = isset($_GET['lot']) ? $_GET['lot'] : '';
if (!$lot_number) {
  die("Lot number is required.");
}


// Fetch all purchase items for this lot
$sql = "
SELECT 
    p.id AS purchase_id,
    p.product_id,
    pr.name AS product_name,
    pr.sku,
    pr.status AS product_status,
    pr.category_id,
    pr.subcategory_id,
    pr.supplier_id AS product_supplier_id,
    pr.cost_price,
    pr.selling_price,
    pr.vat,
    pr.price,
    pr.quantity_in_stock,
    pr.low_stock_limit,
    pr.description,
    pr.image,
    p.supplier_id AS purchase_supplier_id,
    p.lot,
    p.quantity AS purchased_quantity,
    p.product_left,
    p.purchase_price AS unit_price,
    p.purchase_date,
    p.receipt_id,
    r.receipt_number,
    r.type AS receipt_type,
    r.total_amount AS receipt_total,
    r.discount_value AS receipt_discount,
    r.created_by AS purchased_by,
    r.created_at AS receipt_created_at,
    r.updated_at AS receipt_updated_at
FROM purchase p
LEFT JOIN product pr ON p.product_id = pr.id
LEFT JOIN receipt r ON p.receipt_id = r.id
WHERE p.lot = ?
ORDER BY p.id DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $lot_number);
$stmt->execute();
$result = $stmt->get_result();
$purchaseItems = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!$purchaseItems) die("Purchase not found.");
?>



<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">

    <!-- Navbar -->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!-- Sidebar -->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!-- App Main -->
    <main class="app-main">
      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <h3 class="mb-0" style="font-weight: 800;">Add New Purchase</h3>
        </div>
      </div>

      <!-- Form Error -->
      <?php if (!empty($formError)): ?>
        <div id="errorBox" class="alert alert-danger text-center">
          <?= htmlspecialchars($formError) ?>
        </div>
      <?php endif; ?>

      <!-- Container -->
      <div class="container">

        <h2 class="mb-4">Return Products for Lot: <?= htmlspecialchars($lot_number) ?></h2>

        <!-- Purchase Items Table -->
        <table class="table table-bordered table-striped mb-4">
          <thead class="table-primary">
            <tr>
              <th>Product</th>
              <th>SKU</th>
              <th>Purchased Qty</th>
              <th>Qty Left</th>
              <th>Unit Price</th>
              <th>Total Price</th>
              <th>Purchase Date</th>
              <th>Receipt</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($purchaseItems as $item): ?>
              <tr>
                <td>
                  <?= htmlspecialchars($item['product_name']) ?><br>
                  <?php if ($item['image']): ?>
                    <img src="<?= htmlspecialchars($item['image']) ?>" class="product-image img-thumbnail mt-1">
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($item['sku']) ?></td>
                <td><?= htmlspecialchars($item['purchased_quantity']) ?></td>
                <td><?= htmlspecialchars($item['product_left']) ?></td>
                <td>$<?= number_format($item['unit_price'], 2) ?></td>
                <td>$<?= number_format($item['unit_price'] * $item['purchased_quantity'], 2) ?></td>
                <td><?= date('d M Y', strtotime($item['purchase_date'])) ?></td>
                <td><?= htmlspecialchars($item['receipt_number']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Return Form -->
        <div class="card mb-4">
          <div class="card-header bg-warning text-dark">
            Return Details
          </div>
          <div class="card-body">
            <form action="process_return.php" method="POST">
              <input type="hidden" name="lot" value="<?= htmlspecialchars($lot_number) ?>">

              <div class="mb-3">
                <label for="return_date" class="form-label">Return Date</label>
                <input type="date" id="return_date" name="return_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
              </div>

              <div class="mb-3">
                <label for="return_reason" class="form-label">Reason for Return</label>
                <textarea id="return_reason" name="return_reason" class="form-control" rows="3" required></textarea>
              </div>

              <div class="mb-3">
                <label for="reimbursement_amount" class="form-label">Reimbursement Amount</label>
                <input type="number" id="reimbursement_amount" name="reimbursement_amount" class="form-control" step="0.01" value="<?= number_format(array_sum(array_column($purchaseItems, 'unit_price')), 2) ?>" required>
              </div>

              <button type="submit" class="btn btn-success"><i class="bi bi-arrow-return-left"></i> Submit Return</button>
              <a href="purchase_list.php" class="btn btn-secondary">Cancel</a>
            </form>
          </div>
        </div>

      </div>

    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>

  </div>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


</body>

</html>