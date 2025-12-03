<?php
include_once __DIR__ . '/../config/db_config.php';
session_start();
$formError = "";
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Add Product | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">

  <!-- Bootstrap & Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css">

  <style>
    .form-section-title {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 12px;
      color: #2c3e50;
      border-left: 4px solid #0d6efd;
      padding-left: 10px;
    }

    .card {
      border-radius: 12px !important;
    }

    .form-label {
      font-weight: 600;
    }
  </style>
</head>

<?php
$conn = connectDB();

// Fetch suppliers & categories
$categories = $conn->query("SELECT id, name FROM category ORDER BY name ASC");
$suppliers  = $conn->query("SELECT id, name FROM supplier ORDER BY name ASC");

// Handle submit
if (isset($_POST['submit'])) {

  $name         = trim($_POST['name']);
  $category_id  = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
  $supplier_id  = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
  $price        = !empty($_POST['price']) ? floatval($_POST['price']) : null;

  // Stock fixed at 0 — not editable
  $quantity     = 0;

  $created_at   = date('Y-m-d H:i:s');
  $updated_at   = date('Y-m-d H:i:s');

  if (empty($name)) {
    $formError = "Product name is required.";
  } else {

    $query = "INSERT INTO product 
      (name, category_id, supplier_id, price, quantity_in_stock, created_at, updated_at)
      VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
      "siidiss",
      $name,
      $category_id,
      $supplier_id,
      $price,
      $quantity,
      $created_at,
      $updated_at
    );

    if ($stmt->execute()) {
      $_SESSION['success_message'] = "Product added successfully!";
      header("Location: index.php");
      exit;
    } else {
      $formError = "Error: " . $stmt->error;
    }

    $stmt->close();
  }
}
?>

<!-- Body -->
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">

    <!-- Navbar -->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!-- Sidebar -->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!-- App Main -->
    <main class="app-main">

      <!-- Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between">
          <h3 class="mb-0">Add New Product</h3>
        </div>
      </div>

      <!-- Form Error -->
      <?php if ($formError): ?>
        <div id="errorBox" class="alert alert-danger text-center mt-3"><?= $formError ?></div>
      <?php endif; ?>

      <!-- App Content Body -->
      <div class="app-content-body mt-3">
        <div class="container-fluid">
          <div class="card shadow-sm p-4">

            <!-- Form -->
            <form method="post">
              <!-- Product Name & Price - Title -->
              <div class="form-section-title">Basic Information</div>

              <!-- Product Name & Price - Content -->
              <div class="row mb-4">
                <!-- Product Name -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Product Name *</label>
                  <input type="text" name="name" class="form-control" placeholder="e.g., Samsung Monitor" required>
                </div>

                <!-- Price -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Price</label>
                  <input type="number" step="0.01" name="price" class="form-control" placeholder="e.g., 15000">
                </div>
              </div>

              <!-- Category & Supplier - Title -->
              <div class="form-section-title">Associations</div>

              <!-- Category & Supplier - Content -->
              <div class="row mb-4">
                <!-- Category -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Category</label>
                  <select name="category_id" class="form-select">
                    <option value="">-- Select Category --</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                      <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <!-- Supplier -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Supplier</label>
                  <select name="supplier_id" class="form-select">
                    <option value="">-- Select Supplier --</option>
                    <?php while ($sup = $suppliers->fetch_assoc()): ?>
                      <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>

              <!-- Stock Section -->
              <div class="form-section-title">Stock</div>

              <!-- Stock (Disabled)  -->
              <div class="row mb-4">
                <div class="col-md-6">
                  <label class="form-label">Quantity in Stock</label>
                  <input type="text" class="form-control" value="0" disabled>
                  <small class="text-muted">Stock updates automatically through Purchase entries.</small>
                </div>
              </div>

              <!-- Save & Cancel Button -->
              <div class="d-flex gap-2">
                <!-- Save Product -->
                <button type="submit" name="submit" class="btn btn-primary px-4 py-2">
                  <i class="bi bi-check2-circle"></i> Save Product
                </button>

                <!-- Cancel -->
                <a href="index.php" class="btn btn-secondary px-4 py-2">
                  <i class="bi bi-x-circle"></i> Cancel
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>

  </div>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

  <!-- AdminLTE JS -->
  <script src="./js/adminlte.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- Table Initialization -->
  <script>
    $(document).ready(function() {
      $('#categoriesTable').DataTable({
        "paging": true,
        "pageLength": 10,
        "lengthChange": true,
        "ordering": true,
        "order": [],
        "info": true,
        "autoWidth": false,
        "columnDefs": [{
            "orderable": false,
            "targets": 5
          } // Disable sorting for Actions column
        ]
      });

      // Auto hide success/fail messages
      setTimeout(() => {
        const msg = document.getElementById('successMsg');
        if (msg) {
          msg.style.transition = "opacity 0.5s";
          msg.style.opacity = "0";
          setTimeout(() => msg.remove(), 500);
        }
        const failMsg = document.getElementById('failMsg');
        if (failMsg) {
          failMsg.style.transition = "opacity 0.5s";
          failMsg.style.opacity = "0";
          setTimeout(() => failMsg.remove(), 500);
        }
      }, 3000);
    });
  </script>
</body>

</body>

</html>