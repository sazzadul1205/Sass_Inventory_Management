<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';

// --- Check if user is logged in ---
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Purchase Receipt | Sass Inventory</title>
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
// --- Get receipt ID from query string ---
$receiptId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($receiptId <= 0) die("Invalid receipt ID.");

// --- Connect to database ---
$conn = connectDB();

// --- Get Purchase Receipt Info ---
$stmt = $conn->prepare("
    SELECT r.*, u.username AS purchaser_name
    FROM receipt r
    LEFT JOIN user u ON r.created_by = u.id
    WHERE r.id = ? AND r.type = 'purchase'
");
$stmt->bind_param("i", $receiptId);
$stmt->execute();
$receipt = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$receipt) die("Purchase receipt not found.");

// --- Get Purchase Items ---
$stmt = $conn->prepare("
    SELECT p.*, pr.name AS product_name, s.name AS supplier_name
    FROM purchase p
    LEFT JOIN product pr ON p.product_id = pr.id
    LEFT JOIN supplier s ON p.supplier_id = s.id
    WHERE p.receipt_id = ?
");
$stmt->bind_param("i", $receiptId);
$stmt->execute();
$purchaseItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- Calculate totals ---
$totalQty = 0;
$totalAmount = 0; 
foreach ($purchaseItems as $item) {
  $totalQty += $item['quantity'];
  $totalAmount += $item['purchase_price'];
}

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
          <h3 class="mb-0" style="font-weight: 800;">Recept</h3>

          <!-- View Selector Buttons -->
          <div class="d-flex gap-2">
            <button id="btnA4" class="btn btn-primary" style="width: 100px;">A4 View</button>
            <button id="btnPOS" class="btn btn-secondary" style="width: 100px;">POS View</button>
          </div>
        </div>
      </div>

      <!-- ========================= A4 View ========================= -->
      <div id="a4View" class="card p-4">

        <!-- Top Header Row -->
        <div class="row mb-4 align-items-center">
          <div class="col-md-6">
            <h2 class="fw-bold mb-1"><?= strtoupper($Project_Name ?? 'Sass Inventory') ?></h2>
            <p class="mb-1">Your Trusted Inventory Solution</p>
            <p class="mb-1">Address: 123, Main Street, City</p>
            <p class="mb-1">Phone: +880123456789 | Email: info@sassinventory.com</p>
          </div>
          <div class="col-md-6 text-md-end">
            <h3 class="fw-bold mb-1">Purchase Receipt</h3>
            <p class="mb-0">(Purchaser Copy)</p>
          </div>
        </div>

        <hr>

        <!-- Receipt Info -->
        <div class="row mb-3">
          <div class="col-md-6"><strong>Receipt #:</strong> <?= htmlspecialchars($receipt['receipt_number']) ?></div>
          <div class="col-md-6"><strong>Date:</strong> <?= date('Y-m-d H:i', strtotime($receipt['created_at'])) ?></div>
          <div class="col-md-6"><strong>Purchased By:</strong> <?= htmlspecialchars($receipt['purchaser_name']) ?></div>
        </div>

        <!-- Purchase Table -->
        <table class="table table-bordered table-striped mt-3">
          <thead class="table-dark">
            <tr>
              <th>Product</th>
              <th>Supplier</th>
              <th>Qty</th>
              <th>Unit Price</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($purchaseItems as $item):
              $unitPrice = $item['purchase_price'] / $item['quantity'];
            ?>
              <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= htmlspecialchars($item['supplier_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($unitPrice, 2) ?></td>
                <td><?= number_format($item['purchase_price'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot class="table-light">
            <tr>
              <th colspan="2">Total</th>
              <th><?= $totalQty ?></th>
              <th></th>
              <th><?= number_format($totalAmount, 2) ?></th>
            </tr>
          </tfoot>
        </table>

        <!-- Signature Block -->
        <div class="signature">
          <div>________________<br>Purchaser</div>
          <div>________________<br>Supplier</div>
          <div>________________<br>Guarantor</div>
        </div>

        <!-- Print & Download Buttons -->
        <div class="mt-3 d-flex justify-content-center gap-2">
          <button class="btn btn-primary btn-sm" onclick="window.print()">Print</button>
          <button class="btn btn-success btn-sm" onclick="downloadPDF('a4View')">Download</button>
        </div>
      </div>

      <!-- ========================= POS View ========================= -->
      <div id="posView" class="card p-3">

        <!-- Top Header -->
        <div class="text-center mb-3">
          <h5 class="fw-bold mb-1"><?= strtoupper($Project_Name ?? 'Sass Inventory') ?></h5>
          <p class="mb-0">Your Trusted Inventory Solution</p>
          <p class="mb-0">Address: 123, Main Street, City</p>
          <p class="mb-0">Phone: +880123456789 | Email: info@sassinventory.com</p>
          <h6 class="fw-bold mt-2">Purchase Receipt (Purchaser Copy)</h6>
        </div>

        <!-- Receipt Info -->
        <p><strong>#<?= htmlspecialchars($receipt['receipt_number']) ?></strong></p>
        <p>Date: <?= date('Y-m-d H:i', strtotime($receipt['created_at'])) ?></p>
        <p>By: <?= htmlspecialchars($receipt['purchaser_name']) ?></p>

        <!-- Purchase Table -->
        <table class="table table-sm table-borderless mt-2">
          <thead>
            <tr>
              <th>Item</th>
              <th>Qty</th>
              <th>Unit</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($purchaseItems as $item):
              $unitPrice = $item['purchase_price'] / $item['quantity'];
            ?>
              <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($unitPrice, 2) ?></td>
                <td><?= number_format($item['purchase_price'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th>Total</th>
              <th><?= $totalQty ?></th>
              <th></th>
              <th><?= number_format($totalAmount, 2) ?></th>
            </tr>
          </tfoot>
        </table>

        <!-- Signature Block -->
        <div class="signature d-flex justify-content-between text-center">
          <div>__________<br>Purchaser</div>
          <div>__________<br>Supplier</div>
          <div>__________<br>Guarantor</div>
        </div>

        <!-- Print & Download Buttons -->
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