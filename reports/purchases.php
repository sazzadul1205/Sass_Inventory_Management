<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('view_purchase_report', '../index.php');

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
  <title>Purchase Report | Sass Inventory</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Favicon -->
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">

  <!-- Source Sans 3 Font -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="print" onload="this.media='all'" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />

  <!-- AdminLTE Styles -->
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />

  <!-- DataTables Bootstrap 5 CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <style>
    .text-truncate {
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
      cursor: pointer;
    }

    /* Select2 - match Bootstrap height & fix jump */
    .select2-container--default .select2-selection--single {
      background-color: #fff;
      color: #000;
      border: 1px solid #ced4da;
      border-radius: 0.25rem;
      height: calc(1.5em + 0.75rem + 2px);
      line-height: 1.5;
      padding: 0.375rem 0.75rem;
      box-sizing: border-box;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #000;
      line-height: 1.5;
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

    /* Toolbar container for better visual separation */
    .table-toolbar {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-start;
      gap: 10px;
      margin-bottom: 1rem;
      align-items: center;
    }

    .table-toolbar .form-control,
    .table-toolbar .form-select {
      min-width: 200px;
    }

    /* Spin animation */
    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    /* Apply spin when .spinning class is added */
    .reset-icon.spinning {
      animation: spin 0.8s linear infinite;
    }
  </style>
</head>

<?php
$conn = connectDB();

// Fetch purchase data from view_purchase_report
$sql = "SELECT * FROM view_purchase_report";
$result = $conn->query($sql);
$purchases = $result->fetch_all(MYSQLI_ASSOC);
?>

<!-- Body -->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">

    <!-- Navbar -->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!-- Sidebar -->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!-- Main -->
    <main class="app-main">

      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <!-- Page Title -->
          <h3 class="mb-0" style="font-weight: 800;">Purchase Report</h3>
        </div>
      </div>

      <!-- Purchase Table Toolbar -->
      <div class="table-toolbar p-3 mb-3 rounded shadow-sm bg-white d-flex flex-wrap align-items-end gap-3">

        <!-- Product Search -->
        <div class="d-flex flex-column flex-grow-1" style="min-width: 200px;">
          <label for="productSearch" class="form-label fw-semibold mb-1">Search Product</label>
          <input type="text" id="productSearch" class="form-control" placeholder="Type to search...">
        </div>

        <!-- Supplier Filter -->
        <div class="d-flex flex-column" style="min-width: 200px;">
          <label for="supplierFilter" class="form-label fw-semibold mb-1">Filter by Supplier</label>
          <select id="supplierFilter" class="form-select">
            <option value="">All Suppliers</option>
            <?php
            $supResult = $conn->query("
              SELECT DISTINCT s.name 
              FROM supplier s
              JOIN product p ON p.supplier_id = s.id
              ORDER BY s.name ASC
            ");
            while ($sup = $supResult->fetch_assoc()) {
              echo "<option value=\"{$sup['name']}\">{$sup['name']}</option>";
            }
            ?>
          </select>
        </div>

        <!-- Purchased By Filter -->
        <div class="d-flex flex-column" style="min-width: 200px;">
          <label for="purchasedFilter" class="form-label fw-semibold mb-1">Filter by Purchased By</label>
          <select id="purchasedFilter" class="form-select">
            <option value="">All Users</option>
            <?php
            $userResult = $conn->query("
              SELECT DISTINCT username 
              FROM user
              ORDER BY username ASC
            ");
            while ($usr = $userResult->fetch_assoc()) {
              echo "<option value=\"{$usr['username']}\">{$usr['username']}</option>";
            }
            ?>
          </select>
        </div>

        <!-- Reset Button -->
        <div class="d-flex flex-column align-items-start" style="min-width: 120px;">
          <label class="form-label mb-1">&nbsp;</label>
          <button id="resetFilters" class="btn btn-secondary w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-arrow-counterclockwise reset-icon"></i> Reset
          </button>
        </div>

      </div>

      <!-- Purchase Table -->
      <div class="app-content-body mt-3">
        <div class="table-responsive container-fluid">
          <?php if ($result->num_rows > 0): ?>
            <table id="purchaseTable" class="table table-bordered table-striped">
              <thead class="table-dark">
                <tr>
                  <th>#</th>
                  <th>Product</th>
                  <th>Supplier</th>
                  <th>Qty Purchased</th>
                  <th>Unit Cost</th>
                  <th>Total Cost</th>
                  <th>Purchase Date</th>
                  <th>Invoice No.</th>
                  <th>Purchased By</th>
                </tr>
              </thead>

              <tbody>
                <?php foreach ($purchases as $index => $row): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                    <td><?= $row['qty_purchased'] ?></td>
                    <td><?= number_format($row['unit_price'], 2) ?></td>
                    <td><?= number_format($row['total_cost'], 2) ?></td>
                    <td><?= $row['purchase_date'] ?></td>
                    <td class="text-truncate" style="max-width:120px;"
                      data-bs-toggle="tooltip" data-bs-placement="top"
                      title="<?= htmlspecialchars($row['receipt_number']) ?>">
                      <?= htmlspecialchars($row['receipt_number']) ?>
                    </td>
                    <td><?= htmlspecialchars($row['purchased_by']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              <h5>No purchase data found</h5>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Purchase Chart Section -->
      <div class="container-fluid mt-5 px-3">
        <div class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0">Purchase Trend Overview</h5>
            <button class="btn btn-sm btn-outline-primary mt-2 mt-md-0" data-bs-toggle="collapse" data-bs-target="#purchaseGraphBox">
              Toggle Graph
            </button>
          </div>

          <div id="purchaseGraphBox" class="collapse show">
            <div class="card-body">

              <!-- Time Range Buttons -->
              <div class="btn-toolbar mb-3 flex-wrap">
                <div class="btn-group me-2 mb-2">
                  <button class="btn btn-outline-secondary time-range" data-range="1h">1 Hour</button>
                  <button class="btn btn-outline-secondary time-range" data-range="6h">6 Hours</button>
                  <button class="btn btn-outline-secondary time-range" data-range="12h">12 Hours</button>
                  <button class="btn btn-outline-secondary time-range" data-range="24h">24 Hours</button>
                </div>

                <div class="btn-group me-2 mb-2">
                  <button class="btn btn-outline-secondary time-range" data-range="3d">3 Days</button>
                  <button class="btn btn-outline-secondary time-range" data-range="7d">7 Days</button>
                  <button class="btn btn-outline-secondary time-range" data-range="30d">30 Days</button>
                </div>

                <div class="btn-group mb-2">
                  <button class="btn btn-outline-secondary time-range" data-range="90d">90 Days</button>
                  <button class="btn btn-outline-secondary time-range" data-range="365d">1 Year</button>
                </div>
              </div>

              <!-- Selected Range -->
              <p class="mt-2 mb-3">Selected Range: <span id="selectedRange">7 Days</span></p>

              <!-- Chart -->
              <div id="purchaseChartContainer" style="width: calc(100% - 60px); height: 450px; margin: 0 auto;"></div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <!-- jQuery Library -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- Popper.js for Bootstrap tooltips & popovers -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

  <!-- AdminLTE Dashboard JS -->
  <script src="<?= $Project_URL ?>/js/adminlte.js"></script>

  <!-- DataTables Core JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <!-- DataTables Bootstrap 5 Integration -->
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

  <!-- ApexCharts Library -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <!-- Select2 Library for enhanced select dropdowns -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- Tooltip -->
  <script>
    $(document).ready(function() {
      // Initialize DataTable
      var table = $('#purchaseTable').DataTable({
        paging: true,
        pageLength: 5,
        lengthChange: true,
        ordering: true,
        order: [],
        lengthMenu: [
          [5, 10, 25, 50, -1],
          [5, 10, 25, 50, "All"]
        ],
        info: true,
        autoWidth: false,
        dom: '<"top-pagination d-flex justify-content-between mb-2"lp>rt<"bottom-pagination"ip>',
        language: {
          search: "" // Remove default search
        }
      });

      // Reset all filters and search with animation
      $('#resetFilters').on('click', function() {
        var icon = $(this).find('.reset-icon');
        icon.addClass('spinning');

        $('#productSearch').val('');
        $('#supplierFilter').val('').trigger('change');
        $('#purchasedFilter').val('').trigger('change');
        table.search('').columns().search('').draw();

        setTimeout(function() {
          icon.removeClass('spinning');
        }, 800);
      });

      // Initialize Select2 for Supplier and Purchased By
      $('#supplierFilter, #purchasedFilter').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%',
        dropdownParent: $('body'),
        matcher: function(params, data) {
          if (data.id === "") return data;
          if ($.trim(params.term) === '') return data;
          if (typeof data.text === 'undefined') return null;
          if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) return data;
          return null;
        }
      });

      // Filter table by Supplier (column 2)
      $('#supplierFilter').on('change', function() {
        var val = $(this).val();
        table.column(2).search(val ? val : '').draw();
      });

      // Filter table by Purchased By (column 8)
      $('#purchasedFilter').on('change', function() {
        var val = $(this).val();
        table.column(8).search(val ? val : '').draw();
      });

      // Search products by name (column 1)
      $('#productSearch').on('keyup', function() {
        table.column(1).search(this.value).draw();
      });

      // Tooltips
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      });
    });
  </script>

  <!-- Chart -->
  <script>
    $(document).ready(function() {


      // Prepare chart data
      const purchaseData = <?php echo json_encode(array_map(function ($p) {
                              return [
                                'date' => $p['purchase_date'],
                                'total' => (float)$p['total_cost']
                              ];
                            }, $purchases)); ?>;

      let chart;

      function updateChart(range) {
        const now = new Date();
        let start;

        switch (range) {
          case '1h':
            start = new Date(now - 1 * 3600000);
            break;
          case '6h':
            start = new Date(now - 6 * 3600000);
            break;
          case '12h':
            start = new Date(now - 12 * 3600000);
            break;
          case '24h':
            start = new Date(now - 24 * 3600000);
            break;
          case '3d':
            start = new Date(now - 3 * 24 * 3600000);
            break;
          case '7d':
            start = new Date(now - 7 * 24 * 3600000);
            break;
          case '30d':
            start = new Date(now - 30 * 24 * 3600000);
            break;
          case '90d':
            start = new Date(now - 90 * 24 * 3600000);
            break;
          case '365d':
            start = new Date(now - 365 * 24 * 3600000);
            break;
          default:
            start = new Date(purchaseData[0].date);
        }

        const interval = ['1h', '6h', '12h', '24h'].includes(range) ? 3600000 : 24 * 3600000;

        const timeline = [];
        for (let t = start; t <= now; t = new Date(t.getTime() + interval)) {
          timeline.push(t.getTime());
        }

        const values = timeline.map(ts =>
          purchaseData
          .filter(p => {
            const t = new Date(p.date).getTime();
            return t >= ts && t < ts + interval;
          })
          .reduce((sum, p) => sum + p.total, 0)
        );

        const labels = timeline.map(ts => new Date(ts).toISOString());

        if (chart) chart.destroy();

        chart = new ApexCharts(document.querySelector("#purchaseChartContainer"), {
          chart: {
            type: "line",
            height: 450,
            zoom: {
              enabled: true
            }
          },
          series: [{
            name: "Purchase Cost",
            data: values
          }],
          xaxis: {
            type: "datetime",
            categories: labels
          },
          stroke: {
            curve: "smooth"
          },
          yaxis: {
            title: {
              text: "Cost"
            }
          }
        });

        chart.render();
      }

      updateChart('7d');

      $(".time-range").on("click", function() {
        let range = $(this).data("range");
        updateChart(range);

        $(".time-range").removeClass("active btn-primary")
          .addClass("btn-outline-secondary");
        $(this).addClass("active btn-primary")
          .removeClass("btn-outline-secondary");

        $("#selectedRange").text($(this).text());
      });

      // Initialize tooltip
      $(document).ready(function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })
      });
    });
  </script>

</body>

</html>