<?php


// Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
  die("User not logged in.");
}

$userId = $_SESSION['user_id'];

// Fetch and sum quantity grouped by sale_date for current user
$sql = "SELECT sale_date, SUM(quantity) AS total_quantity 
        FROM sale 
        WHERE sold_by = ?
        GROUP BY sale_date 
        ORDER BY sale_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Prepare JS array for Sale chart
$saleDataArray = [];
$today = date('Y-m-d');

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $saleDate = $row['sale_date'];

    // Skip invalid dates (like '0000-00-00') or future dates
    if (!$saleDate || strtotime($saleDate) === false || $saleDate > $today) {
      continue;
    }

    $saleDataArray[] = [
      "x" => $saleDate,
      "y" => (int)$row['total_quantity']
    ];
  }
}

// Print JavaScript
echo "<script>\n";
echo "const saleRawData = " . json_encode($saleDataArray) . ";\n";
echo "</script>";

$stmt->close();
?>


<!-- Sale Quantity Chart -->
<div id="sale-chart-component" class="p-3 bg-light rounded">
  <!-- Header with title and buttons -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-2 mb-md-0" style="font-weight: 800; font-size: 1.5rem;">Total Sale Quantity</h2>
    <div class="btn-group" role="group" aria-label="Sale date range">
      <button type="button" class="btn btn-primary active" data-days="7">7 Days</button>
      <button type="button" class="btn btn-primary" data-days="30">1 Month</button>
      <button type="button" class="btn btn-primary" data-days="90">3 Months</button>
      <button type="button" class="btn btn-primary" data-days="180">6 Months</button>
      <button type="button" class="btn btn-primary" data-days="365">1 Year</button>
      <button type="button" class="btn btn-primary" data-days="730">2 Years</button>
      <button type="button" class="btn btn-primary" data-days="1095">3 Years</button>
    </div>
  </div>

  <!-- Chart Card -->
  <div class="bg-white p-3 rounded shadow-sm">
    <div id="saleChart"></div>
  </div>
</div>

<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  (function() {

    function fillMissingDates(data, days) {
      const result = [];
      const end = new Date();
      const start = new Date(new Date() - days * 24 * 60 * 60 * 1000);
      for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        const dateStr = d.toISOString().split('T')[0];
        const found = data.find(item => item.x === dateStr);
        result.push({
          x: dateStr,
          y: found ? found.y : 0
        });
      }
      return result;
    }

    let fullSaleData = saleRawData;

    const saleChartOptions = {
      chart: {
        type: 'line',
        height: 350
      },
      series: [{
        name: 'Sale Quantity',
        data: fillMissingDates(fullSaleData, 7)
      }],
      xaxis: {
        type: 'datetime'
      },
      yaxis: {
        title: {
          text: 'Quantity'
        }
      },
      stroke: {
        curve: 'smooth'
      },
      tooltip: {
        x: {
          format: 'dd MMM yyyy'
        }
      }
    };

    const saleChart = new ApexCharts(document.querySelector("#saleChart"), saleChartOptions);
    saleChart.render();

    // Handle Sale buttons with active state
    const saleButtons = document.querySelectorAll('#sale-chart-component .btn-group .btn');
    saleButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        // Remove active class from all buttons
        saleButtons.forEach(b => b.classList.remove('active'));
        // Add active to clicked
        btn.classList.add('active');
        // Update chart
        const days = parseInt(btn.getAttribute('data-days'));
        const filtered = fillMissingDates(fullSaleData, days);
        saleChart.updateSeries([{
          name: 'Sale Quantity',
          data: filtered
        }]);
      });
    });
  })();
</script>