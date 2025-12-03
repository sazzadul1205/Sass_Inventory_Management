<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';
$conn = connectDB();
$formError = "";

// Fetch all products
$products = $conn->query("SELECT id, name, supplier_id, price, quantity_in_stock FROM product ORDER BY name ASC");

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
  header("Location: ../login.php");
  exit;
}

// Handle form submission
if (isset($_POST['submit'])) {

  $quantities = $_POST['quantity'] ?? [];
  $productIds = $_POST['product_id'] ?? [];
  $salePrices = $_POST['sale_price'] ?? [];
  $saleDates = $_POST['sale_date'] ?? [];

  if (empty($productIds)) {
    $formError = "Please select at least one product.";
  } else {
    $errors = [];

    // Insert into sale table
    $stmt = $conn->prepare("INSERT INTO sale 
    (product_id, quantity, sale_price, sale_date, created_by)
    VALUES (?, ?, ?, ?, ?)");

    // Update product stock (subtract)
    $updateStock = $conn->prepare("UPDATE product 
            SET quantity_in_stock = quantity_in_stock - ? 
            WHERE id = ?");

    $createdAt = $updatedAt = date('Y-m-d H:i:s');

    foreach ($productIds as $i => $prodId) {

      $prodId = intval($prodId);
      $qty = intval($quantities[$i]);
      $sPrice = floatval($salePrices[$i]);
      $sDate = !empty($saleDates[$i]) ? $saleDates[$i] : date('Y-m-d');

      if ($qty <= 0) continue;

      // Check stock first
      $checkStock = $conn->query("SELECT quantity_in_stock FROM product WHERE id = $prodId")->fetch_assoc();
      if ($checkStock['quantity_in_stock'] < $qty) {
        $errors[] = "Not enough stock for product ID $prodId.";
        continue;
      }

      // Insert sale record
      $stmt->bind_param("iidis", $prodId, $qty, $sPrice, $sDate, $user_id);

      if (!$stmt->execute()) {
        $errors[] = "Failed to add sale for product ID $prodId: " . $stmt->error;
      } else {
        // Deduct stock
        $updateStock->bind_param("ii", $qty, $prodId);
        $updateStock->execute();
      }
    }

    $stmt->close();
    $updateStock->close();

    if (empty($errors)) {
      $_SESSION['success_message'] = "Sale(s) added successfully!";
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
      font-size: 1.2rem;
      margin-bottom: 15px;
      border-left: 4px solid #0d6efd;
      padding-left: 10px;
    }

    .total-price {
      font-weight: 600;
      margin-top: 0.5rem;
    }

    .stock-info {
      font-size: 0.85rem;
      color: #0d6efd;
    }

    .product-row {
      border: 1px solid #0d6efd33;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 15px;
      background-color: #f9f9f9;
    }

    .btn-add-product,
    .btn-remove-product {
      width: 100%;
    }

    @media (min-width: 768px) {

      .btn-add-product,
      .btn-remove-product {
        width: auto;
      }
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
          <h3>Add New Sell</h3>
        </div>
      </div>

      <?php if ($formError): ?>
        <div class="alert alert-danger mt-3"><?= $formError ?></div>
      <?php endif; ?>

      <div class="app-content-body mt-3">
        <div class="container-fluid">
          <div class="card shadow-sm p-4">
            <div class="alert alert-info"><i class="bi bi-info-circle"></i> Once a sell is created, it cannot be edited.</div>

            <form method="post" id="purchaseForm">
              <div class="form-section-title">Products</div>
              <div id="productsContainer">
                <div class="product-row">
                  <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                      <label class="form-label">Product</label>
                      <select name="product_id[]" class="form-select product-select" required>
                        <option value="">-- Select Product --</option>
                        <?php
                        $products->data_seek(0);
                        while ($prod = $products->fetch_assoc()):
                        ?>
                          <option value="<?= $prod['id'] ?>" data-price="<?= $prod['price'] ?>" data-stock="<?= $prod['quantity_in_stock'] ?>">
                            <?= htmlspecialchars($prod['name']) ?>
                          </option>
                        <?php endwhile; ?>
                      </select>
                      <div class="stock-info mt-1">In Stock: <span class="stock-amount">0</span></div>
                    </div>

                    <div class="col-md-2" style="padding-bottom: 25px;">
                      <label class="form-label">Quantity</label>
                      <input type="number" name="quantity[]" class="form-control quantity" min="1" value="1" required>
                    </div>

                    <div class="col-md-3" style="padding-bottom: 25px;">
                      <label class="form-label">Purchase Price</label>
                      <input type="number" name="purchase_price[]" class="form-control purchase-price" step="0.01" min="0" required readonly>
                    </div>

                    <div class="col-md-3" style="padding-bottom: 25px;">
                      <label class="form-label">Total Price</label>
                      <input type="text" class="form-control total-price" value="0" readonly>
                    </div>
                  </div>

                  <div class="row g-3 mt-1">
                    <div class="col-md-3">
                      <label class="form-label">Sale Price</label>
                      <input type="number" name="sale_price[]" class="form-control" step="0.01" min="0">
                    </div>

                    <div class="col-md-3">
                      <label class="form-label">Sale Date</label>
                      <input type="date" name="sale_date[]" class="form-control">
                    </div>

                    <div class="col-md-3 " style="padding-top: 30px;">
                      <button type="button" class="btn btn-success btn-add-product"><i class="bi bi-plus-circle"></i></button>
                      <button type="button" class="btn btn-danger btn-remove-product"><i class="bi bi-dash-circle"></i></button>
                    </div>
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
      function updateTotal(row) {
        const qty = parseFloat(row.find('.quantity').val() || 0);
        const price = parseFloat(row.find('.purchase-price').val() || 0);
        const total = qty * price;

        // Format with commas and 2 decimals
        row.find('.total-price').val(total.toLocaleString(undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }));
      }

      // Trigger update when quantity or purchase price changes
      $(document).on('input', '.quantity, .purchase-price', function() {
        const row = $(this).closest('.product-row');
        updateTotal(row);
      });


      $(document).on('change', '.product-select', function() {
        const price = parseFloat($(this).find(':selected').data('price') || 0);
        const stock = parseInt($(this).find(':selected').data('stock') || 0);
        const row = $(this).closest('.product-row');
        row.find('.purchase-price').val(price);
        row.find('.stock-amount').text(stock);
        updateTotal(row);
      });

      $(document).on('click', '.btn-add-product', function() {
        const row = $(this).closest('.product-row').clone();
        row.find('input').val('');
        row.find('select').val('');
        row.find('.stock-amount').text('0');
        row.find('.total-price').val('0');
        $('#productsContainer').append(row);
      });

      $(document).on('click', '.btn-remove-product', function() {
        if ($('.product-row').length > 1) {
          $(this).closest('.product-row').remove();
        }
      });
    });
  </script>
</body>

</html>