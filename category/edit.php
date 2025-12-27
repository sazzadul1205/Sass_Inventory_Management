<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('edit_category', '../index.php');

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
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Edit Category | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" />

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

// Fetch subcategories if main category
$subCategories = [];
if ($category['parent_id'] === null) {
  $stmtSub = $conn->prepare("SELECT * FROM category WHERE parent_id = ? ORDER BY id ASC");
  $stmtSub->bind_param("i", $categoryId);
  $stmtSub->execute();
  $subCategories = $stmtSub->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmtSub->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name        = trim($_POST['name']);
  $description = trim($_POST['description']);
  $updated_at  = date('Y-m-d H:i:s');

  if (empty($name)) {
    $formError = "Category name cannot be empty!";
  } else {
    // Update main category
    $stmt = $conn->prepare("UPDATE category SET name = ?, description = ?, updated_at = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $description, $updated_at, $categoryId);
    $stmt->execute();
    $stmt->close();

    // Handle subcategories
    if ($category['parent_id'] === null) {
      $submittedSubs = $_POST['subcategories'] ?? [];

      foreach ($submittedSubs as $subIdKey => $sub) {
        $subName = trim($sub['name'] ?? '');
        $subDesc = trim($sub['description'] ?? '');
        $subId   = isset($sub['id']) ? intval($sub['id']) : null;

        if ($subName === '') continue;

        if ($subId) {
          // Update existing subcategory
          $stmtUpdate = $conn->prepare("UPDATE category SET name = ?, description = ?, updated_at = ? WHERE id = ?");
          $stmtUpdate->bind_param("sssi", $subName, $subDesc, $updated_at, $subId);
          $stmtUpdate->execute();
          $stmtUpdate->close();
        } else {
          // Insert new subcategory
          $created_at = date('Y-m-d H:i:s');
          $stmtInsert = $conn->prepare("INSERT INTO category (name, description, parent_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
          $stmtInsert->bind_param("ssiss", $subName, $subDesc, $categoryId, $created_at, $updated_at);
          $stmtInsert->execute();
          $stmtInsert->close();
        }
      }
    }

    $_SESSION['success_message'] = "Category '$name' updated successfully!";
    header("Location: index.php");
    exit;
  }
}


$conn->close();
?>

<!-- body -->

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

      <!-- Form Error -->
      <?php if (!empty($formError)): ?>
        <div id="errorBox" class="alert alert-danger text-center">
          <?= htmlspecialchars($formError) ?>
        </div>
      <?php endif; ?>

      <!-- App Content Body -->
      <div class="app-content-body mt-3">
        <div class="container-fluid">
          <div class="card shadow-sm rounded-3">
            <div class="card-body">

              <!-- Header -->
              <h4 class="mb-4">Update Category Information</h4>

              <!-- Form -->
              <form method="POST" action="">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" class="form-control" id="name" name="name" required
                      value="<?= htmlspecialchars($category['name']) ?>">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" class="form-control" id="description" name="description"
                      value="<?= htmlspecialchars($category['description']) ?>">
                  </div>
                </div>

                <!-- Subcategories -->
                <?php if ($category['parent_id'] === null): ?>
                  <div class="mt-4">
                    <h5 class="mb-3 fw-bold text-secondary border-bottom pb-2">Subcategories</h5>
                    <div id="subcategories-wrapper" class="d-flex flex-column gap-2">
                      <?php foreach ($subCategories as $sub): ?>
                        <div class="d-flex align-items-center gap-2 subcategory-row">
                          <input type="hidden" name="subcategories[<?= $sub['id'] ?>][id]" value="<?= $sub['id'] ?>">
                          <input type="text" name="subcategories[<?= $sub['id'] ?>][name]" class="form-control" style="flex:0 0 30%" placeholder="Subcategory Name" value="<?= htmlspecialchars($sub['name']) ?>">
                          <input type="text" name="subcategories[<?= $sub['id'] ?>][description]" class="form-control" style="flex:0 0 66%" placeholder="Description" value="<?= htmlspecialchars($sub['description']) ?>">
                          <button type="button" class="btn btn-danger btn-remove-subcategory flex-shrink-0"><i class="bi bi-dash-lg"></i></button>
                        </div>
                      <?php endforeach; ?>

                      <!-- Blank row for new subcategory -->
                      <div class="d-flex align-items-center gap-2 subcategory-row" data-new-row="1">
                        <input type="text" name="subcategories[new_0][name]" class="form-control" style="flex:0 0 30%" placeholder="Subcategory Name">
                        <input type="text" name="subcategories[new_0][description]" class="form-control" style="flex:0 0 66%" placeholder="Description">
                        <button type="button" class="btn btn-success btn-add-subcategory flex-shrink-0"><i class="bi bi-plus-lg"></i></button>
                      </div>
                    </div>
                  </div>
                <?php else: ?>
                  <div class="alert alert-info mt-3">
                    This is a subcategory. Subcategories cannot have their own subcategories.
                  </div>
                <?php endif; ?>

                <div class="mt-4 d-flex gap-2">
                  <button type="submit" class="btn btn-primary px-4 py-2"><i class="bi bi-check2-circle"></i> Update Category</button>
                  <a href="index.php" class="btn btn-secondary px-4 py-2"><i class="bi bi-x-circle"></i> Cancel</a>
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

  <!-- Add new subcategory -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const wrapper = document.getElementById('subcategories-wrapper');
      let newSubCounter = 1; // for unique new subcategory keys

      wrapper.addEventListener('click', e => {
        // Add new subcategory row
        if (e.target.closest('.btn-add-subcategory')) {
          const row = e.target.closest('.subcategory-row');
          const clone = row.cloneNode(true);

          // Clear input values
          clone.querySelectorAll('input').forEach(input => {
            if (input.type !== "hidden") input.value = '';
          });

          // Update names for PHP processing
          clone.querySelectorAll('input').forEach(input => {
            if (!input.hasAttribute('type') || input.type === 'text') {
              const field = input.getAttribute('placeholder').toLowerCase().includes('name') ? 'name' : 'description';
              input.name = `subcategories[new_${newSubCounter}][${field}]`;
            }
          });
          newSubCounter++;

          // Switch button to remove
          const btn = clone.querySelector('.btn-add-subcategory');
          btn.classList.remove('btn-success', 'btn-add-subcategory');
          btn.classList.add('btn-danger', 'btn-remove-subcategory');
          btn.innerHTML = '<i class="bi bi-dash-lg"></i>';

          wrapper.appendChild(clone);
        }

        // Remove subcategory row
        if (e.target.closest('.btn-remove-subcategory')) {
          const row = e.target.closest('.subcategory-row');
          row.remove();
        }
      });
    });
  </script>


</body>

</html>