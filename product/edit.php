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

                <!-- PRODUCT BASIC INFO -->
                <div class="row mb-4">

                  <!-- Product Name -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Product Name *</label>
                    <input
                      type="text"
                      name="name"
                      class="form-control"
                      required
                      value="<?= htmlspecialchars($product['name']) ?>">
                  </div>

                  <!-- SKU -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Product Code (SKU) *</label>
                    <input
                      type="text"
                      name="sku"
                      class="form-control"
                      required
                      value="<?= htmlspecialchars($product['sku']) ?>">
                  </div>

                  <!-- Status -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Availability</label>
                    <select name="status" class="form-select">
                      <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Available</option>
                      <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Not Available</option>
                    </select>
                  </div>

                </div>


                <!-- CATEGORY / SUBCATEGORY / SUPPLIER -->
                <div class="row mb-4">

                  <!-- Category -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="categorySelect" class="form-select">
                      <option value="">Choose a category</option>
                      <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option
                          value="<?= $cat['id'] ?>"
                          <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($cat['name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <!-- Subcategory (loaded via AJAX on edit load) -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Subcategory</label>
                    <select
                      name="subcategory_id"
                      id="subCategorySelect"
                      class="form-select"
                      disabled>
                      <option value="">Select a category first</option>
                    </select>
                  </div>

                  <!-- Supplier (filtered by category) -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" id="supplierSelect" class="form-select" disabled>
                      <option value="">Choose a supplier</option>
                    </select>
                  </div>

                </div>


                <!-- PRICING (REFERENCE-MATCHED) -->
                <div class="row mb-4">

                  <!-- Cost Price -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Cost Price *</label>

                    <!-- Visible formatted input -->
                    <input
                      type="text"
                      class="form-control currency-input"
                      data-target="minimum_price"
                      value="<?= number_format($product['cost_price'], 2) ?>"
                      required>

                    <!-- Hidden numeric value -->
                    <input
                      type="hidden"
                      name="minimum_price"
                      id="minimum_price"
                      value="<?= number_format($product['cost_price'], 2, '.', '') ?>">
                  </div>

                  <!-- Selling Price -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Selling Price (MRP) *</label>

                    <input
                      type="text"
                      class="form-control currency-input"
                      data-target="mrp"
                      value="<?= number_format($product['selling_price'], 2) ?>"
                      required>

                    <input
                      type="hidden"
                      name="mrp"
                      id="mrp"
                      value="<?= number_format($product['selling_price'], 2, '.', '') ?>">
                  </div>

                  <!-- VAT -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">VAT (%)</label>
                    <input
                      type="number"
                      name="vat"
                      class="form-control"
                      min="0"
                      max="100"
                      step="0.01"
                      value="<?= $product['vat'] ?>"
                      required>
                  </div>

                </div>


                <!-- STOCK (READ-ONLY) -->
                <div class="row mb-4">

                  <!-- Current Stock -->
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Current Stock</label>
                    <input
                      type="text"
                      class="form-control"
                      value="<?= (int)$product['quantity_in_stock'] ?>"
                      disabled>
                  </div>

                  <!-- Low Stock Alert -->
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Low Stock Alert</label>
                    <input
                      type="number"
                      name="stock_limit"
                      class="form-control"
                      min="0"
                      value="<?= $product['low_stock_limit'] ?>">
                  </div>

                </div>


                <!-- DESCRIPTION & IMAGE -->
                <div class="row mb-4">

                  <!-- Description -->
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Description</label>
                    <textarea
                      name="description"
                      class="form-control"
                      rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
                  </div>

                  <!-- Image Upload -->
                  <div class="col-md-3 mb-3">
                    <label class="form-label">Product Image</label>
                    <input
                      type="file"
                      name="image"
                      id="imageInput"
                      class="form-control"
                      accept="image/*">
                  </div>

                  <!-- Image Preview -->
                  <div class="col-md-3 mb-3 d-flex align-items-center justify-content-center">
                    <img
                      id="imagePreview"
                      src="<?= !empty($product['image'])
                              ? $Project_URL . 'assets/products/' . $product['image']
                              : 'https://via.placeholder.com/150x150?text=Preview' ?>"
                      class="img-fluid rounded shadow-sm"
                      style="max-height:150px;">
                  </div>

                </div>


                <!-- ACTION BUTTONS -->
                <div class="mt-4 d-flex gap-2">
                  <button type="submit" name="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle"></i> Update Product
                  </button>
                  <a href="index.php" class="btn btn-secondary">
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

      /* 
         SELECT2 INITIALIZATION
         - Same setup as Add Product
       */
      $('#categorySelect, #subCategorySelect, #supplierSelect').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%'
      });

      /* 
         PRELOADED VALUES (FROM PHP)
         - Used only on Edit page
       */
      const categoryId = "<?= $product['category_id'] ?>";
      const subCategoryId = "<?= $product['subcategory_id'] ?>";
      const supplierId = "<?= $product['supplier_id'] ?>";

      /* 
         AUTO-HIDE ERROR MESSAGE
       */
      setTimeout(() => {
        const box = document.getElementById("errorBox");
        if (box) {
          box.style.opacity = "0";
          setTimeout(() => box.remove(), 500);
        }
      }, 3000);

      /* 
         LOAD SUBCATEGORIES (AJAX)
         - Supports Edit preload
       */
      function loadSubcategories(categoryId, selectedId = null) {
        const $sub = $('#subCategorySelect');

        // Reset dropdown
        $sub.empty();

        // No category selected
        if (!categoryId) {
          $sub
            .prop('disabled', true)
            .append('<option>Select a category first</option>')
            .trigger('change');
          return;
        }

        $.ajax({
          url: 'fetch_subcategories.php',
          type: 'GET',
          data: {
            parent_id: categoryId
          },
          dataType: 'json',

          success: function(data) {

            // No subcategories found
            if (!Array.isArray(data) || data.length === 0) {
              $sub
                .prop('disabled', true)
                .append('<option>No subcategories available</option>')
                .trigger('change');
              return;
            }

            // Populate subcategories
            $sub.append('<option></option>');
            data.forEach(sub => {
              const selected = selectedId == sub.id ? 'selected' : '';
              $sub.append(
                `<option value="${sub.id}" ${selected}>${sub.name}</option>`
              );
            });

            $sub.prop('disabled', false).trigger('change');
          },

          error: function() {
            $sub
              .prop('disabled', true)
              .append('<option>Error loading subcategories</option>')
              .trigger('change');
          }
        });
      }

      /* 
         LOAD SUPPLIERS BY CATEGORY (AJAX)
         - Same logic as Add Product
         - Supports Edit preload
       */
      function loadSuppliers(categoryId, selectedId = null) {
        const $sup = $('#supplierSelect');

        // Reset dropdown
        $sup.empty();

        if (!categoryId) {
          $sup
            .prop('disabled', true)
            .append('<option>Choose a category first</option>')
            .trigger('change');
          return;
        }

        $.ajax({
          url: 'get_suppliers_by_category.php',
          type: 'GET',
          data: {
            category_id: categoryId
          },
          dataType: 'json',

          success: function(data) {

            // No suppliers found
            if (!Array.isArray(data) || data.length === 0) {
              $sup
                .prop('disabled', true)
                .append('<option>No suppliers available</option>')
                .trigger('change');
              return;
            }

            // Populate suppliers
            $sup.append('<option></option>');
            data.forEach(sup => {
              const selected = selectedId == sup.id ? 'selected' : '';
              $sup.append(
                `<option value="${sup.id}" ${selected}>${sup.name}</option>`
              );
            });

            $sup.prop('disabled', false).trigger('change');
          },

          error: function() {
            $sup
              .prop('disabled', true)
              .append('<option>Error loading suppliers</option>')
              .trigger('change');
          }
        });
      }

      /* 
         INITIAL LOAD (EDIT MODE ONLY)
         - Preloads subcategory & supplier
       */
      if (categoryId) {
        loadSubcategories(categoryId, subCategoryId);
        loadSuppliers(categoryId, supplierId);
      }

      /* 
         CATEGORY CHANGE HANDLER
         - Reloads dependent dropdowns
       */
      $('#categorySelect').on('change', function() {
        const selectedCategory = $(this).val();
        loadSubcategories(selectedCategory);
        loadSuppliers(selectedCategory);
      });

      /* 
         IMAGE PREVIEW (EDIT SAFE)
         - Replaces preview only if new image selected
       */
      const imageInput = document.getElementById('imageInput');
      const imagePreview = document.getElementById('imagePreview');

      imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = e => imagePreview.src = e.target.result;
        reader.readAsDataURL(file);
      });

    });
  </script>

  <!-- 
     CURRENCY FORMATTER (MATCHES ADD PAGE)
 -->
  <script>
    $(function() {

      function formatWithCommas(number) {
        return number.toLocaleString('en-US', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
      }

      $('.currency-input')

        // Clear default on focus
        .on('focus', function() {
          if (this.value === '0.00') this.value = '';
        })

        // Allow only numbers & dot
        .on('input', function() {
          this.value = this.value.replace(/[^0-9.]/g, '');
        })

        // Format & sync hidden input
        .on('blur', function() {
          let raw = this.value.replace(/,/g, '');
          if (raw === '' || isNaN(raw)) raw = '0';

          const number = parseFloat(raw);
          this.value = formatWithCommas(number);

          const target = $(this).data('target');
          $('#' + target).val(number.toFixed(2));
        });

    });
  </script>

</body>

</html>