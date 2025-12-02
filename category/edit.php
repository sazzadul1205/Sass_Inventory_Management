<?php include_once __DIR__ . '/../config/db_config.php'; ?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Edit Category | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
</head>

<?php
session_start();
$formError = "";

// Check for category ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid category ID.");
}

$categoryId = intval($_GET['id']);
$conn = connectDB();

// Fetch category data
$stmt = $conn->prepare("SELECT * FROM category WHERE id = ?");
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
  die("Category not found.");
}

$category = $result->fetch_assoc();

// Handle form submission
if (isset($_POST['submit'])) {
  $name        = trim($_POST['name']);
  $description = trim($_POST['description']);
  $updated_at  = date('Y-m-d H:i:s');

  if (empty($name)) {
    $formError = "Category name is required.";
  } else {
    $updateStmt = $conn->prepare("UPDATE category SET name = ?, description = ?, updated_at = ? WHERE id = ?");
    $updateStmt->bind_param("sssi", $name, $description, $updated_at, $categoryId);

    if ($updateStmt->execute()) {
      $_SESSION['success_message'] = "Category updated successfully!";
      header("Location: index.php");
      exit;
    } else {
      $formError = "Failed to update category: " . $updateStmt->error;
    }

    $updateStmt->close();
  }
}

$conn->close();
?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <?php include_once '../Inc/Navbar.php'; ?>
    <?php include_once '../Inc/Sidebar.php'; ?>

    <main class="app-main">
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid">
          <h3 class="mb-0">Edit Category</h3>
        </div>
      </div>

      <?php if (!empty($formError)): ?>
        <div id="errorBox" class="alert alert-danger text-center">
          <?= htmlspecialchars($formError) ?>
        </div>
      <?php endif; ?>

      <div class="app-content-body mt-3">
        <div class="container-fluid">
          <div class="card shadow-sm rounded-3">
            <div class="card-body">
              <h4 class="mb-4">Update Category Information</h4>
              <form method="post" autocomplete="on">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" name="name" id="name" class="form-control"
                      value="<?= htmlspecialchars($category['name']) ?>" required>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" name="description" id="description" class="form-control"
                      value="<?= htmlspecialchars($category['description']) ?>">
                  </div>
                </div>

                <button type="submit" name="submit" class="btn btn-primary mt-2">Update Category</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </main>

    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <script src="<?= $Project_URL ?>/js/adminlte.js"></script>

  <script>
    // Fade out error box
    setTimeout(() => {
      const errorBox = document.getElementById("errorBox");
      if (errorBox) {
        errorBox.style.transition = "opacity 0.5s";
        errorBox.style.opacity = "0";
        setTimeout(() => errorBox.remove(), 500);
      }
    }, 3000);
  </script>
</body>

</html>