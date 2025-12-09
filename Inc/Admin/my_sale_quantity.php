<?php

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
  die("User not logged in.");
}

$userId = $_SESSION['user_id'];


// Fetch personalized sales for current user
$sql = "SELECT sale_date, SUM(quantity) AS total_quantity 
        FROM sale 
        WHERE sold_by = ?
        GROUP BY sale_date 
        ORDER BY sale_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();

$personalSaleData = [];
$today = date('Y-m-d');

while ($row = $result->fetch_assoc()) {
  $saleDate = $row['sale_date'];
  if (!$saleDate || strtotime($saleDate) === false || $saleDate > $today) continue;
  $personalSaleData[] = [
    "x" => $saleDate,
    "y" => (int)$row['total_quantity']
  ];
}

echo "<script>\n";
echo "const personalSaleData = " . json_encode($personalSaleData) . ";\n";
echo "</script>";

$stmt->close();
// $conn->close();
?>

<!-- Personalized Sale Chart -->
<div id="personal-sale-chart" class="p-3 bg-light rounded mt-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-2 mb-md-0" style="font-weight: 800; font-size: 1.5rem;">My Total Sale Quantity</h2>
    <div class="btn-group" role="group">
      <button type="button" class="btn btn-info active" data-days="7">7 Days</button>
      <button type="button" class="btn btn-info" data-days="30">1 Month</button>
      <button type="button" class="btn btn-info" data-days="90">3 Months</button>
      <button type="button" class="btn btn-info" data-days="180">6 Months</button>
      <button type="button" class="btn btn-info" data-days="365">1 Year</button>
      <button type="button" class="btn btn-info" data-days="730">2 Years</button>
      <button type="button" class="btn btn-info" data-days="1095">3 Years</button>
    </div>
  </div>

  <div class="bg-white p-3 rounded shadow-sm">
    <div id="personalSaleChart"></div>
  </div>
</div>

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

    let fullPersonalSaleData = personalSaleData;

    const personalSaleChartOptions = {
      chart: {
        type: 'line',
        height: 350
      },
      series: [{
        name: 'My Sale Quantity',
        data: fillMissingDates(fullPersonalSaleData, 7)
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

    const personalSaleChart = new ApexCharts(
      document.querySelector("#personalSaleChart"),
      personalSaleChartOptions
    );
    personalSaleChart.render();

    // Handle buttons for this chart only
    const buttons = document.querySelectorAll('#personal-sale-chart .btn-group .btn');
    buttons.forEach(btn => {
      btn.addEventListener('click', () => {
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const days = parseInt(btn.getAttribute('data-days'));
        personalSaleChart.updateSeries([{
          name: 'My Sale Quantity',
          data: fillMissingDates(fullPersonalSaleData, days)
        }]);
      });
    });
  })();
</script>