<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">

  <!-- Fonts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="print" onload="this.media='all'">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

  <!-- AdminLTE -->
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css">

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>

<?php
$conn = connectDB();

// Fetch products with category and supplier names
$sql = "
  SELECT 
    p.id,
    p.name,
    p.category_id,
    p.supplier_id,
    p.price,
    p.quantity_in_stock,
    p.created_at,
    p.updated_at,
    c.name AS category_name,
    s.name AS supplier_name
  FROM product p
  LEFT JOIN category c ON p.category_id = c.id
  LEFT JOIN supplier s ON p.supplier_id = s.id
  ORDER BY p.id ASC
";
$result = $conn->query($sql);
?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">

    <!-- Header -->
    <?php include_once '../Inc/Navbar.php'; ?>
    <!-- Sidebar -->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <main class="app-main">

      <!-- Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <h3 class="mb-0">All Products</h3>
          <a href="add.php" class="btn btn-sm btn-primary px-3 py-2">
            <i class="bi bi-plus me-1"></i> Add New Product
          </a>
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

      <div class="app-content-body mt-3">
        <div class="container-fluid">

          <?php if ($result->num_rows == 0): ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              <h5>No products found</h5>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table id="productsTable" class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-primary">
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= $row['id'] ?></td>
                      <td><?= htmlspecialchars($row['name']) ?></td>
                      <td><?= $row['category_name'] ?: "—" ?></td>
                      <td><?= $row['supplier_name'] ?: "—" ?></td>
                      <td><?= number_format($row['price'], 2) ?></td>
                      <td><?= $row['quantity_in_stock'] ?></td>
                      <td><?= !empty($row['created_at']) ? date('d M Y h:i A', strtotime($row['created_at'])) : '' ?></td>
                      <td><?= !empty($row['updated_at']) ? date('d M Y h:i A', strtotime($row['updated_at'])) : '' ?></td>
                      <td>
                        <div class="d-flex gap-1">
                          <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm flex-fill">
                            <i class="bi bi-pencil-square"></i> Edit
                          </a>
                          <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm flex-fill" onclick="return confirm('Delete this product?');">
                            <i class="bi bi-trash"></i> Delete
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

        </div>
      </div>

    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
  <script src="./js/adminlte.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#productsTable').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false,
        columnDefs: [{
          orderable: false,
          targets: 8
        }] // Disable sorting on Actions
      });

      // Auto hide messages
      setTimeout(() => {
        ['successMsg', 'failMsg'].forEach(id => {
          const el = document.getElementById(id);
          if (el) {
            el.style.transition = "opacity 0.5s";
            el.style.opacity = "0";
            setTimeout(() => el.remove(), 500);
          }
        });
      }, 3000);
    });
  </script>

</body>

</html>