<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('add_product', '../index.php');

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
  <title>Add New Product | Sass Inventory System</title>
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
    /*  General Card & Form Styling */
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

    /* Sub Category height fix */
    #subCategorySelect {
      height: calc(1.5em + 0.75rem + 2px);
      padding: 0.375rem 0.75rem;
      line-height: 1.5;
      border-radius: 8px;
      font-size: 1rem;
    }
  </style>
</head>

<!-- PHP -->
<?php
$formError = "";

// Connect to the database
$conn = connectDB();

// Fetch all categories & suppliers
$categories = $conn->query("
    SELECT id, name
    FROM category
    WHERE parent_id IS NULL
    ORDER BY name ASC
");
$suppliers  = $conn->query("SELECT id, name FROM supplier ORDER BY name ASC");

// Handle form submission
// Handle form submission
if (isset($_POST['submit'])) {
  $name        = trim($_POST['name']);
  $sku         = trim($_POST['sku']);
  $status      = $_POST['status'] ?? 'inactive';
  $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
  $subcategory_id = !empty($_POST['subcategory_id']) ? intval($_POST['subcategory_id']) : null;
  $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
  $cost_price  = !empty($_POST['minimum_price']) ? floatval($_POST['minimum_price']) : 0;
  $selling_price = !empty($_POST['mrp']) ? floatval($_POST['mrp']) : 0;
  $vat         = !empty($_POST['vat']) ? floatval($_POST['vat']) : 0;
  $quantity    = 0;
  $low_stock_limit = !empty($_POST['stock_limit']) ? intval($_POST['stock_limit']) : null;
  $description = $_POST['description'] ?? '';
  $created_at  = date('Y-m-d H:i:s');
  $updated_at  = date('Y-m-d H:i:s');

  // Handle image upload
  $imageFileName = null;
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

  // Validate product name
  if (empty($name)) {
    $formError = "Product name is required.";
  }

  // Insert product if no errors
  if (empty($formError)) {
    $query = "INSERT INTO product 
            (name, sku, status, category_id, subcategory_id, supplier_id, cost_price, selling_price, vat, price, quantity_in_stock, low_stock_limit, description, image, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
      "sssiiiddddiissss",
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
      $quantity,
      $low_stock_limit,
      $description,
      $imageFileName,
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
          <h3 class="mb-0" style="font-weight: 800;">Add New Product</h3>
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
                Add Product Information
              </h4>

              <!-- Product Form -->
              <form method="post" enctype="multipart/form-data">

                <!-- Product Name, SKU & Status -->
                <div class="row mb-4">
                  <!-- Product Name -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Type the product name" maxlength="255" required>
                  </div>

                  <!-- Product SKU -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Product Code (SKU) *</label>
                    <input type="text" name="sku" class="form-control" placeholder="Unique code for this product" maxlength="50" required>
                  </div>

                  <!-- Status -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Availability</label>
                    <select name="status" class="form-select">
                      <option value="active">Available</option>
                      <option value="inactive">Not Available</option>
                    </select>
                  </div>
                </div>

                <!-- Category, Subcategory & Supplier -->
                <div class="row mb-4">
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="categorySelect" class="form-select">
                      <option value="">Choose a category</option>
                      <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">Subcategory</label>
                    <select name="subcategory_id" id="subCategorySelect" class="form-select" disabled>
                      <option value="">Select a category first</option>
                    </select>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" id="supplierSelect" class="form-select">
                      <option value="">Choose a supplier</option>
                      <?php while ($sup = $suppliers->fetch_assoc()): ?>
                        <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                </div>

                <!-- Pricing -->
                <div class="row mb-4">
                  <!-- Cost Price -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Cost Price (Purchase Price) *</label>
                    <input
                      type="number"
                      name="minimum_price"
                      class="form-control"
                      placeholder="Enter how much you buy this product for"
                      min="0.01"
                      step="0.01"
                      required
                      oninput="this.value = Math.max(this.value, 0.01)"
                      title="Must be a positive number">
                    <small class="text-muted">Enter the price you purchase this product for. Must be greater than 0.</small>
                  </div>

                  <!-- Selling Price -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Selling Price (MRP) *</label>
                    <input
                      type="number"
                      name="mrp"
                      class="form-control"
                      placeholder="Enter the selling price"
                      min="0.01"
                      step="0.01"
                      required
                      oninput="this.value = Math.max(this.value, 0.01)"
                      title="Must be a positive number">
                    <small class="text-muted">The price you sell this product for. Must be higher than the Cost Price.</small>
                  </div>

                  <!-- VAT -->
                  <div class="col-md-4 mb-3">
                    <label class="form-label">VAT (%) *</label>
                    <input
                      type="number"
                      name="vat"
                      class="form-control"
                      placeholder="Enter VAT percentage"
                      min="0"
                      max="100"
                      step="0.01"
                      required
                      oninput="this.value = Math.min(Math.max(this.value, 0), 100)"
                      title="Enter a percentage between 0 and 100">
                    <small class="text-muted">Tax percentage (0-100%). Automatically included in selling price if needed.</small>
                  </div>
                </div>

                <!-- Stock -->
                <div class="row mb-4">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Stock Available</label>
                    <input type="text" class="form-control" value="0" disabled>
                    <small class="text-muted">Current stock is updated automatically.</small>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Low Stock Alert</label>
                    <input type="number" name="stock_limit" class="form-control" placeholder="Minimum stock to alert" min="0">
                    <small class="text-muted">We'll notify you if stock goes below this number.</small>
                  </div>
                </div>

                <!-- Description & Image -->
                <div class="row mb-4">
                  <div class="col-md-12 mb-3">
                    <label class="form-label">Product Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Write a simple description of the product"></textarea>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
                    <small class="text-muted">Upload an image (JPG or PNG, max 2MB)</small>
                  </div>

                  <div class="col-md-6 mb-3 d-flex align-items-center justify-content-center">
                    <!-- Image preview -->
                    <img id="imagePreview" src="https://via.placeholder.com/150x150?text=Preview"
                      alt="Image Preview" class="img-fluid rounded shadow-sm" style="max-height: 150px;">
                  </div>
                </div>

                <!-- Form Buttons -->
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

  <!-- JS Dependencies -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Select2 -->
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

  <!-- Category & Sub Category -->
  <script>
    $(document).ready(function() {
      // Initialize Select2 only on Category and Supplier
      $('#categorySelect, #supplierSelect').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%',
      });

      // Auto-hide error messages
      setTimeout(() => {
        const box = $("#errorBox");
        if (box.length) {
          box.css('opacity', 0);
          setTimeout(() => box.remove(), 500);
        }
      }, 3000);

      // Category → Sub Category logic (plain select)
      $('#categorySelect').on('change', function() {
        const categoryId = $(this).val();
        const $subSelect = $('#subCategorySelect');

        // Reset
        $subSelect.empty();
        $subSelect.prop('disabled', true);

        if (!categoryId) {
          $subSelect.append('<option value="">Select Category First</option>');
          return;
        }

        // Fetch subcategories
        $.ajax({
          url: 'fetch_subcategories.php',
          method: 'GET',
          data: {
            parent_id: categoryId
          },
          dataType: 'json',
          success: function(data) {
            $subSelect.empty();

            if (!Array.isArray(data) || data.length === 0) {
              $subSelect.append('<option value="">No Sub Categories Available</option>');
              $subSelect.prop('disabled', true);
              return;
            }

            $subSelect.append('<option value="">-- Select Sub Category --</option>');

            data.forEach(function(sub) {
              $subSelect.append(`<option value="${sub.id}">${sub.name}</option>`);
            });

            $subSelect.prop('disabled', false);
          },
          error: function() {
            $subSelect.empty().append('<option value="">Error loading sub categories</option>');
            $subSelect.prop('disabled', true);
          }
        });
      });
    });
  </script>

  <script>
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');

    imageInput.addEventListener('change', function() {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          imagePreview.src = e.target.result;
        }
        reader.readAsDataURL(file);
      } else {
        // If no file, revert to placeholder
        imagePreview.src = "https://via.placeholder.com/150x150?text=Preview";
      }
    });
  </script>

  <script>
    $('#categorySelect').on('change', function() {
      const categoryId = $(this).val();
      const $supplierSelect = $('#supplierSelect');

      // Clear current options
      $supplierSelect.empty();

      if (!categoryId) {
        $supplierSelect.append(new Option("Choose a supplier", ""));
        $supplierSelect.prop('disabled', true);
        $supplierSelect.trigger('change'); // refresh Select2
        return;
      }

      // Fetch suppliers from server
      $.ajax({
        url: 'get_suppliers_by_category.php',
        method: 'GET',
        data: {
          category_id: categoryId
        },
        dataType: 'json',
        success: function(data) {
          $supplierSelect.prop('disabled', false);

          if (!Array.isArray(data) || data.length === 0) {
            $supplierSelect.append(new Option("No suppliers available", ""));
          } else {
            $supplierSelect.append(new Option("-- Select Supplier --", ""));
            data.forEach(function(supplier) {
              $supplierSelect.append(new Option(supplier.name, supplier.id));
            });
          }

          // **Refresh Select2 so it shows the updated options**
          $supplierSelect.trigger('change');
        },
        error: function() {
          $supplierSelect.empty().append(new Option("Error loading suppliers", ""));
          $supplierSelect.prop('disabled', true);
          $supplierSelect.trigger('change');
        }
      });
    });
  </script>

</body>

</html>