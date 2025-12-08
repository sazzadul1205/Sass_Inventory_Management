<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('edit_product', '../index.php');

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
  <title>Edit Product | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" />

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

  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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


    /* Select2 Custom Styling */
    .select2-container--default .select2-selection--single {
      height: calc(1.5em + 0.75rem + 2px);
      /* match bootstrap input height */
      padding: 0.375rem 0.75rem;
      line-height: 1.5;
      border-radius: 8px;
      border: 1px solid #ced4da;
      background-color: #fff;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 1.5;
      color: #000;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
      border-color: #000 transparent transparent transparent;
    }

    .select2-container--default .select2-dropdown {
      background-color: #fff;
      color: #000;
    }

    .select2-container--default .select2-results__option {
      color: #000;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
      background-color: #fff;
      color: #000;
      line-height: 1.5;
    }
  </style>
</head>

<?php
$formError = "";

// Connect to the database
$conn = connectDB();

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
  $_SESSION['error_message'] = "Invalid Product ID.";
  header("Location: index.php");
  exit;
}

// Fetch suppliers & categories
$categories = $conn->query("SELECT id, name FROM category ORDER BY name ASC");
$suppliers  = $conn->query("SELECT id, name FROM supplier ORDER BY name ASC");

// Fetch product info
$productId = intval($_GET['id']);
$product = null;

if ($productId) {
  $stmt = $conn->prepare("SELECT * FROM product WHERE id = ?");
  $stmt->bind_param("i", $productId);
  $stmt->execute();
  $product = $stmt->get_result()->fetch_assoc();
  $stmt->close();
}

// Handle form submission
if (isset($_POST['submit'])) {
  $name        = trim($_POST['name']);
  $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
  $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
  $price       = !empty($_POST['price']) ? floatval($_POST['price']) : null;
  $quantity    = isset($_POST['quantity_in_stock']) ? intval($_POST['quantity_in_stock']) : 0;
  $updated_at  = date('Y-m-d H:i:s');

  if (empty($name)) {
    $formError = "Product name is required.";
  } else {
    $query = "UPDATE product SET name=?, category_id=?, supplier_id=?, price=?, quantity_in_stock=?, updated_at=? WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("siidisi", $name, $category_id, $supplier_id, $price, $quantity, $updated_at, $productId);

    if ($stmt->execute()) {
      $_SESSION['success_message'] = "Product updated successfully!";
      header("Location: index.php");
      exit;
    } else {
      $formError = "Error: " . $stmt->error;
    }

    $conn->close();
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
          <h3 class="mb-0" style="font-weight: 800;">Edit Product</h3>
        </div>
      </div>

      <!-- Form Error -->
      <?php if (!empty($formError)): ?>
        <div id="errorBox" class="alert alert-danger text-center">
          <?= htmlspecialchars($formError) ?>
        </div>
      <?php endif; ?>

      <!-- App Content Body -->
      <div class="app-content-body mt-3">
        <div class="container-fluid">
          <div class="card card-custom shadow-sm">
            <div class="card-body">

              <!-- Header -->
              <h4 class="mb-4 fw-bold text-secondary border-bottom pb-2">
                Update Product Information
              </h4>

              <!-- Form -->
              <form method="post">

                <!-- Product Name & Price -->
                <div class="row mb-4">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control"
                      placeholder="e.g., Samsung Monitor" required
                      value="<?= $product ? htmlspecialchars($product['name']) : '' ?>">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" name="price" class="form-control"
                      placeholder="e.g., 15000"
                      value="<?= $product ? $product['price'] : '' ?>">
                  </div>
                </div>

                <!-- Associations -->
                <div class="row">
                  <!-- Category -->
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="categorySelect" class="form-select">
                      <option value="">-- All Categories --</option>
                      <?php
                      // Reset pointer to first row
                      $categories->data_seek(0);
                      while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($product && $product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($cat['name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <!-- Supplier -->
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" id="supplierSelect" class="form-select">
                      <option value="">-- All Suppliers --</option>
                      <?php
                      $suppliers->data_seek(0);
                      while ($sup = $suppliers->fetch_assoc()): ?>
                        <option value="<?= $sup['id'] ?>" <?= ($product && $product['supplier_id'] == $sup['id']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($sup['name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                </div>

                <!-- Stock Section -->
                <div class="form-section-title">Stock</div>
                <div class="row mb-4">
                  <div class="col-md-6">
                    <label class="form-label">Quantity in Stock</label>
                    <input type="text" class="form-control"
                      value="<?= $product ? intval($product['quantity_in_stock']) : 0 ?>" disabled>
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

  <!-- JQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlaysscrollbars.browser.es6.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

  <!-- Select2 Dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- Select2 Initialization -->
  <script>
    $(document).ready(function() {
      // Initialize Select2 for Category and Supplier
      $('#categorySelect, #supplierSelect').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%',
      });

      // Auto-hide error message
      setTimeout(() => {
        const box = document.getElementById("errorBox");
        if (box) {
          box.style.opacity = "0";
          setTimeout(() => box.remove(), 500);
        }
      }, 3000);
    });
  </script>
</body>

</html>