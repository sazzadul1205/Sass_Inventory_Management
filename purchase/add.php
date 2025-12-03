<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';
$conn = connectDB();
$formError = "";

// Fetch all products for selection
$products = $conn->query("SELECT id, name, supplier_id, price FROM product ORDER BY name ASC");

// Handle form submission
if (isset($_POST['submit'])) {
  $quantities = $_POST['quantity'] ?? [];
  $productIds = $_POST['product_id'] ?? [];
  $purchasePrice = $_POST['purchase_price'] ?? [];
  $purchaseDates = $_POST['purchase_date'] ?? []; // <-- treat as array

  if (empty($productIds)) {
    $formError = "Please select at least one product.";
  } else {
    $errors = [];
    $stmt = $conn->prepare("INSERT INTO purchase (product_id, supplier_id, quantity, purchase_price, purchase_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $updateStock = $conn->prepare("UPDATE product SET quantity_in_stock = quantity_in_stock + ? WHERE id = ?");

    $createdAt = $updatedAt = date('Y-m-d H:i:s');

    foreach ($productIds as $i => $prodId) {
      $prodId = intval($prodId);
      $qty = intval($quantities[$i]);
      $price = floatval($purchasePrice[$i]);
      $purchaseDate = !empty($purchaseDates[$i]) ? $purchaseDates[$i] : date('Y-m-d');

      if ($qty <= 0) continue;

      // Get supplier from product
      $productRow = $conn->query("SELECT supplier_id FROM product WHERE id = $prodId")->fetch_assoc();
      $supplierId = $productRow['supplier_id'];

      $stmt->bind_param("iiidsss", $prodId, $supplierId, $qty, $price, $purchaseDate, $createdAt, $updatedAt);
      if (!$stmt->execute()) {
        $errors[] = "Failed to add product ID $prodId: " . $stmt->error;
      } else {
        // Update stock
        $updateStock->bind_param("ii", $qty, $prodId);
        $updateStock->execute();
      }
    }

    $stmt->close();
    $updateStock->close();

    if (empty($errors)) {
      $_SESSION['success_message'] = "Purchase(s) added successfully!";
      header("Location: index.php");
      exit;
    } else {
      $formError = implode("<br>", $errors);
    }
  }
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Add Purchase | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css">

  <style>
    .card {
      border-radius: 12px !important;
    }

    .form-section-title {
      font-weight: 600;
      font-size: 1.1rem;
      margin-bottom: 12px;
      border-left: 4px solid #0d6efd;
      padding-left: 10px;
    }
  </style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <?php include_once '../Inc/Navbar.php'; ?>
    <?php include_once '../Inc/Sidebar.php'; ?>

    <main class="app-main">
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between">
          <h3>Add New Purchase</h3>
        </div>
      </div>

      <?php if ($formError): ?>
        <div class="alert alert-danger mt-3"><?= $formError ?></div>
      <?php endif; ?>

      <div class="app-content-body mt-3">
        <div class="container-fluid">
          <div class="card shadow-sm p-4">

            <!-- Info message -->
            <div class="alert alert-info mt-3">
              <i class="bi bi-info-circle"></i> Once a purchase is created, it cannot be edited.
            </div>

            <form method="post" id="purchaseForm">
              <div class="form-section-title">Products</div>
              <div id="productsContainer">
                <div class="row mb-3 product-row">
                  <div class="col-md-4">
                    <label class="form-label">Product</label>
                    <select name="product_id[]" class="form-select product-select" required>
                      <option value="">-- Select Product --</option>
                      <?php while ($prod = $products->fetch_assoc()): ?>
                        <option value="<?= $prod['id'] ?>" data-price="<?= $prod['price'] ?>"><?= htmlspecialchars($prod['name']) ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity[]" class="form-control" min="1" value="1" required>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Purchase Price</label>
                    <input type="number" name="purchase_price[]" class="form-control purchase-price" step="0.01" min="0" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Purchase Date</label>
                    <input type="date" name="purchase_date[]" class="form-control" value="<?= date('Y-m-d') ?>" required>
                  </div>
                  <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-success btn-add-product"><i class="bi bi-plus-circle"></i></button>
                  </div>
                </div>
              </div>

              <div class="mt-4 d-flex gap-2">
                <button type="submit" name="submit" class="btn btn-primary px-4 py-2">
                  <i class="bi bi-check2-circle"></i> Save Purchase
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

    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    $(document).ready(function() {
      // Auto-fill purchase price from product selection
      $(document).on('change', '.product-select', function() {
        let price = $(this).find(':selected').data('price') || 0;
        $(this).closest('.product-row').find('.purchase-price').val(price);
      });

      // Add new product row
      $(document).on('click', '.btn-add-product', function() {
        let row = $(this).closest('.product-row').clone();
        row.find('input').val('');
        row.find('select').val('');
        $('#productsContainer').append(row);
        row.find('.btn-add-product').removeClass('btn-success btn-add-product').addClass('btn-danger btn-remove-product').html('<i class="bi bi-dash-circle"></i>');
      });

      // Remove product row
      $(document).on('click', '.btn-remove-product', function() {
        $(this).closest('.product-row').remove();
      });
    });
  </script>
</body>

</html>