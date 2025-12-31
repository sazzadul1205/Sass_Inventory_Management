<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('view_receipt', '../index.php');

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
  <title>Sales Receipt | Sass Inventory</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Fonts & Icons -->
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    media="print" onload="this.media='all'" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">

  <!-- AdminLTE CSS -->
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />

  <!-- Custom CSS -->
  <style>
    .card {
      border-radius: 12px;
      margin: 20px;
      margin-top: 30px;
      padding: 20px;
      margin-bottom: 20px;
    }

    .table td,
    .table th {
      vertical-align: middle;
    }

    h4,
    h5,
    h6 {
      font-weight: 700;
    }

    /* POS view styles */
    #posView {
      display: none;
      font-size: 12px;
      max-width: 320px;
      margin: 0 auto;
      background: #fff;
      padding: 10px;
      border: 1px dashed #000;
    }

    #posView .table {
      font-size: 12px;
      width: 100%;
      margin-top: 10px;
    }

    /* Signature block */
    .signature {
      margin-top: 30px;
      display: flex;
      justify-content: space-between;
    }

    .signature div {
      text-align: center;
    }
  </style>
</head>

<?php
$receiptId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($receiptId <= 0) die("Invalid receipt ID.");

$conn = connectDB();

// --- Get Sale Receipt Info ---
$stmt = $conn->prepare("
    SELECT r.*, u.username AS seller_name
    FROM receipt r
    LEFT JOIN user u ON r.created_by = u.id
    WHERE r.id = ?
");
$stmt->bind_param("i", $receiptId);
$stmt->execute();
$receipt = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$receipt) die("Sale receipt not found.");

// --- Get Sale Items with all details ---
$stmt = $conn->prepare("
    SELECT 
        s.*, 
        pr.name AS product_name,
        s.lot,
        s.quantity,
        s.sale_price,
        s.vat_percent,
        s.buyer_name,
        s.buyer_phone,
        s.sale_date
    FROM sale s
    LEFT JOIN product pr ON s.product_id = pr.id
    WHERE s.receipt_id = ?
    ORDER BY s.id ASC
");
$stmt->bind_param("i", $receiptId);
$stmt->execute();
$saleItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- Calculate totals with VAT ---
$totalQty = 0;
$totalAmount = 0;
$totalVat = 0;
$subTotal = 0;

foreach ($saleItems as $item) {
  $qty = (int)$item['quantity'];
  $unitPrice = (float)$item['sale_price'];
  $vatPercent = (float)($item['vat_percent'] ?? 0);

  $itemSubtotal = $qty * $unitPrice;
  $itemVat = $itemSubtotal * ($vatPercent / 100);
  $itemTotal = $itemSubtotal + $itemVat;

  $totalQty += $qty;
  $subTotal += $itemSubtotal;
  $totalVat += $itemVat;
  $totalAmount += $itemTotal;
}

// --- Get discount value & final total from receipt ---
$discountValue = floatval($receipt['discount_value'] ?? 0);
$finalTotal = $totalAmount - $discountValue;

// --- Get buyer info from first sale item ---
$buyerName = $saleItems[0]['buyer_name'] ?? '';
$buyerPhone = $saleItems[0]['buyer_phone'] ?? '';
$saleDate = $saleItems[0]['sale_date'] ?? $receipt['created_at'];
?>

<!-- Body -->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">

    <!-- Header -->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!-- Sidebar -->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!-- Main Content -->
    <main class="app-main">
      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <!-- Page Title -->
          <h3 class="mb-0" style="font-weight: 800;">Sales Receipt</h3>

          <!-- View Selector Buttons -->
          <div class="d-flex gap-2">
            <button id="btnA4" class="btn btn-primary" style="width: 100px;">A4 View</button>
            <button id="btnPOS" class="btn btn-secondary" style="width: 100px;">POS View</button>
          </div>
        </div>
      </div>

      <!-- ========================= A4 View ========================= -->
      <div id="a4View" class="card p-4">
        <!-- Header & Customer Info -->
        <div class="row mb-4">
          <div class="col-md-6">
            <h2 class="fw-bold mb-1"><?= strtoupper($Project_Name ?? 'Sass Inventory') ?></h2>
            <p class="mb-1">Your Trusted Inventory Solution</p>
            <p class="mb-1">Address: 123, Main Street, City</p>
            <p class="mb-1">Phone: +880123456789 | Email: info@sassinventory.com</p>
            <p class="mb-1"><strong>Customer:</strong> <?= htmlspecialchars($buyerName) ?></p>
            <?php if (!empty($buyerPhone)): ?>
              <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($buyerPhone) ?></p>
            <?php endif; ?>
          </div>
          <div class="col-md-6 text-md-end">
            <h3 class="fw-bold mb-1">Sales Receipt</h3>
            <p class="mb-0">(Customer Copy)</p>
            <p><strong>Receipt #:</strong> <?= htmlspecialchars($receipt['receipt_number']) ?></p>
            <p><strong>Date:</strong> <?= date('Y-m-d', strtotime($saleDate)) ?></p>
            <p><strong>Time:</strong> <?= date('H:i', strtotime($receipt['created_at'])) ?></p>
            <p><strong>Sold By:</strong> <?= htmlspecialchars($receipt['seller_name']) ?></p>
          </div>
        </div>

        <!-- Sale Table -->
        <table class="table table-bordered table-striped mt-3">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Product</th>
              <th>Lot</th>
              <th>Qty</th>
              <th>Unit Price</th>
              <th>VAT (%)</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($saleItems as $index => $item):
              $qty = (int)$item['quantity'];
              $unitPrice = (float)$item['sale_price'];
              $vatPercent = (float)($item['vat_percent'] ?? 0);
              $itemSubtotal = $qty * $unitPrice;
              $itemVat = $itemSubtotal * ($vatPercent / 100);
              $itemTotal = $itemSubtotal + $itemVat;
            ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= htmlspecialchars($item['lot']) ?></td>
                <td><?= $qty ?></td>
                <td><?= number_format($unitPrice, 2) ?></td>
                <td><?= number_format($vatPercent, 2) ?></td>
                <td><?= number_format($itemTotal, 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot class="table-light">
            <tr>
              <th colspan="3">Subtotal</th>
              <th><?= $totalQty ?></th>
              <th></th>
              <th></th>
              <th><?= number_format($subTotal, 2) ?></th>
            </tr>
            <tr>
              <th colspan="6">VAT Amount</th>
              <th><?= number_format($totalVat, 2) ?></th>
            </tr>
            <tr>
              <th colspan="6">Total Amount</th>
              <th><?= number_format($totalAmount, 2) ?></th>
            </tr>
            <tr>
              <th colspan="6">Discount
                (<?php
                  if ($totalAmount > 0):
                    echo number_format(($discountValue / $totalAmount) * 100, 2) . '%';
                  else:
                    echo '0%';
                  endif;
                  ?>)
              </th>
              <th><?= number_format($discountValue, 2) ?></th>
            </tr>
            <tr>
              <th colspan="6">Final Total</th>
              <th class="text-success"><?= number_format($finalTotal, 2) ?></th>
            </tr>
          </tfoot>
        </table>

        <!-- Terms & Conditions -->
        <div class="mt-4">
          <h6>Terms & Conditions:</h6>
          <p class="mb-1">1. Goods once sold cannot be returned or exchanged.</p>
          <p class="mb-1">2. This is a computer generated receipt.</p>
          <p class="mb-1">3. All disputes are subject to jurisdiction of local courts.</p>
        </div>

        <!-- Signature Block -->
        <div class="signature mt-4">
          <div>________________<br>Customer Signature</div>
          <div>________________<br>Seller Signature</div>
          <div>________________<br>Authorized Signature</div>
        </div>

        <!-- Print & Download Buttons -->
        <div class="mt-3 d-flex justify-content-center gap-2">
          <button class="btn btn-primary btn-sm" onclick="window.print()">Print</button>
          <button class="btn btn-success btn-sm" onclick="downloadPDF('a4View')">Download</button>
        </div>
      </div>

      <!-- ========================= POS View ========================= -->
      <div id="posView" class="card p-3">
        <div class="text-center mb-3">
          <h5 class="fw-bold mb-1"><?= strtoupper($Project_Name ?? 'Sass Inventory') ?></h5>
          <p class="mb-0">Your Trusted Inventory Solution</p>
          <p class="mb-0">Customer: <?= htmlspecialchars($buyerName) ?></p>
          <?php if (!empty($buyerPhone)): ?>
            <p class="mb-0">Phone: <?= htmlspecialchars($buyerPhone) ?></p>
          <?php endif; ?>
          <h6 class="fw-bold mt-2">Sales Receipt (Customer Copy)</h6>
          <p>#<?= htmlspecialchars($receipt['receipt_number']) ?></p>
          <p>Date: <?= date('Y-m-d H:i', strtotime($receipt['created_at'])) ?></p>
          <p>By: <?= htmlspecialchars($receipt['seller_name']) ?></p>
        </div>

        <table class="table table-sm table-borderless mt-2">
          <thead>
            <tr>
              <th>Item</th>
              <th>Lot</th>
              <th>Qty</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($saleItems as $item):
              $qty = (int)$item['quantity'];
              $unitPrice = (float)$item['sale_price'];
              $vatPercent = (float)($item['vat_percent'] ?? 0);
              $itemSubtotal = $qty * $unitPrice;
              $itemVat = $itemSubtotal * ($vatPercent / 100);
              $itemTotal = $itemSubtotal + $itemVat;
            ?>
              <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= htmlspecialchars($item['lot']) ?></td>
                <td><?= $qty ?></td>
                <td><?= number_format($itemTotal, 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="3">Subtotal</th>
              <th><?= number_format($subTotal, 2) ?></th>
            </tr>
            <tr>
              <th colspan="3">VAT</th>
              <th><?= number_format($totalVat, 2) ?></th>
            </tr>
            <tr>
              <th colspan="3">Total</th>
              <th><?= number_format($totalAmount, 2) ?></th>
            </tr>
            <tr>
              <th colspan="3">Discount
                (<?php
                  if ($totalAmount > 0):
                    echo number_format(($discountValue / $totalAmount) * 100, 2) . '%';
                  else:
                    echo '0%';
                  endif;
                  ?>)
              </th>
              <th><?= number_format($discountValue, 2) ?></th>
            </tr>
            <tr>
              <th colspan="3">Final Total</th>
              <th class="text-success"><?= number_format($finalTotal, 2) ?></th>
            </tr>
          </tfoot>
        </table>

        <div class="text-center mt-3">
          <p class="mb-1"><small>Thank you for your purchase!</small></p>
          <p class="mb-1"><small>This is a computer generated receipt</small></p>
          <p><small>Date: <?= date('Y-m-d H:i:s') ?></small></p>
        </div>

        <div class="mt-3 d-flex justify-content-center gap-2">
          <button class="btn btn-primary btn-sm" onclick="window.print()">Print</button>
          <button class="btn btn-success btn-sm" onclick="downloadPDF('posView')">Download</button>
        </div>
      </div>

    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <!-- JS Dependencies -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

  <!-- Custom JS -->
  <script>
    $(document).ready(function() {
      // Toggle between A4 and POS views
      $('#btnA4').click(() => {
        $('#a4View').show();
        $('#posView').hide();
      });
      $('#btnPOS').click(() => {
        $('#posView').show();
        $('#a4View').hide();
      });
    });

    // --- Download / Preview PDF ---
    function downloadPDF(viewId) {
      const {
        jsPDF
      } = window.jspdf;
      const element = document.getElementById(viewId);

      // Temporarily show element if hidden
      const originalDisplay = element.style.display;
      element.style.display = 'block';

      html2canvas(element, {
        scale: 2
      }).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF('p', 'pt', 'a4');
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);

        // Open PDF in a new tab for preview/download
        const pdfBlob = pdf.output('bloburl');
        window.open(pdfBlob, '_blank');

        // Restore original display
        element.style.display = originalDisplay;
      }).catch(err => {
        console.error("PDF generation error:", err);
        alert("Error generating PDF. Check console.");
      });
    }
  </script>
</body>

</html>