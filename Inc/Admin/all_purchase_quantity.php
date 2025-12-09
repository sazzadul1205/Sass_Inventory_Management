<?php
// Fetch and sum quantity grouped by date
$sql = "SELECT purchase_date, SUM(quantity) AS total_quantity 
        FROM purchase 
        GROUP BY purchase_date 
        ORDER BY purchase_date ASC";

$result = $conn->query($sql);

// Prepare JS array
$jsArray = [];
$today = date('Y-m-d');

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $purchaseDate = $row['purchase_date'];

    // Skip invalid dates (like '0000-00-00') or future dates
    if (!$purchaseDate || strtotime($purchaseDate) === false || $purchaseDate > $today) {
      continue;
    }

    $jsArray[] = [
      "x" => $purchaseDate,
      "y" => (int)$row['total_quantity']
    ];
  }
}

// Print JavaScript
echo "<script>\n";
echo "const rawData = " . json_encode($jsArray) . ";\n";
echo "</script>";

// $conn->close();
?>

<!-- Purchase Quantity Chart -->
<div id="purchase-chart-component" class="p-3 bg-light rounded">
  <!-- Header with title and buttons -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-2 mb-md-0" style="font-weight: 800; font-size: 1.5rem;">Total Purchase Quantity</h2>
    <div class="btn-group" role="group" aria-label="Date range">
      <button type="button" class="btn btn-success active" data-days="7">7 Days</button>
      <button type="button" class="btn btn-success" data-days="30">1 Month</button>
      <button type="button" class="btn btn-success" data-days="90">3 Months</button>
      <button type="button" class="btn btn-success" data-days="180">6 Months</button>
      <button type="button" class="btn btn-success" data-days="365">1 Year</button>
      <button type="button" class="btn btn-success" data-days="730">2 Years</button>
      <button type="button" class="btn btn-success" data-days="1095">3 Years</button>
    </div>
  </div>

  <!-- Chart Card -->
  <div class="bg-white p-3 rounded shadow-sm">
    <div id="chart"></div>
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

    let fullData = rawData;

    const options = {
      chart: {
        type: 'line',
        height: 350
      },
      series: [{
        name: 'Quantity',
        data: fillMissingDates(fullData, 7)
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

    const chart = new ApexCharts(document.querySelector("#purchase-chart-component #chart"), options);
    chart.render();

    // Handle button clicks with active state
    const buttons = document.querySelectorAll('#purchase-chart-component .btn-group .btn');
    buttons.forEach(btn => {
      btn.addEventListener('click', () => {
        // Remove active class from all buttons
        buttons.forEach(b => b.classList.remove('active'));
        // Add active to clicked
        btn.classList.add('active');
        // Update chart
        const days = parseInt(btn.getAttribute('data-days'));
        const filtered = fillMissingDates(fullData, days);
        chart.updateSeries([{
          name: 'Quantity',
          data: filtered
        }]);
      });
    });
  })();
</script>