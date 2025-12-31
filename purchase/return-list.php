<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('view_my_purchase_receipts', '../index.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$conn = connectDB();
$userId = $_SESSION['user_id'];

// Check user role for different queries - FIXED
$roleSql = "SELECT r.role_name FROM user u 
            LEFT JOIN role r ON u.role_id = r.id 
            WHERE u.id = ?";
$roleStmt = $conn->prepare($roleSql);
$roleStmt->bind_param("i", $userId);
$roleStmt->execute();
$roleResult = $roleStmt->get_result();
$roleData = $roleResult->fetch_assoc();
$userRole = $roleData['role_name'] ?? 'user';


// Get returns with product and supplier details
if ($userRole === 'admin' || $userRole === 'manager') {
  // Admin/Manager can see all returns
  $sql = "SELECT pr.*, 
            p.name as product_name,
            p.sku as product_sku,
            s.name as supplier_name,
            u.username as returned_by_name,
            r.receipt_number
            FROM purchase_return pr
            LEFT JOIN product p ON pr.product_id = p.id
            LEFT JOIN supplier s ON pr.supplier_id = s.id
            LEFT JOIN user u ON pr.returned_by = u.id
            LEFT JOIN receipt r ON pr.receipt_id = r.id
            ORDER BY pr.created_at DESC";
  $stmt = $conn->prepare($sql);
} else {
  // Regular users can only see their own returns
  $sql = "SELECT pr.*, 
            p.name as product_name,
            p.sku as product_sku,
            s.name as supplier_name,
            u.username as returned_by_name,
            r.receipt_number
            FROM purchase_return pr
            LEFT JOIN product p ON pr.product_id = p.id
            LEFT JOIN supplier s ON pr.supplier_id = s.id
            LEFT JOIN user u ON pr.returned_by = u.id
            LEFT JOIN receipt r ON pr.receipt_id = r.id
            WHERE pr.returned_by = ?
            ORDER BY pr.created_at DESC";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $userId);
}

$stmt->execute();
$result = $stmt->get_result();
$returns = $result->fetch_all(MYSQLI_ASSOC);

// Get stats
$statsSql = "SELECT 
            COUNT(*) as total_returns,
            SUM(return_quantity) as total_quantity,
            SUM(total_refund) as total_refund_amount
            FROM purchase_return";

if ($userRole !== 'admin' && $userRole !== 'manager') {
  $statsSql .= " WHERE returned_by = ?";
  $statsStmt = $conn->prepare($statsSql);
  $statsStmt->bind_param("i", $userId);
} else {
  $statsStmt = $conn->prepare($statsSql);
}

$statsStmt->execute();
$statsResult = $statsStmt->get_result();
$stats = $statsResult->fetch_assoc();

// Close connection
$conn->close();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Purchase Returns | Sass Inventory Management System</title>
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

  <!-- Custom CSS -->
  <style>
    .btn-warning:hover {
      background-color: #d39e00 !important;
      border-color: #c99700 !important;
    }

    .btn-danger:hover {
      background-color: #bb2d3b !important;
      border-color: #b02a37 !important;
    }

    .return-badge {
      font-size: 0.8rem;
      padding: 0.25rem 0.5rem;
    }

    .stats-card {
      transition: transform 0.3s;
    }

    .stats-card:hover {
      transform: translateY(-5px);
    }

    .modal-lg {
      max-width: 800px;
    }

    .reason-badge {
      font-size: 0.75rem;
    }
  </style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

  <!-- Main -->
  <div class="app-wrapper">
    <!--Header-->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!--Sidebar-->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!-- Main -->
    <main class="app-main">
      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <!-- Page Title -->
          <h3 class="mb-0" style="font-weight: 800;">Purchase Returns</h3>

        </div>
      </div>

      <!-- Success/Fail Messages -->
      <?php if (!empty($_SESSION['success_message'])): ?>
        <div id="successMsg" class="alert alert-success mt-3 m-3"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>
      <?php if (!empty($_SESSION['error_message'])): ?>
        <div id="failMsg" class="alert alert-danger mt-3 m-3"><?= $_SESSION['error_message'] ?></div>
        <?php unset($_SESSION['error_message']); ?>
      <?php endif; ?>

      <!-- Main Content -->
      <div class="container-fluid py-4">

        <!-- Statistics Cards -->
        <div class="row mb-4">
          <?php
          // Define statistics cards array
          $statsCards = [
            [
              'title' => 'Total Returns',
              'value' => number_format($stats['total_returns'] ?? 0),
              'icon' => 'bi bi-arrow-return-left',
              'bg' => 'bg-primary',
              'link' => '#', // Add appropriate link if needed
              'permission' => 'view_returns' // Add appropriate permission
            ],
            [
              'title' => 'Total Quantity',
              'value' => number_format($stats['total_quantity'] ?? 0),
              'icon' => 'bi bi-box-seam',
              'bg' => 'bg-success',
              'link' => '#',
              'permission' => 'view_returns'
            ],
            [
              'title' => 'Total Refund',
              'value' => '$' . number_format($stats['total_refund_amount'] ?? 0, 2),
              'icon' => 'bi bi-currency-dollar',
              'bg' => 'bg-warning',
              'link' => '#',
              'permission' => 'view_returns'
            ]
          ];
          ?>

          <?php foreach ($statsCards as $card):
            // Check permission if your system has this function
            // if (!hasPermission($card['permission'])) continue;
          ?>
            <div class="col-xl-4 col-md-4 col-sm-6 mb-4">
              <div class="card text-white <?= $card['bg'] ?> o-hidden h-100 shadow-sm" style="border-radius: 10px; transition: transform 0.2s;">
                <!-- Card Body -->
                <div class="card-body d-flex align-items-center">
                  <!-- Card Icon -->
                  <div class="card-body-icon me-3">
                    <i class="<?= $card['icon'] ?> fs-2"></i>
                  </div>

                  <!-- Card Content -->
                  <div>
                    <div class="h5" style="font-weight: 600; font-size: medium;"><?= $card['title'] ?></div>
                    <div class="fw-bold fs-5"><?= $card['value'] ?></div>
                  </div>
                </div>

                <!-- Footer - Optional, remove if not needed -->
                <?php if (!empty($card['link']) && $card['link'] !== '#'): ?>
                  <a class="card-footer text-white clearfix small z-1" href="<?= $card['link'] ?>">
                    <span class="float-start">View Details</span>
                    <span class="float-end"><i class="fas fa-angle-right"></i></span>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Returns Table -->
        <div class="app-content-body mt-3">
          <div class="table-responsive container-fluid">
            <?php if (!empty($returns)): ?>
              <div class="table-responsive">
                <table id="returnsTable" class="table table-bordered table-striped table-hover align-middle">
                  <thead class="table-primary">
                    <tr>
                      <th>#</th>
                      <th>Return #</th>
                      <th>Product</th>
                      <th>Supplier</th>
                      <th>Quantity</th>
                      <th>Refund Amount</th>
                      <th>Return Date</th>
                      <th>Reason</th>
                      <th>Returned By</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($returns as $index => $return): ?>
                      <tr>
                        <td><?= $index + 1 ?></td>
                        <td>
                          <span class="badge bg-info"><?= htmlspecialchars($return['return_number']) ?></span>
                        </td>
                        <td>
                          <strong><?= htmlspecialchars($return['product_name']) ?></strong>
                          <?php if (!empty($return['product_sku'])): ?>
                            <br><small class="text-muted">SKU: <?= htmlspecialchars($return['product_sku']) ?></small>
                          <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($return['supplier_name']) ?></td>
                        <td>
                          <span class="badge bg-primary"><?= $return['return_quantity'] ?> units</span>
                        </td>
                        <td>
                          <span class="text-success fw-bold">$<?= number_format($return['total_refund'], 2) ?></span>
                        </td>
                        <td><?= date('d M Y', strtotime($return['return_date'])) ?></td>
                        <td>
                          <?php
                          $reasonColors = [
                            'defective' => 'danger',
                            'wrong_item' => 'warning',
                            'damaged' => 'dark',
                            'quality_issue' => 'secondary',
                            'excess_stock' => 'info',
                            'expired' => 'danger',
                            'customer_return' => 'primary',
                            'other' => 'secondary'
                          ];
                          $color = $reasonColors[$return['return_reason']] ?? 'secondary';
                          ?>
                          <span class="badge bg-<?= $color ?>">
                            <?= ucfirst(str_replace('_', ' ', $return['return_reason'])) ?>
                          </span>
                        </td>
                        <td><?= htmlspecialchars($return['returned_by_name']) ?></td>
                        <td>
                          <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-primary"
                              onclick="viewReturnDetails(<?= htmlspecialchars(json_encode($return)) ?>)">
                              <i class="bi bi-eye"></i> View
                            </button>
                            <?php if (can('edit_returns')): ?>
                              <button type="button" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                              </button>
                            <?php endif; ?>
                            <?php if (can('delete_returns')): ?>
                              <button type="button" class="btn btn-sm btn-danger"
                                onclick="confirmDelete(<?= $return['id'] ?>)">
                                <i class="bi bi-trash"></i>
                              </button>
                            <?php endif; ?>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                <h5>No returns found</h5>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <!-- View Return Details Modal -->
  <div class="modal fade" id="viewReturnModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="bi bi-eye"></i> Return Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="returnDetailsContent">
          <!-- Content will be loaded here by JavaScript -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="printReturnDetails()">
            <i class="bi bi-printer"></i> Print
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- JS Dependencies -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
  <script src="<?= $Project_URL ?>/js/adminlte.js"></script>

  <!-- DataTables -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- Custom JS -->
  <script>
    // Initialize DataTable
    $(document).ready(function() {
      $('#returnsTable').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false,
        language: {
          search: "Search returns:",
          lengthMenu: "Show _MENU_ returns",
          info: "Showing _START_ to _END_ of _TOTAL_ returns",
          paginate: {
            first: "First",
            last: "Last",
            next: "Next",
            previous: "Previous"
          }
        }
      });
    });

    // Auto-hide messages after 5 seconds
    setTimeout(() => {
      const msg = document.getElementById('successMsg') || document.getElementById('failMsg');
      if (msg) msg.style.display = 'none';
    }, 5000);

    setTimeout(() => {
      const msg = document.getElementById('successMsg') || document.getElementById('failMsg');
      if (msg) msg.remove();
    }, 3000);

    // View return details
    function viewReturnDetails(returnData) {
      // Format date
      const returnDate = new Date(returnData.return_date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });

      const createdDate = new Date(returnData.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });

      // Reason mapping
      const reasonMap = {
        'defective': 'Defective Product',
        'wrong_item': 'Wrong Item Received',
        'damaged': 'Damaged in Transit',
        'quality_issue': 'Quality Issues',
        'excess_stock': 'Excess Stock',
        'expired': 'Expired Product',
        'customer_return': 'Customer Returned Item',
        'other': 'Other'
      };

      // Refund method mapping
      const methodMap = {
        'cash': 'Cash',
        'bank_transfer': 'Bank Transfer',
        'credit_note': 'Credit Note',
        'adjustment': 'Stock Adjustment',
        'exchange': 'Exchange for Other Product'
      };

      // Condition mapping
      const conditionMap = {
        'new': 'New/Unopened',
        'opened': 'Opened but Unused',
        'used': 'Used',
        'damaged': 'Damaged',
        'defective': 'Defective'
      };

      const content = `
        <div class="row">
          <div class="col-md-6">
            <div class="card mb-3">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-box-seam"></i> Product Information</h6>
              </div>
              <div class="card-body">
                <table class="table table-borderless">
                  <tr>
                    <th width="40%">Product Name:</th>
                    <td>${returnData.product_name}</td>
                  </tr>
                  ${returnData.product_sku ? `
                  <tr>
                    <th>SKU:</th>
                    <td>${returnData.product_sku}</td>
                  </tr>` : ''}
                  <tr>
                    <th>Supplier:</th>
                    <td>${returnData.supplier_name}</td>
                  </tr>
                  <tr>
                    <th>Lot Number:</th>
                    <td><span class="badge bg-dark">${returnData.lot_number}</span></td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="card mb-3">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-currency-dollar"></i> Financial Details</h6>
              </div>
              <div class="card-body">
                <table class="table table-borderless">
                  <tr>
                    <th width="40%">Return Number:</th>
                    <td><span class="badge bg-info">${returnData.return_number}</span></td>
                  </tr>
                  <tr>
                    <th>Quantity Returned:</th>
                    <td><span class="badge bg-primary">${returnData.return_quantity} units</span></td>
                  </tr>
                  <tr>
                    <th>Unit Price:</th>
                    <td>$${parseFloat(returnData.unit_price).toFixed(2)}</td>
                  </tr>
                  <tr>
                    <th>Total Refund:</th>
                    <td class="text-success fw-bold">$${parseFloat(returnData.total_refund).toFixed(2)}</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6">
            <div class="card mb-3">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Return Information</h6>
              </div>
              <div class="card-body">
                <table class="table table-borderless">
                  <tr>
                    <th width="40%">Return Reason:</th>
                    <td><span class="badge bg-secondary">${reasonMap[returnData.return_reason] || returnData.return_reason}</span></td>
                  </tr>
                  <tr>
                    <th>Refund Method:</th>
                    <td>${methodMap[returnData.refund_method] || returnData.refund_method}</td>
                  </tr>
                  <tr>
                    <th>Item Condition:</th>
                    <td>${conditionMap[returnData.item_condition] || returnData.item_condition}</td>
                  </tr>
                  <tr>
                    <th>Return Date:</th>
                    <td>${returnDate}</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="card mb-3">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-person"></i> User Information</h6>
              </div>
              <div class="card-body">
                <table class="table table-borderless">
                  <tr>
                    <th width="40%">Returned By:</th>
                    <td>${returnData.returned_by_name}</td>
                  </tr>
                  ${returnData.receipt_number ? `
                  <tr>
                    <th>Original Receipt:</th>
                    <td><span class="badge bg-info">${returnData.receipt_number}</span></td>
                  </tr>` : ''}
                  <tr>
                    <th>Purchase ID:</th>
                    <td>${returnData.purchase_id}</td>
                  </tr>
                  <tr>
                    <th>Record Created:</th>
                    <td>${createdDate}</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </div>
        
        ${returnData.notes ? `
        <div class="card mb-3">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-chat-text"></i> Notes</h6>
          </div>
          <div class="card-body">
            <p class="mb-0">${returnData.notes}</p>
          </div>
        </div>` : ''}
        
        <div class="alert alert-info">
          <i class="bi bi-info-circle"></i>
          <strong>Note:</strong> This return has been recorded in the system and stock levels have been updated accordingly.
        </div>
      `;

      document.getElementById('returnDetailsContent').innerHTML = content;
      new bootstrap.Modal(document.getElementById('viewReturnModal')).show();
    }

    // Print return details
    function printReturnDetails() {
      const modalContent = document.getElementById('returnDetailsContent').innerHTML;
      const printWindow = window.open('', '_blank');
      printWindow.document.write(`
        <html>
          <head>
            <title>Return Details - Print</title>
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; }
              .print-header { text-align: center; margin-bottom: 30px; }
              .section { margin-bottom: 20px; }
              table { width: 100%; border-collapse: collapse; }
              th, td { padding: 8px; border: 1px solid #ddd; }
              th { background-color: #f5f5f5; }
              .badge { padding: 3px 8px; border-radius: 3px; font-size: 12px; }
              .total { font-weight: bold; color: #28a745; }
            </style>
          </head>
          <body>
            <div class="print-header">
              <h2>Return Details</h2>
              <p>Printed on: ${new Date().toLocaleString()}</p>
            </div>
            ${modalContent}
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    }

    // Print all returns
    function printReturns() {
      window.open('print_returns.php', '_blank');
    }

    // Confirm delete
    function confirmDelete(returnId) {
      if (confirm('Are you sure you want to delete this return? This action cannot be undone.')) {
        window.location.href = `delete_return.php?id=${returnId}`;
      }
    }
  </script>
</body>

</html>