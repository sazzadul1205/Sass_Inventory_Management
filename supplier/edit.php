<?php
include_once __DIR__ . '/../config/auth_guard.php';
requirePermission('edit_supplier', '../index.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$formError = "";
$conn = connectDB();

// Check supplier ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['fail_message'] = "Invalid supplier ID!";
  header("Location: index.php");
  exit;
}

$supplier_id = intval($_GET['id']);

// Fetch supplier info
$stmt = $conn->prepare("SELECT * FROM supplier WHERE id = ?");
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['fail_message'] = "Supplier not found!";
  header("Location: index.php");
  exit;
}

$supplier = $result->fetch_assoc();
$stmt->close();

// Fetch selected categories for this supplier
$catResult = $conn->query("SELECT category_id FROM supplier_category WHERE supplier_id = $supplier_id");
$selectedCategories = [];
while ($row = $catResult->fetch_assoc()) {
  $selectedCategories[] = $row['category_id'];
}

// Handle form submission
if (isset($_POST['submit'])) {
  $supplierData = $_POST['supplier'] ?? [];
  $name = trim($supplierData['name'] ?? '');
  $phone = trim($supplierData['phone'] ?? '');
  $email = trim($supplierData['email'] ?? '');
  $address = trim($supplierData['address'] ?? '');
  $contact_person = trim($supplierData['contact_person'] ?? '');
  $type = trim($supplierData['type'] ?? '');
  $categories = $supplierData['categories'] ?? [];
  $updated_at = date('Y-m-d H:i:s');

  if (empty($name)) {
    $formError = "Supplier name is required.";
  } else {
    // Update supplier info
    $stmt = $conn->prepare("UPDATE supplier SET name=?, phone=?, email=?, address=?, contact_person=?, type=?, updated_at=? WHERE id=?");
    $stmt->bind_param("sssssssi", $name, $phone, $email, $address, $contact_person, $type, $updated_at, $supplier_id);

    if ($stmt->execute()) {
      // Update categories
      $conn->query("DELETE FROM supplier_category WHERE supplier_id = $supplier_id");
      if (!empty($categories)) {
        $stmt2 = $conn->prepare("INSERT INTO supplier_category (supplier_id, category_id) VALUES (?, ?)");
        foreach ($categories as $cat_id) {
          $cat_id = intval($cat_id);
          $stmt2->bind_param("ii", $supplier_id, $cat_id);
          $stmt2->execute();
        }
        $stmt2->close();
      }

      $_SESSION['success_message'] = "Supplier '$name' updated successfully!";
      header("Location: index.php");
      exit;
    } else {
      $formError = "Failed to update supplier: " . $stmt->error;
    }
    $stmt->close();
  }
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Edit Supplier | Sass Inventory</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Fonts & Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3/index.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css">

  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <style>
    .card-custom {
      border-radius: 12px;
      border: 1px solid #e9ecef;
      transition: .2s ease;
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

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <?php include_once '../Inc/Navbar.php'; ?>
    <?php include_once '../Inc/Sidebar.php'; ?>

    <main class="app-main">
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <h3 class="mb-0" style="font-weight:800;">Edit Supplier</h3>
        </div>
      </div>

      <?php if (!empty($formError)): ?>
        <div id="errorBox" class="alert alert-danger text-center"><?= htmlspecialchars($formError) ?></div>
      <?php endif; ?>

      <div class="app-content-body mt-4">
        <div class="container-fluid">
          <div class="card card-custom shadow-sm">
            <div class="card-body p-4">
              <h4 class="mb-4 fw-bold text-secondary border-bottom pb-2">Update Supplier Information</h4>

              <form method="post" autocomplete="on">
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Supplier Name *</label>
                    <input type="text" name="supplier[name]" class="form-control" value="<?= htmlspecialchars($supplier['name']) ?>" required>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="supplier[phone]" class="form-control" value="<?= htmlspecialchars($supplier['phone']) ?>">
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="supplier[email]" class="form-control" value="<?= htmlspecialchars($supplier['email']) ?>">
                  </div>

                  <div class="col-md-12 mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="supplier[address]" class="form-control" rows="2"><?= htmlspecialchars($supplier['address']) ?></textarea>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="supplier[contact_person]" class="form-control" value="<?= htmlspecialchars($supplier['contact_person']) ?>">
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">Supplier Type</label>
                    <select name="supplier[type]" class="form-select">
                      <option value="">Select Type</option>
                      <option value="manufacturer" <?= $supplier['type'] == 'manufacturer' ? 'selected' : '' ?>>Manufacturer</option>
                      <option value="distributor" <?= $supplier['type'] == 'distributor' ? 'selected' : '' ?>>Distributor</option>
                      <option value="wholesaler" <?= $supplier['type'] == 'wholesaler' ? 'selected' : '' ?>>Wholesaler</option>
                      <option value="retailer" <?= $supplier['type'] == 'retailer' ? 'selected' : '' ?>>Retailer</option>
                    </select>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Categories</label>
                    <select name="supplier[categories][]" class="form-select" id="supplier_categories" multiple="multiple">
                      <?php
                      $catResult = $conn->query("SELECT id, name FROM category WHERE parent_id IS NULL ORDER BY name ASC");
                      while ($cat = $catResult->fetch_assoc()) {
                        $selected = in_array($cat['id'], $selectedCategories) ? 'selected' : '';
                        echo "<option value=\"{$cat['id']}\" $selected>{$cat['name']}</option>";
                      }
                      ?>
                    </select>
                    <small class="text-muted">Select one or more categories for this supplier</small>
                  </div>

                </div>

                <div class="mt-4 d-flex gap-2">
                  <button type="submit" name="submit" class="btn btn-primary px-4 py-2"><i class="bi bi-check2-circle"></i> Update Supplier</button>
                  <a href="index.php" class="btn btn-secondary px-4 py-2"><i class="bi bi-x-circle"></i> Cancel</a>
                </div>
              </form>

            </div>
          </div>
        </div>
      </div>
    </main>

    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#supplier_categories').select2({
        placeholder: "Select categories",
        width: '100%',
        closeOnSelect: false,
        allowClear: true
      });

      setTimeout(() => {
        const box = document.getElementById("errorBox");
        if (box) {
          box.style.opacity = '0';
          setTimeout(() => box.remove(), 500);
        }
      }, 3000);
    });
  </script>
</body>

</html>