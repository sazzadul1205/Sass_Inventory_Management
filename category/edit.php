<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';
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
  <title>Edit Category | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon" />

  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Fonts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="print" onload="this.media='all'" />

  <!-- Overlay Scrollbars -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />

  <!-- AdminLTE -->
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />
</head>

<?php
$conn = connectDB();
$formError = "";

// Check for category ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['fail_message'] = "Invalid category ID!";
  header("Location: index.php");
  exit;
}

$categoryId = intval($_GET['id']);

// Fetch category info
$stmt = $conn->prepare("SELECT * FROM category WHERE id = ?");
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['fail_message'] = "Category not found!";
  header("Location: index.php");
  exit;
}

$category = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name        = trim($_POST['name']);
  $description = trim($_POST['description']);
  $updated_at  = date('Y-m-d H:i:s');

  if (!empty($name)) {
    $stmt = $conn->prepare("UPDATE category SET name = ?, description = ?, updated_at = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $description, $updated_at, $categoryId);

    if ($stmt->execute()) {
      $_SESSION['success_message'] = "Category '$name' updated successfully!";
      header("Location: index.php");
      exit;
    } else {
      $formError = "Failed to update category: " . $stmt->error;
    }

    $stmt->close();
  } else {
    $formError = "Category name cannot be empty!";
  }
}

$conn->close();
?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <!-- Navbar -->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!--Sidebar-->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!--App Main-->
    <main class="app-main">
      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <h3 class="mb-0" style="font-weight: 800;">Edit Category</h3>
        </div>
      </div>

      <!-- Messages -->
      <?php if (!empty($_SESSION['success_message'])): ?>
        <div id="successMsg" class="alert alert-success"><?= $_SESSION['success_message']; ?></div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>
      <?php if (!empty($formError)): ?>
        <div id="failMsg" class="alert alert-danger"><?= htmlspecialchars($formError); ?></div>
      <?php endif; ?>

      <!-- App Content Body -->
      <div class="app-content-body mt-3">
        <div class="container-fluid">
          <div class="card shadow-sm rounded-3">
            <div class="card-body">
              <h4 class="mb-4">Update Category Information</h4>

              <!-- Form -->
              <form method="POST" action="">
                <div class="row">
                  <!-- Category Name -->
                  <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" class="form-control" id="name" name="name" required
                      value="<?= htmlspecialchars($category['name']) ?>">
                  </div>

                  <!-- Description -->
                  <div class="col-md-6 mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" class="form-control" id="description" name="description"
                      value="<?= htmlspecialchars($category['description']) ?>">
                  </div>
                </div>

                <!-- Buttons -->
                <div class="mt-4 d-flex gap-2">
                  <button type="submit" name="submit" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-check2-circle"></i> Update Category
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

    <!--Footer-->
    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
  <script src="<?= $Project_URL ?>/js/adminlte.js"></script>

  <!-- Auto Remove Messages -->
  <script>
    setTimeout(() => {
      document.querySelectorAll("#successMsg, #failMsg").forEach(el => {
        el.style.transition = "0.5s";
        el.style.opacity = "0";
        setTimeout(() => el.remove(), 500);
      });
    }, 2500);
  </script>
</body>

</html>