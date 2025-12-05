<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$conn = connectDB();

// Fetch sales data from the view
$sql = "SELECT * FROM view_sales_report";
$result = $conn->query($sql);
$sales = $result->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Sales Report | Sass Inventory</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="print" onload="this.media='all'" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <?php include_once '../Inc/Navbar.php'; ?>
    <?php include_once '../Inc/Sidebar.php'; ?>

    <main class="app-main">

      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <h3 class="mb-0" style="font-weight: 800;">Sales Report</h3>
        </div>
      </div>

      <!-- Sales Table -->
      <div class="app-content-body mt-3">
        <div class="table-responsive container-fluid">
          <?php if ($result->num_rows > 0): ?>
            <table id="salesTable" class="table table-bordered table-striped">
              <thead class="table-dark">
                <tr>
                  <th>#</th>
                  <th>Product</th>
                  <th>Supplier</th>
                  <th>Qty Sold</th>
                  <th>Unit Price</th>
                  <th>Total Revenue</th>
                  <th>Sale Date</th>
                  <th>Receipt No.</th>
                  <th>Sold By</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($sales as $index => $row): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                    <td><?= $row['qty_sold'] ?></td>
                    <td><?= number_format($row['unit_price'], 2) ?></td>
                    <td><?= number_format($row['total_revenue'], 2) ?></td>
                    <td><?= $row['sale_date'] ?></td>
                    <td><?= htmlspecialchars($row['receipt_number']) ?></td>
                    <td><?= htmlspecialchars($row['sold_by']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              <h5>No sales data found</h5>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Sales Chart Section -->
      <div class="container-fluid mt-5 px-3">
        <div class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0">Sales Trend Overview</h5>
            <button class="btn btn-sm btn-outline-primary mt-2 mt-md-0" data-bs-toggle="collapse" data-bs-target="#salesGraphBox">
              Toggle Graph
            </button>
          </div>

          <div id="salesGraphBox" class="collapse show">
            <div class="card-body">

              <!-- Time Range Buttons -->
              <div class="btn-toolbar mb-3 flex-wrap" role="toolbar" aria-label="Time range buttons">
                <div class="btn-group me-2 mb-2" role="group">
                  <button type="button" class="btn btn-outline-secondary time-range" data-range="1h">1 Hour</button>
                  <button type="button" class="btn btn-outline-secondary time-range" data-range="6h">6 Hours</button>
                  <button type="button" class="btn btn-outline-secondary time-range" data-range="12h">12 Hours</button>
                  <button type="button" class="btn btn-outline-secondary time-range" data-range="24h">24 Hours</button>
                </div>
                <div class="btn-group me-2 mb-2" role="group">
                  <button type="button" class="btn btn-outline-secondary time-range" data-range="3d">3 Days</button>
                  <button type="button" class="btn btn-outline-secondary time-range" data-range="7d">7 Days</button>
                  <button type="button" class="btn btn-outline-secondary time-range" data-range="30d">30 Days</button>
                </div>
                <div class="btn-group mb-2" role="group">
                  <button type="button" class="btn btn-outline-secondary time-range" data-range="90d">90 Days</button>
                  <button type="button" class="btn btn-outline-secondary time-range" data-range="365d">1 Year</button>
                </div>
              </div>

              <!-- Show Selected Range -->
              <p class="mt-2 mb-3">Selected Range: <span id="selectedRange">7 Days</span></p>

              <!-- Chart Container -->
              <div id="salesChartContainer" style="width: calc(100% - 60px); height: 450px; margin: 0 auto;"></div>

            </div>
          </div>
        </div>
      </div>

    </main>
  </div>

  <!-- JS Dependencies -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
  <script src="<?= $Project_URL ?>/js/adminlte.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- ApexCharts -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <script>
    $(document).ready(function() {
      // Initialize DataTable
      $('#salesTable').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false
      });

      // Prepare sales data for JS
      const salesData = <?php echo json_encode(array_map(function ($s) {
                          return [
                            'date' => $s['sale_date'],
                            'total' => (float)$s['total_revenue'] // Use correct total_revenue
                          ];
                        }, $sales)); ?>;

      let chart; // ApexCharts instance

      // Function to update chart based on selected range
      function updateChart(range) {
        const now = new Date();
        let startDate;

        // Determine start date for each range
        switch (range) {
          case '1h':
            startDate = new Date(now.getTime() - 1 * 3600000);
            break;
          case '6h':
            startDate = new Date(now.getTime() - 6 * 3600000);
            break;
          case '12h':
            startDate = new Date(now.getTime() - 12 * 3600000);
            break;
          case '24h':
            startDate = new Date(now.getTime() - 24 * 3600000);
            break;
          case '3d':
            startDate = new Date(now.getTime() - 3 * 24 * 3600000);
            break;
          case '7d':
            startDate = new Date(now.getTime() - 7 * 24 * 3600000);
            break;
          case '30d':
            startDate = new Date(now.getTime() - 30 * 24 * 3600000);
            break;
          case '90d':
            startDate = new Date(now.getTime() - 90 * 24 * 3600000);
            break;
          case '365d':
            startDate = new Date(now.getTime() - 365 * 24 * 3600000);
            break;
          default:
            startDate = new Date(salesData[0].date);
        }

        // Aggregate sales per hour or per day
        const intervalMs = ['1h', '6h', '12h', '24h'].includes(range) ? 3600000 : 24 * 3600000;
        const timeline = [];
        for (let t = startDate.getTime(); t <= now.getTime(); t += intervalMs) timeline.push(t);

        // Map sales onto timeline
        const seriesData = timeline.map(ts => {
          const total = salesData
            .filter(s => {
              const sTime = new Date(s.date).getTime();
              return sTime >= ts && sTime < ts + intervalMs;
            })
            .reduce((sum, s) => sum + s.total, 0);
          return total;
        });

        const categories = timeline.map(ts => new Date(ts).toISOString());

        // Destroy previous chart if exists
        if (chart) chart.destroy();

        // Create new ApexCharts chart
        chart = new ApexCharts(document.querySelector("#salesChartContainer"), {
          chart: {
            type: 'line',
            height: 450,
            zoom: {
              enabled: true
            }
          },
          series: [{
            name: 'Revenue',
            data: seriesData
          }],
          xaxis: {
            categories,
            type: 'datetime'
          },
          stroke: {
            curve: 'smooth'
          },
          yaxis: {
            title: {
              text: 'Revenue'
            },
            min: 0,
            labels: {
              formatter: function(value) {
                return value.toLocaleString('en-US', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                });
              }
            }
          },
          tooltip: {
            y: {
              formatter: function(value) {
                return value.toLocaleString('en-US', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                });
              }
            },
            x: {
              format: 'dd MMM yyyy HH:mm'
            }
          }
        });

        chart.render();
      }

      // Initialize chart with default 7 days
      updateChart('7d');

      // Handle time-range button clicks
      $(document).on('click', '.time-range', function(e) {
        e.preventDefault();
        const range = $(this).data('range');

        // Update chart
        updateChart(range);

        // Highlight selected button
        $(".time-range").removeClass("active btn-primary").addClass("btn-outline-secondary");
        $(this).addClass("active btn-primary").removeClass("btn-outline-secondary");

        // Update selected range text
        $("#selectedRange").text($(this).text());

        // Scroll to chart
        $('html, body').animate({ 
          scrollTop: $("#salesGraphBox").offset().top - 100
        }, 400);
      });
    });
  </script>

</body>

</html>