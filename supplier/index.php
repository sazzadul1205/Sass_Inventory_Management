<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Suppliers | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">

  <!-- Font -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    media="print" onload="this.media='all'">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

  <!-- AdminLTE -->
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css">

  <!-- DataTable -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>

<?php
$conn = connectDB();
$result = $conn->query("SELECT * FROM supplier ORDER BY id ASC");
?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

  <div class="app-wrapper">

    <?php include_once '../Inc/Navbar.php'; ?>
    <?php include_once '../Inc/Sidebar.php'; ?>

    <main class="app-main">

      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center">
          <h3 class="mb-0">All Suppliers</h3>
          <a href="add.php" class="btn btn-sm btn-primary px-3 py-2">
            <i class="bi bi-plus me-1"></i> Add New Supplier
          </a>
        </div>
      </div>

      <div class="app-content-body mt-3">
        <div class="container-fluid">

          <?php if ($result->num_rows == 0): ?>

            <!-- Show empty message ONLY (no table) -->
            <div class="text-center text-muted py-5">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              <h5>No suppliers found</h5>
            </div>

          <?php else: ?>

            <!-- Show table ONLY when data exists -->
            <div class="table-responsive">
              <table id="suppliersTable" class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-primary">
                  <tr>
                    <th>ID</th>
                    <th>Supplier Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                  </tr>
                </thead>

                <tbody>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= $row['id']; ?></td>
                      <td><?= htmlspecialchars($row['name']); ?></td>
                      <td><?= htmlspecialchars($row['phone']); ?></td>
                      <td><?= htmlspecialchars($row['email']); ?></td>
                      <td><?= date('d M Y h:i A', strtotime($row['created_at'])); ?></td>
                      <td><?= date('d M Y h:i A', strtotime($row['updated_at'])); ?></td>
                      <td>
                        <div class="d-flex gap-1">
                          <a href="edit.php?id=<?= urlencode($row['id']) ?>" class="btn btn-warning btn-sm flex-fill">
                            <i class="bi bi-pencil-square"></i> Edit
                          </a>
                          <a href="delete.php?id=<?= urlencode($row['id']) ?>" class="btn btn-danger btn-sm flex-fill"
                            onclick="return confirm('Are you sure you want to delete this category?');">
                            <i class="bi bi-trash"></i> Delete
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>

          <?php endif; ?>

        </div>
      </div>


    </main>

    <?php include_once '../Inc/Footer.php'; ?>

  </div>


  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Bootstrap Core JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

  <!-- DataTables -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#suppliersTable').DataTable({
        "paging": true,
        "pageLength": 10,
        "ordering": true,
        "order": [],
        "columnDefs": [{
          "orderable": false,
          "targets": 6
        }]
      });
    });
  </script>

</body>

</html>