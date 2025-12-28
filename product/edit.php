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

// Fetch categories & suppliers
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
?>

<?php
if (isset($_POST['submit'])) {
  $name            = trim($_POST['name']);
  $sku             = trim($_POST['sku']);
  $status          = $_POST['status'] ?? 'inactive';
  $category_id     = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
  $subcategory_id  = !empty($_POST['subcategory_id']) ? intval($_POST['subcategory_id']) : null;
  $supplier_id     = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
  $cost_price      = !empty($_POST['minimum_price']) ? floatval($_POST['minimum_price']) : 0;
  $selling_price   = !empty($_POST['mrp']) ? floatval($_POST['mrp']) : 0;
  $vat             = !empty($_POST['vat']) ? floatval($_POST['vat']) : 0;
  $low_stock_limit = !empty($_POST['stock_limit']) ? intval($_POST['stock_limit']) : null;
  $description     = $_POST['description'] ?? '';
  $updated_at      = date('Y-m-d H:i:s');

  // Handle Image Upload
  $imageFileName = $product['image'] ?? null;
  if (!empty($_FILES['image']['name'])) {
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName    = $_FILES['image']['name'];
    $fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png'];

    if (in_array($fileExt, $allowedExts)) {
      $imageFileName = uniqid('prod_', true) . '.' . $fileExt;
      $destPath = __DIR__ . '/../assets/products/' . $imageFileName;
      if (!move_uploaded_file($fileTmpPath, $destPath)) {
        $formError = "Failed to upload image.";
      }
    } else {
      $formError = "Invalid image format. Only JPG, JPEG, PNG allowed.";
    }
  }

  // Validate required fields
  if (empty($name)) $formError = "Product name is required.";

  // Update product if no errors
  if (empty($formError)) {
    $query = "UPDATE product SET 
                    name=?, sku=?, status=?, category_id=?, subcategory_id=?, supplier_id=?, 
                    cost_price=?, selling_price=?, vat=?, price=?, low_stock_limit=?, description=?, image=?, updated_at=?
                  WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
      "sssiiiddddiissi",
      $name,
      $sku,
      $status,
      $category_id,
      $subcategory_id,
      $supplier_id,
      $cost_price,
      $selling_price,
      $vat,
      $selling_price,
      $low_stock_limit,
      $description,
      $imageFileName,
      $updated_at,
      $productId
    );

    if ($stmt->execute()) {
      $_SESSION['success_message'] = "Product updated successfully!";
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

              <form method="post" enctype="multipart/form-data">

                <!-- Product Name, SKU & Status -->
                <div class="row mb-4">
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Type the product name" maxlength="255" required
                      value="<?= $product ? htmlspecialchars($product['name']) : '' ?>">
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">Product Code (SKU) *</label>
                    <input type="text" name="sku" class="form-control" placeholder="Unique code for this product" maxlength="50" required
                      value="<?= $product ? htmlspecialchars($product['sku']) : '' ?>">
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">Availability</label>
                    <select name="status" class="form-select">
                      <option value="active" <?= ($product && $product['status'] === 'active') ? 'selected' : '' ?>>Available</option>
                      <option value="inactive" <?= ($product && $product['status'] === 'inactive') ? 'selected' : '' ?>>Not Available</option>
                    </select>
                  </div>
                </div>

                <!-- Category, Subcategory & Supplier -->
                <div class="row mb-4">
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="categorySelect" class="form-select">
                      <option value="">Choose a category</option>
                      <?php
                      $categories->data_seek(0);
                      while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($product && $product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($cat['name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">Subcategory</label>
                    <select name="subcategory_id" id="subCategorySelect" class="form-select" <?= empty($product['subcategory_id']) ? 'disabled' : '' ?>>
                      <option value="">Select a category first</option>
                      <?php if ($product && !empty($product['subcategory_id'])): ?>
                        <option value="<?= $product['subcategory_id'] ?>" selected>Current Subcategory</option>
                      <?php endif; ?>
                    </select>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" id="supplierSelect" class="form-select">
                      <option value="">Choose a supplier</option>
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

                <!-- Pricing -->
                <div class="row mb-4">
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Cost Price (Purchase Price) *</label>
                    <input type="number" name="minimum_price" class="form-control" placeholder="Enter purchase price" min="0.01" step="0.01"
                      value="<?= $product ? $product['cost_price'] : '' ?>" required>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">Selling Price (MRP) *</label>
                    <input type="number" name="mrp" class="form-control" placeholder="Enter selling price" min="0.01" step="0.01"
                      value="<?= $product ? $product['selling_price'] : '' ?>" required>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">VAT (%) *</label>
                    <input type="number" name="vat" class="form-control" placeholder="Enter VAT percentage" min="0" max="100" step="0.01"
                      value="<?= $product ? $product['vat'] : 0 ?>" required>
                  </div>
                </div>

                <!-- Stock -->
                <div class="row mb-4">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Current Stock</label>
                    <input type="text" class="form-control" value="<?= $product ? intval($product['quantity_in_stock']) : 0 ?>" disabled>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Low Stock Alert</label>
                    <input type="number" name="stock_limit" class="form-control" placeholder="Minimum stock to alert" min="0"
                      value="<?= $product ? intval($product['low_stock_limit']) : '' ?>">
                  </div>
                </div>

                <!-- Description & Image -->
                <div class="row mb-4">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Product Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Write a simple description"><?= $product ? htmlspecialchars($product['description']) : '' ?></textarea>
                  </div>

                  <div class="col-md-3 mb-3">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
                  </div>

                  <div class="col-md-3 mb-3 d-flex align-items-center justify-content-center">
                    <img id="imagePreview" src="<?= $product && !empty($product['image']) ? $Project_URL . 'assets/products/' . $product['image'] : 'https://via.placeholder.com/150x150?text=Preview' ?>"
                      alt="Image Preview" class="img-fluid rounded shadow-sm" style="max-height: 150px;">
                  </div>
                </div>

                <!-- Submit -->
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