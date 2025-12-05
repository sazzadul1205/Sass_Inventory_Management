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
  <meta charset="utf-8" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Add Product | Sass Inventory Management System</title>
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

  <!-- Custom CSS -->
  <style>
    .card-custom {
      border-radius: 12px;
      border: 1px solid #e9ecef;
      transition: 0.2s ease;
    }

    .card-custom:hover {
      border-color: #cbd3da;
    }

    .form-label {
      font-weight: 600;
    }

    .form-control,
    .form-select {
      padding: 10px 14px;
      border-radius: 8px;
    }

    .btn-primary {
      border-radius: 8px;
      font-weight: 600;
    }

    .btn-secondary {
      border-radius: 8px;
    }
  </style>
</head>

<?php
$formError = "";

// Connect to DB
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
      $formError = "Failed to add product: " . $stmt->error;
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

      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <!-- Page Title -->
          <h3 class="mb-0 " style="font-weight: 800;">Add New Product</h3>
        </div>
      </div>

      <!-- Form Error -->
      <?php if (!empty($formError)): ?>
        <div id="errorBox" class="alert alert-danger text-center">
          <?= htmlspecialchars($formError) ?>
        </div>
      <?php endif; ?>


      <!-- Body -->
      <div class="app-content-body mt-4">
        <div class="container-fluid">
          <div class="card card-custom shadow-sm">
            <div class="card-body p-4">

              <!-- Header -->
              <h4 class="mb-4">Add Product Information</h4>

              <!-- Form -->
              <form method="post">

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

                <!-- Buttons -->
                <div class="mt-4 d-flex gap-2">
                  <button type="submit" name="submit" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-check2-circle"></i> Save Product
                  </button>
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlaysscrollbars.browser.es6.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

  <!-- JQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- Auto-hide error -->
  <script>
    setTimeout(() => {
      const box = document.getElementById("errorBox");
      if (box) {
        box.style.opacity = "0";
        setTimeout(() => box.remove(), 500);
      }
    }, 3000);
  </script>
</body>

</body>

</html>