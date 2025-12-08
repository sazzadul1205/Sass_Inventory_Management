<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('add_supplier', '../index.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Add New Supplier | Sass Inventory System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" />

  <!-- Mobile + Theme -->
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Fonts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="print" onload="this.media='all'" />

  <!-- Overlay Scrollbars -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />

  <!-- AdminLTE -->
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />

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
  </style>
</head>

<?php
$formError = "";

// Connect to database
$conn = connectDB();

// Handle Submit
if (isset($_POST['submit'])) {

  $name   = trim($_POST['name']);
  $phone  = trim($_POST['phone']);
  $email  = trim($_POST['email']);
  $created_at = date('Y-m-d H:i:s');
  $updated_at = date('Y-m-d H:i:s');

  if (empty($name)) {
    $formError = "Supplier name is required.";
  } else {

    $conn = connectDB();

    $query = "INSERT INTO supplier (name, phone, email, created_at, updated_at) 
                  VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $name, $phone, $email, $created_at, $updated_at);

    if ($stmt->execute()) {
      $_SESSION['success_message'] = "Supplier added successfully!";
      header("Location: index.php");  // suppliers list
      exit;
    } else {
      $formError = "Failed to add supplier: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
  }
}
?>

<!-- Body -->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">

    <!-- Navbar -->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!-- Sidebar -->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!-- Main Content -->
    <main class="app-main">

      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <!-- Page Title -->
          <h3 class="mb-0 " style="font-weight: 800;">Add New Supplier</h3>
        </div>
      </div>

      <!-- Form Error -->
      <?php if (!empty($formError)): ?>
        <div id="errorBox" class="alert alert-danger text-center">
          <?= htmlspecialchars($formError) ?>
        </div>
      <?php endif; ?>

      <!-- Body -->
      <div class="app-content-body mt-4">
        <div class="container-fluid">
          <div class="card card-custom shadow-sm">
            <div class="card-body p-4">

              <!-- Header -->
              <h4 class="mb-4">Add Supplier Information</h4>

              <!-- Form -->
              <form method="post" autocomplete="on">

                <!-- Supplier Information -->
                <div class="row">

                  <!-- Supplier Name -->
                  <div class="col-md-4 mb-3">
                    <label for="name" class="form-label">Supplier Name *</label>
                    <input
                      type="text"
                      name="name"
                      id="name"
                      class="form-control"
                      placeholder="e.g ABC Suppliers"
                      required>
                  </div>

                  <!-- Phone -->
                  <div class="col-md-4 mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input
                      type="text"
                      name="phone"
                      id="phone"
                      class="form-control"
                      placeholder="e.g +1234567890">
                  </div>

                  <!-- Email -->
                  <div class="col-md-4 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input
                      type="email"
                      name="email"
                      id="email"
                      class="form-control"
                      placeholder="e.g 4mH9S@example.com">
                  </div>
                </div>

                <!-- Hidden timestamps -->
                <input type="hidden" name="created_at" value="<?= date('Y-m-d H:i:s') ?>">
                <input type="hidden" name="updated_at" value="<?= date('Y-m-d H:i:s') ?>">


                <!-- Buttons -->
                <div class="mt-4 d-flex gap-2">
                  <button type="submit" name="submit" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-check2-circle"></i> Save Category
                  </button>
                  <a href="index.php" class="btn btn-secondary px-4 py-2">
                    <i class="bi bi-x-circle"></i> Cancel
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlaysscrollbars.browser.es6.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

  <!-- Auto-hide error -->
  <script>
    setTimeout(() => {
      const box = document.getElementById("errorBox");
      if (box) {
        box.style.opacity = "0";
        setTimeout(() => box.remove(), 500);
      }
    }, 3000);
  </script>

</body>

</html>