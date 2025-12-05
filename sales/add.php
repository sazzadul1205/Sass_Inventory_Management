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
  <title>Add Sell | Sass Inventory Management System</title>
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

  <!-- DataTables -->
  <link rel="stylesheet"
    href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

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

    /* Make Select2 slightly taller than default Bootstrap input */
    .select2-container--default .select2-selection--single {
      height: calc(1.75em + 1rem + 2px);
      padding: 0.5rem 0.75rem;
      line-height: 1.75;
      border-radius: 0.5rem;
      border: 1px solid #ced4da;
      background-color: #fff;
      color: #495057;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 1.75;
      padding-left: 0;
      padding-right: 0;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: calc(1.75em + 1rem + 2px);
      top: 0;
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
      height: auto;
      line-height: 1.5;
    }

    /* Total amount display styling */
    #totalAmountDisplay {
      font-weight: 700;
      font-size: 1.2rem;
    }
  </style>
</head>

<?php
$formError = "";

// Connect to DB
$conn = connectDB();

// Fetch all products with stock > 0 for dropdown
$products = $conn->query("
  SELECT p.id, p.name, p.price, p.quantity_in_stock, s.id AS supplier_id, s.name AS supplier_name
  FROM product p
  LEFT JOIN supplier s ON p.supplier_id = s.id
  WHERE p.quantity_in_stock > 0
  ORDER BY p.name ASC
");

// Check if the sell form was submitted
if (isset($_POST['submit'])) {
  $quantities = $_POST['quantity'] ?? [];
  $productIds = $_POST['product_id'] ?? [];
  $salePrices = $_POST['purchase_price'] ?? []; // same input, just renamed logically
  $saleDates = $_POST['purchase_date'] ?? [];
  $soldBy = $_SESSION['user_id'] ?? 0;

  // Generate unique receipt number for sale
  $today = date('Ymd');
  $randomHash = substr(md5(uniqid('', true)), 0, 32 - strlen($today . $soldBy));
  $receiptNumber = $today . $soldBy . $randomHash;

  if (empty($productIds)) {
    $formError = "Please select at least one product.";
  } else {
    $allData = [];

    foreach ($productIds as $i => $prodId) {
      $prodId = intval($prodId);
      $qty = intval($quantities[$i]);
      $price = floatval(str_replace(',', '', $salePrices[$i]));
      $saleDate = !empty($saleDates[$i]) ? $saleDates[$i] : date('Y-m-d');

      if ($qty <= 0 || $price <= 0) continue;

      $productRow = $conn->query("SELECT quantity_in_stock, name FROM product WHERE id = $prodId")->fetch_assoc();
      $stock = $productRow['quantity_in_stock'] ?? 0;
      $productName = $productRow['name'] ?? 'Unknown';

      // Skip if not enough stock
      if ($qty > $stock) continue;

      $allData[] = [
        'product_id' => $prodId,
        'product_name' => $productName,
        'quantity' => $qty,
        'sale_price' => $price,
        'sale_date' => $saleDate
      ];
    }

    if (empty($allData)) {
      $formError = "No valid products selected or quantity exceeds stock.";
    } else {
      $totalAmount = isset($_POST['total_amount']) ? floatval($_POST['total_amount']) : 0;

      $conn->begin_transaction();
      try {
        // Insert into receipt table
        $stmtReceipt = $conn->prepare("
                    INSERT INTO receipt 
                    (receipt_number, type, total_amount, created_by, created_at, updated_at)
                    VALUES (?, 'sale', ?, ?, NOW(), NOW())
                ");
        $stmtReceipt->bind_param("sdi", $receiptNumber, $totalAmount, $soldBy);
        $stmtReceipt->execute();
        $receiptId = $stmtReceipt->insert_id;
        $stmtReceipt->close();

        // Insert into sale table
        $stmtSale = $conn->prepare("
                    INSERT INTO sale 
                    (product_id, quantity, sale_price, sale_date, created_at, updated_at, receipt_id, sold_by)
                    VALUES (?, ?, ?, ?, NOW(), NOW(), ?, ?)
                ");

        foreach ($allData as $item) {
          $stmtSale->bind_param(
            "iidsii",
            $item['product_id'],
            $item['quantity'],
            $item['sale_price'],
            $item['sale_date'],
            $receiptId,
            $soldBy
          );

          $stmtSale->execute();

          // Update stock (subtract sold quantity)
          $conn->query("
                        UPDATE product 
                        SET quantity_in_stock = quantity_in_stock - {$item['quantity']},
                            updated_at = NOW()
                        WHERE id = {$item['product_id']}
                    ");
        }

        $stmtSale->close();
        $conn->commit();

        // Redirect to receipt page
        header("Location: receipt.php?id=" . $receiptId);
        exit;
      } catch (Exception $e) {
        $conn->rollback();
        $formError = "Error saving sale: " . $e->getMessage();
      }
    }
  }
}


?>

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
          <h3 class="mb-0" style="font-weight: 800;">Add New Sell</h3>
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
              <h4 class="mb-4">Add Sell Information</h4>

              <!-- Form -->
              <form method="post" id="sellForm">

                <!-- Alert -->
                <div class="alert alert-info mt-3">
                  <i class="bi bi-info-circle"></i> Once a sell is created, it cannot be edited.
                </div>

                <!-- Product Row -->
                <div class="row pb-2 product-row">

                  <!-- Product -->
                  <div class="col-md-4">
                    <label class="form-label">Product Name</label>
                    <select name="product_id[]" class="form-select select-product" required>
                      <option value="">-- Select Product --</option>
                      <?php
                      $products->data_seek(0); // reset pointer
                      while ($prod = $products->fetch_assoc()):
                      ?>
                        <option value="<?= $prod['id'] ?>"
                          data-price="<?= $prod['price'] ?>"
                          data-stock="<?= $prod['quantity_in_stock'] ?>"
                          data-supplier="<?= htmlspecialchars($prod['supplier_name'] ?? '') ?>"
                          data-supplier-id="<?= $prod['supplier_id'] ?? 0 ?>">
                          <?= htmlspecialchars($prod['name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <!-- Quantity -->
                  <div class="col-md-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity[]" class="form-control" min="1" value="1" required>
                  </div>

                  <!-- Sell Price -->
                  <div class="col-md-2">
                    <label class="form-label">Sell Price</label>
                    <input type="text" name="purchase_price[]" class="form-control purchase-price" required>
                  </div>

                  <!-- Sell Date -->
                  <div class="col-md-3">
                    <label class="form-label">Sell Date</label>
                    <input type="date" name="purchase_date[]" class="form-control" value="<?= date('Y-m-d') ?>" required>
                  </div>

                  <!-- Buttons -->
                  <div class="col-md-1 d-flex align-items-end btn-box">
                    <button type="button" class="btn btn-success btn-add-product me-1">
                      <i class="bi bi-plus-circle"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-remove-product">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>

                  <!-- Hidden supplier ID for procedure -->
                  <input type="hidden" name="supplier_id[]" class="supplier-id-hidden" value="">

                  <!-- Hidden Total Amount -->
                  <input type="hidden" id="total_amount" name="total_amount" value="0">

                  <!-- Product Info -->
                  <div class="col-12 mt-2 product-info text-muted small">
                    Default Estimated Price: <span class="info-price">-</span> |
                    Current Available Stock: <span class="info-stock">-</span> |
                    <span class="comparison-text text-muted">Difference per unit: - (-%)</span>
                  </div>
                </div>

                <!-- Total Amount & Save & Cancel -->
                <div class="mt-4 d-flex justify-content-between align-items-center">
                  <!-- Save & Cancel Buttons -->
                  <div class="d-flex gap-2">
                    <button type="submit" name="submit" class="btn btn-primary px-4 py-2">
                      <i class="bi bi-check2-circle"></i> Save Sell
                    </button>
                    <a href="index.php" class="btn btn-secondary px-4 py-2">
                      <i class="bi bi-x-circle"></i> Cancel
                    </a>
                  </div>

                  <!-- Total Amount Display -->
                  <div class="text-start">
                    <h5>Total Sell Amount: <span id="totalAmountDisplay">0.00</span></h5>
                  </div>
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

  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- Custom JS -->
  <script>
    $(document).ready(function() {
      const $form = $('#sellForm');

      // Format number with commas
      function formatNumberWithCommas(x) {
        if (!x && x !== 0) return '';
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      }

      // Format currency (for info display)
      function formatCurrency(num) {
        if (isNaN(num)) return '';
        return new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: 'USD'
        }).format(num);
      }

      // Update product info display (default price, stock, supplier, difference)
      // Update product info display (default price, stock, supplier, difference)
      function updateProductInfo($row) {
        const quantity = parseInt($row.find('input[name="quantity[]"]').val()) || 1;
        const unitPrice = parseFloat($row.find('.purchase-price').data('raw')) || 0;
        const $option = $row.find('.select-product option:selected');
        const defaultUnitPrice = parseFloat($option.data('price')) || 0;

        const $info = $row.find('.product-info');
        $info.find('.info-price').text(formatCurrency(defaultUnitPrice));
        $info.find('.info-stock').text($option.data('stock') ?? '-');
        $info.find('.info-supplier').text($option.data('supplier') ?? '-');

        if (defaultUnitPrice === 0 || unitPrice === 0) {
          $info.find('.comparison-text')
            .text('Difference per unit: - (-%)')
            .removeClass('text-success text-danger text-secondary')
            .addClass('text-muted');
          return;
        }

        // Reverse difference for upsell (sale)
        const diffPerUnit = unitPrice - defaultUnitPrice; // or defaultUnitPrice - unitPrice depending on profit calc
        const diffPercent = ((diffPerUnit / defaultUnitPrice) * 100).toFixed(2);

        let textColor = 'text-secondary';
        if (diffPerUnit > 0) textColor = 'text-success'; // profit → green
        if (diffPerUnit < 0) textColor = 'text-danger'; // loss → red
        const sign = diffPerUnit >= 0 ? '+' : '';

        $info.find('.comparison-text')
          .text(`Profit per unit: ${sign}${diffPerUnit.toFixed(2)} (${sign}${diffPercent}%)`)
          .removeClass('text-success text-danger text-secondary text-muted')
          .addClass(textColor);
      }


      // Handle row update
      function handleRowUpdate($row, forceRecalc = false) {
        const quantity = parseInt($row.find('input[name="quantity[]"]').val()) || 1;
        const defaultUnitPrice = parseFloat($row.find('.select-product option:selected').data('price')) || 0;
        let unitPrice = parseFloat($row.find('.purchase-price').data('raw')) || 0;

        if (unitPrice === 0 || forceRecalc) {
          unitPrice = defaultUnitPrice;
          $row.find('.purchase-price').data('raw', unitPrice);

          // Show total in input
          const displayVal = (unitPrice * quantity).toFixed(2);
          $row.find('.purchase-price').val(formatNumberWithCommas(displayVal));
        }

        updateProductInfo($row);
        updateTotalAmount();
      }

      // Initialize Select2
      function initSelect2($element) {
        $element.select2({
          placeholder: "-- Select Product --",
          allowClear: true,
          width: '100%',
          dropdownParent: $('body')
        });
      }

      // Update total amount
      function updateTotalAmount() {
        let total = 0;
        $('.product-row').each(function() {
          const qty = parseFloat($(this).find('input[name="quantity[]"]').val()) || 0;
          const unitPrice = parseFloat($(this).find('.purchase-price').data('raw')) || 0;
          total += qty * unitPrice;
        });

        const formatted = formatNumberWithCommas(total.toFixed(2));
        $('#totalAmountDisplay').text(formatted);

        //  Store raw number in hidden input (no commas!)
        $('#total_amount').val(total.toFixed(2));
      }


      // Initialize first row
      initSelect2($('.select-product'));

      // Event: Product change
      $form.on('change', '.select-product', function() {
        const $row = $(this).closest('.product-row');
        const supplierId = $(this).find('option:selected').data('supplier-id') || 0;
        $row.find('.supplier-id-hidden').val(supplierId);

        handleRowUpdate($row, true);
      });


      // Event: Quantity change with stock validation
      $form.on('input', 'input[name="quantity[]"]', function() {
        const $row = $(this).closest('.product-row');
        const $select = $row.find('.select-product option:selected');
        const maxStock = parseInt($select.data('stock')) || 0;
        let qty = parseInt($(this).val()) || 0;

        // If quantity exceeds stock, reset to max and show visual feedback
        if (qty > maxStock) {
          qty = maxStock;
          $(this).val(qty);
          $(this).addClass('is-invalid');
        } else {
          $(this).removeClass('is-invalid');
        }

        // Update the stock info live
        const $info = $row.find('.product-info');
        $info.find('.info-stock').text(maxStock);

        handleRowUpdate($row, true);
      });

      // Before form submission, check stock
      $form.on('submit', function(e) {
        let valid = true;
        $('.product-row').each(function() {
          const qty = parseInt($(this).find('input[name="quantity[]"]').val()) || 0;
          const maxStock = parseInt($(this).find('.select-product option:selected').data('stock')) || 0;
          if (qty > maxStock) {
            valid = false;
            $(this).find('input[name="quantity[]"]').addClass('is-invalid');
          }
        });

        if (!valid) {
          e.preventDefault();
          alert('Quantity cannot exceed current available stock!');
          return false;
        }
      });


      // Event: Manual purchase price input
      $form.on('input', '.purchase-price', function() {
        const $row = $(this).closest('.product-row');
        let input = this;
        let val = input.value;

        // Save cursor position
        let selectionStart = input.selectionStart;

        // Count how many commas are to the left of cursor
        let commasBefore = (val.slice(0, selectionStart).match(/,/g) || []).length;

        // Remove commas to get numeric value
        let numericVal = val.replace(/,/g, '');
        let floatVal = parseFloat(numericVal) || 0;

        // Store as unit price (divide by quantity)
        const quantity = parseInt($row.find('input[name="quantity[]"]').val()) || 1;
        const unitPrice = floatVal / quantity;
        $(input).data('raw', unitPrice);

        // Format with commas for display
        let formattedVal = formatNumberWithCommas((unitPrice * quantity).toFixed(2));
        input.value = formattedVal;

        // Calculate new cursor position
        let newCommasBefore = (formattedVal.slice(0, selectionStart).match(/,/g) || []).length;
        let diffCommas = newCommasBefore - commasBefore;
        input.selectionStart = input.selectionEnd = selectionStart + diffCommas;

        updateProductInfo($row);
        updateTotalAmount();
      });


      $form.on('blur', '.purchase-price', function() {
        let val = parseFloat($(this).val().replace(/,/g, '')) || 0;
        $(this).val(formatNumberWithCommas(val.toFixed(2)));
      });

      // Add new row
      // Add new row manually (no cloning previous)
      $form.on('click', '.btn-add-product', function() {
        const $newRow = $(`
        <div class="row pb-2 product-row">
          <div class="col-md-4">
            <label class="form-label">Product</label>
            <select name="product_id[]" class="form-select select-product" required>
              <option value="">-- Select Product --</option>
              <?php
              $products->data_seek(0);
              while ($prod = $products->fetch_assoc()):
              ?>
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
            <div class="col-12 mt-2 product-info text-muted small">
              Default Estimated Price: <span class="info-price">-</span> |
              Current Available Stock: <span class="info-stock">-</span> |
              Supplier Name: <span class="info-supplier">-</span> |
              <span class="comparison-text text-muted">Difference per unit: - (-%)</span>
            </div>
          </div>`);

        $('.product-row').last().after($newRow);
        initSelect2($newRow.find('.select-product'));
      });


      // Remove row
      $form.on('click', '.btn-remove-product', function() {
        const $allRows = $('.product-row');
        const $row = $(this).closest('.product-row');

        if ($allRows.length > 1) {
          $row.remove();
        } else {
          $row.find('select.select-product').val('').trigger('change');
          $row.find('input[name="quantity[]"]').val(1);
          $row.find('input[name="purchase_price[]"]').val('').data('raw', 0);
          $row.find('input[name="purchase_date[]"]').val('<?= date('Y-m-d') ?>');
          const $info = $row.find('.product-info');
          $info.find('.info-price').text('-');
          $info.find('.info-stock').text('-');
          $info.find('.info-supplier').text('-');
          $info.find('.comparison-text')
            .text('Difference per unit: - (-%)')
            .removeClass('text-success text-danger text-secondary')
            .addClass('text-muted');
        }
        updateTotalAmount();
      });

      // Initial total calculation
      updateTotalAmount();
    });
  </script>

</body>

</html>