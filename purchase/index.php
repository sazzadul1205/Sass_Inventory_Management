<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>Purchases | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>

<?php
$conn = connectDB();

// Fetch purchases with product & supplier names
$sql = "
  SELECT 
    p.id,
    p.product_id,
    pr.name AS product_name,
    p.supplier_id,
    s.name AS supplier_name,
    p.quantity,
    p.purchase_price,
    p.purchase_date,
    p.created_at,
    p.updated_at
  FROM purchase p
  LEFT JOIN products pr ON p.product_id = pr.id
  LEFT JOIN supplier s ON p.supplier_id = s.id
  ORDER BY p.id DESC
";

$result = $conn->query($sql);
?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

  <div class="app-wrapper">

    <?php include_once '../Inc/Navbar.php'; ?>
    <?php include_once '../Inc/Sidebar.php'; ?>

    <main class="app-main">

      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <h3 class="mb-0">All Purchases</h3>

          <a href="add.php" class="btn btn-sm btn-primary px-3 py-2">
            <i class="bi bi-plus me-1"></i> Add New Purchase
          </a>
        </div>
      </div>

      <!-- Messages -->
      <?php
      if (!empty($_SESSION['success_message'])) {
        echo "<div id='successMsg' class='alert alert-success'>{$_SESSION['success_message']}</div>";
        unset($_SESSION['success_message']);
      }

      if (!empty($_SESSION['fail_message'])) {
        echo "<div id='failMsg' class='alert alert-danger'>{$_SESSION['fail_message']}</div>";
        unset($_SESSION['fail_message']);
      }
      ?>

      <div class="app-content-body mt-3">
        <div class="table-responsive container-fluid">

          <table id="purchaseTable" class="table table-bordered table-hover table-striped align-middle">
            <thead class="table-primary">
              <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Supplier</th>
                <th>Quantity</th>
                <th>Purchase Price</th>
                <th>Purchase Date</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>
              <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>

                    <td><?= htmlspecialchars($row['product_name'] ?? 'Unknown Product') ?></td>

                    <td><?= htmlspecialchars($row['supplier_name'] ?? 'Unknown Supplier') ?></td>

                    <td><?= htmlspecialchars($row['quantity']) ?></td>

                    <td><?= htmlspecialchars($row['purchase_price']) ?></td>

                    <td>
                      <?= !empty($row['purchase_date']) ? date('d M Y', strtotime($row['purchase_date'])) : '' ?>
                    </td>

                    <td>
                      <?= !empty($row['created_at']) ? date('d M Y h:i A', strtotime($row['created_at'])) : '' ?>
                    </td>

                    <td>
                      <?= !empty($row['updated_at']) ? date('d M Y h:i A', strtotime($row['updated_at'])) : '' ?>
                    </td>

                    <td>
                      <div class="d-flex gap-1">
                        <a href="edit.php?id=<?= urlencode($row['id']) ?>" class="btn btn-warning btn-sm flex-fill">
                          <i class="bi bi-pencil-square"></i>
                        </a>

                        <a href="delete.php?id=<?= urlencode($row['id']) ?>"
                          onclick="return confirm('Delete this purchase?')"
                          class="btn btn-danger btn-sm flex-fill">
                          <i class="bi bi-trash"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="9" class="text-center">No purchases found</td>
                </tr>
              <?php endif; ?>
            </tbody>

          </table>

        </div>
      </div>

    </main>

    <?php include_once '../Inc/Footer.php'; ?>

  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#purchaseTable').DataTable({
        pageLength: 10,
        order: [],
        columnDefs: [{
          orderable: false,
          targets: 8
        }]
      });

      setTimeout(() => {
        document.querySelectorAll('#successMsg,#failMsg').forEach(msg => {
          msg.style.transition = "opacity .5s";
          msg.style.opacity = "0";
          setTimeout(() => msg.remove(), 500);
        });
      }, 3000);
    });
  </script>

</body>

</html>