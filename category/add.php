<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('add_category', '../index.php');

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
  <title>Add New Category | Sass Inventory System</title>
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

// Fetch roles (currently unused, can remove if not needed)
$roleQuery = "SELECT * FROM role ORDER BY role_name ASC";
$rolesResult = $conn->query($roleQuery);

// Form Submit Handler
if (isset($_POST['submit'])) {

  // Collect and sanitize input
  $name        = trim($_POST['name']);
  $description = trim($_POST['description']);
  $created_at  = date('Y-m-d H:i:s');
  $updated_at  = date('Y-m-d H:i:s');

  // Basic validation
  if (empty($name)) {
    $formError = "Category name is required.";
  } else {
    $stmt = $conn->prepare("INSERT INTO category (name, description, created_at, updated_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $description, $created_at, $updated_at);

    if ($stmt->execute()) {
      $_SESSION['success_message'] = "Category added successfully!";
      header("Location: index.php"); // redirect to category listing
      exit;
    } else {
      $formError = "Failed to add category: " . $stmt->error;
    }

    $conn->close();
    $stmt->close();
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
          <h3 class="mb-0 " style="font-weight: 800;">Add New Category</h3>
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
              <h4 class="mb-4 fw-bold text-secondary border-bottom pb-2">
                Add Category Information
              </h4>

              <!-- Form -->
              <form method="post" autocomplete="on">

                <!-- Category Name & Description  -->
                <div class="row">
                  <!-- Category Name -->
                  <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input
                      type="text"
                      name="name"
                      id="name"
                      class="form-control"
                      placeholder="e.g Mobile, Hardware etc..."
                      required>
                  </div>

                  <!-- Description -->
                  <div class="col-md-6 mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input
                      type="text"
                      name="description"
                      id="description"
                      class="form-control"
                      placeholder=" Brief description about the category">
                  </div>
                </div>

                <!-- Hidden timestamps -->
                <input type="hidden" name="created_at" value="<?= date('Y-m-d H:i:s') ?>">
                <input type="hidden" name="updated_at" value="<?= date('Y-m-d H:i:s') ?>">

                <!-- Buttons -->
                <div class="mt-4 d-flex gap-2">
                  <button type="submit" name="submit" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-check2-circle"></i> Add Category
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