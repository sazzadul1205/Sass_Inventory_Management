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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Purchase | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">

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

// Fetch all products with supplier info
$products = $conn->query("
  SELECT 
    p.id, p.name, p.price, p.quantity_in_stock, s.name AS supplier_name
  FROM product p
  LEFT JOIN supplier s ON p.supplier_id = s.id
  ORDER BY p.name ASC
");

// Handle form submission
if (isset($_POST['submit'])) {
  $quantities = $_POST['quantity'] ?? [];
  $productIds = $_POST['product_id'] ?? [];
  $purchasePrice = $_POST['purchase_price'] ?? [];
  $purchaseDates = $_POST['purchase_date'] ?? [];

  if (empty($productIds)) {
    $formError = "Please select at least one product.";
  } else {
    $errors = [];
    $stmt = $conn->prepare("INSERT INTO purchase (product_id, supplier_id, quantity, purchase_price, purchase_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $updateStock = $conn->prepare("UPDATE product SET quantity_in_stock = quantity_in_stock + ? WHERE id = ?");
    $createdAt = $updatedAt = date('Y-m-d H:i:s');

    $lastPurchaseId = null;

    foreach ($productIds as $i => $prodId) {
      $prodId = intval($prodId);
      $qty = intval($quantities[$i]);
      $price = floatval($purchasePrice[$i]);
      $purchaseDate = !empty($purchaseDates[$i]) ? $purchaseDates[$i] : date('Y-m-d');

      if ($qty <= 0) continue;

      $productRow = $conn->query("SELECT supplier_id FROM product WHERE id = $prodId")->fetch_assoc();
      $supplierId = $productRow['supplier_id'];

      $stmt->bind_param("iiidsss", $prodId, $supplierId, $qty, $price, $purchaseDate, $createdAt, $updatedAt);
      if (!$stmt->execute()) {
        $errors[] = "Failed to add product ID $prodId: " . $stmt->error;
      } else {
        // Capture the last inserted purchase ID
        $lastPurchaseId = $stmt->insert_id;

        // Update product stock
        $updateStock->bind_param("ii", $qty, $prodId);
        $updateStock->execute();
      }
    }

    $stmt->close();
    $updateStock->close();

    if (empty($errors) && $lastPurchaseId) {
      // Redirect to receipt page using last purchase ID
      header("Location: receipt.php?id=" . $lastPurchaseId);
      exit;
    } elseif (!empty($errors)) {
      $formError = implode("<br>", $errors);
    } else {
      $formError = "Purchase was not added. Please try again.";
    }
  }
}

?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <?php include_once '../Inc/Navbar.php'; ?>
    <?php include_once '../Inc/Sidebar.php'; ?>

    <main class="app-main">
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <h3 class="mb-0" style="font-weight: 800;">Add New Purchase</h3>
        </div>
      </div>

      <?php if (!empty($formError)): ?>
        <div id="errorBox" class="alert alert-danger text-center"><?= htmlspecialchars($formError) ?></div>
      <?php endif; ?>

      <div class="app-content-body mt-4">
        <div class="container-fluid">
          <div class="card card-custom shadow-sm">
            <div class="card-body p-4">
              <h4 class="mb-4">Add Purchase Information</h4>

              <form method="post" id="purchaseForm">
                <div class="alert alert-info mt-3">
                  <i class="bi bi-info-circle"></i> Once a purchase is created, it cannot be edited.
                </div>

                <!-- Product Row -->
                <div class="row pb-2 product-row">
                  <div class="col-md-4">
                    <label class="form-label">Product</label>
                    <select name="product_id[]" class="form-select product-select" required>
                      <option value="">-- Select Product --</option>
                      <?php while ($prod = $products->fetch_assoc()): ?>
                        <option value="<?= $prod['id'] ?>"
                          data-price="<?= $prod['price'] ?>"
                          data-stock="<?= $prod['quantity_in_stock'] ?>"
                          data-supplier="<?= htmlspecialchars($prod['supplier_name']) ?>">
                          <?= htmlspecialchars($prod['name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <div class="col-md-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity[]" class="form-control" min="1" value="1" required>
                  </div>

                  <div class="col-md-2">
                    <label class="form-label">Purchase Price</label>
                    <input type="text" name="purchase_price[]" class="form-control purchase-price" required>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label">Purchase Date</label>
                    <input type="date" name="purchase_date[]" class="form-control" value="<?= date('Y-m-d') ?>" required>
                  </div>

                  <div class="col-md-1 d-flex align-items-end btn-box">
                    <button type="button" class="btn btn-success btn-add-product me-1">
                      <i class="bi bi-plus-circle"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-remove-product">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </div>

                <!-- Product Info -->
                <div class="product-info text-muted small">
                  Default Estimated Price: <span class="info-price">-</span> |
                  Current Available Stock: <span class="info-stock">-</span> |
                  Supplier Name: <span class="info-supplier">-</span> |
                  <span class="comparison-text text-muted">Difference per unit: - (-%)</span>
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
      </div>
    </main>

    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- Custom JS -->
  <script>
    $(document).ready(function() {
      const $form = $('#purchaseForm');

      function formatNumberWithCommas(x) {
        if (!x && x !== 0) return '';
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      }

      function formatCurrency(num) {
        if (isNaN(num)) return '';
        return new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: 'USD'
        }).format(num);
      }

      function updateProductInfo($row) {
        const quantity = parseInt($row.find('input[name="quantity[]"]').val()) || 1;
        const totalPrice = parseFloat($row.find('.purchase-price').data('raw')) || 0;
        const unitPrice = quantity > 0 ? totalPrice / quantity : 0;
        const $option = $row.find('.product-select option:selected');
        const defaultUnitPrice = parseFloat($option.data('price')) || 0;

        // Update global product info section
        $('.info-price').text(formatCurrency(defaultUnitPrice));
        $('.info-stock').text($option.data('stock') ?? '-');
        $('.info-supplier').text($option.data('supplier') ?? '-');

        if (defaultUnitPrice === 0 || unitPrice === 0) {
          $('.comparison-text')
            .text('Difference per unit: - (-%)')
            .removeClass('text-success text-danger text-secondary')
            .addClass('text-muted');
          return;
        }

        const diffPerUnit = unitPrice - defaultUnitPrice;
        const diffPercent = ((diffPerUnit / defaultUnitPrice) * 100).toFixed(2);
        let textColor = 'text-secondary';
        if (diffPerUnit < 0) textColor = 'text-success';
        if (diffPerUnit > 0) textColor = 'text-danger';
        const sign = diffPerUnit >= 0 ? '+' : '';
        $('.comparison-text')
          .text(`Difference per unit: ${sign}${diffPerUnit.toFixed(2)} (${sign}${diffPercent}%)`)
          .removeClass('text-success text-danger text-secondary text-muted')
          .addClass(textColor);
      }

      function handleRowUpdate($row, forceRecalc = false) {
        const quantity = parseInt($row.find('input[name="quantity[]"]').val()) || 1;
        const defaultUnitPrice = parseFloat($row.find('.product-select option:selected').data('price')) || 0;

        let rawVal = parseFloat($row.find('.purchase-price').data('raw')) || 0;

        // If rawVal is 0 or forceRecalc is true, calculate total automatically
        if (rawVal === 0 || forceRecalc) {
          rawVal = defaultUnitPrice * quantity;
          $row.find('.purchase-price').val(formatNumberWithCommas(rawVal.toFixed(2)));
          $row.find('.purchase-price').data('raw', rawVal);
        }

        updateProductInfo($row);
      }

      // Initialize rows on page load
      $('.product-row').each(function() {
        const $row = $(this);
        const $option = $row.find('.product-select option:selected');
        if ($option.val()) {
          handleRowUpdate($row, true);
        }
      });

      // Product selection change
      $form.on('change', '.product-select', function() {
        const $row = $(this).closest('.product-row');
        handleRowUpdate($row, true);
      });

      // Quantity change
      $form.on('input', 'input[name="quantity[]"]', function() {
        const $row = $(this).closest('.product-row');
        handleRowUpdate($row, true);
      });

      // Manual purchase price input
      $form.on('input', '.purchase-price', function() {
        const $row = $(this).closest('.product-row');
        const val = parseFloat($(this).val().replace(/,/g, '')) || 0;
        $(this).data('raw', val);

        updateProductInfo($row);
      });

      // Add row
      $form.on('click', '.btn-add-product', function() {
        const $lastRow = $('.product-row').last();
        const $newRow = $lastRow.clone();

        $newRow.find('.product-select').val('');
        $newRow.find('input[name="quantity[]"]').val(1);
        $newRow.find('.purchase-price').val('').data('raw', 0);
        $newRow.find('input[name="purchase_date[]"]').val('<?= date('Y-m-d') ?>');

        $lastRow.after($newRow);
      });

      // Remove row
      $form.on('click', '.btn-remove-product', function() {
        const $allRows = $('.product-row');
        const $row = $(this).closest('.product-row');
        if ($allRows.length > 1) {
          $row.remove();
          handleRowUpdate($('.product-row').last(), true);
        } else {
          $row.find('.product-select').val('');
          $row.find('input[name="quantity[]"]').val(1);
          $row.find('.purchase-price').val('').data('raw', 0);
          $row.find('input[name="purchase_date[]"]').val('<?= date('Y-m-d') ?>');
          $('.info-price, .info-stock, .info-supplier').text('-');
          $('.comparison-text')
            .text('Difference per unit: - (-%)')
            .removeClass('text-success text-danger text-secondary')
            .addClass('text-muted');
        }
      });
    });
  </script>
</body>

</html>