<?php
include_once __DIR__ . '/../config/auth_guard.php';
requirePermission('add_supplier', '../index.php');

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
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Fonts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="print" onload="this.media='all'" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />

  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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

    /* Select2 Custom Styling */
    .select2-container--default .select2-selection--single {
      height: calc(1.5em + 0.75rem + 2px);
      /* match bootstrap input height */
      padding: 0.375rem 0.75rem;
      line-height: 1.5;
      border-radius: 8px;
      border: 1px solid #ced4da;
      background-color: #fff;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 1.5;
      color: #000;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
      border-color: #000 transparent transparent transparent;
    }

    .select2-container--default .select2-dropdown {
      background-color: #fff;
      color: #000;
    }

    .select2-container--default .select2-results__option {
      color: #000;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
      background-color: #fff;
      color: #000;
      line-height: 1.5;
    }
  </style>
</head>

<?php
$formError = "";
$conn = connectDB();

if (isset($_POST['submit'])) {
  $supplier = $_POST['supplier'] ?? [];
  $name = trim($supplier['name'] ?? '');
  $phone = trim($supplier['phone'] ?? '');
  $email = trim($supplier['email'] ?? '');
  $address = trim($supplier['address'] ?? '');
  $contact_person = trim($supplier['contact_person'] ?? '');
  $type = trim($supplier['type'] ?? '');
  $categories = $supplier['categories'] ?? [];

  if (empty($name)) {
    $formError = "Supplier name is required.";
  } else {
    $conn = connectDB();

    // Insert supplier
    $stmt = $conn->prepare("
            INSERT INTO supplier (name, phone, email, address, contact_person, type, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
    $stmt->bind_param("ssssss", $name, $phone, $email, $address, $contact_person, $type);

    if ($stmt->execute()) {
      $supplier_id = $stmt->insert_id;

      // Insert categories
      if (!empty($categories)) {
        $stmt2 = $conn->prepare("INSERT INTO supplier_category (supplier_id, category_id) VALUES (?, ?)");
        foreach ($categories as $cat_id) {
          $cat_id = intval($cat_id);
          $stmt2->bind_param("ii", $supplier_id, $cat_id);
          $stmt2->execute();
        }
        $stmt2->close();
      }

      $_SESSION['success_message'] = "Supplier added successfully!";
      header("Location: index.php");
      exit;
    } else {
      $formError = "Failed to add supplier: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
  }
}

?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <?php include_once '../Inc/Navbar.php'; ?>
    <?php include_once '../Inc/Sidebar.php'; ?>

    <main class="app-main">
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <h3 class="mb-0" style="font-weight: 800;">Add New Supplier</h3>
        </div>
      </div>

      <?php if (!empty($formError)): ?>
        <div id="errorBox" class="alert alert-danger text-center"><?= htmlspecialchars($formError) ?></div>
      <?php endif; ?>

      <div class="app-content-body mt-4">
        <div class="container-fluid">
          <div class="card card-custom shadow-sm">
            <div class="card-body p-4">
              <h4 class="mb-4 fw-bold text-secondary border-bottom pb-2">Add Supplier Information</h4>

              <form method="post" autocomplete="on">
                <div class="row">
                  <div style="flex: 0 0 30%;">
                    <label for="supplier_name" class="form-label">Supplier Name</label>
                    <input type="text" name="supplier[name]" id="supplier_name" class="form-control" placeholder="Supplier Name" required>
                  </div>

                  <div style="flex: 0 0 30%;">
                    <label for="supplier_email" class="form-label">Email</label>
                    <input type="email" name="supplier[email]" id="supplier_email" class="form-control" placeholder="supplier@example.com">
                  </div>

                  <div style="flex: 0 0 30%;">
                    <label for="supplier_phone" class="form-label">Phone</label>
                    <input type="text" name="supplier[phone]" id="supplier_phone" class="form-control" placeholder="+8801XXXXXXXXX">
                  </div>

                  <div style="flex: 0 0 100%;" class="mt-3">
                    <label for="supplier_address" class="form-label">Address</label>
                    <textarea name="supplier[address]" id="supplier_address" class="form-control" rows="2" placeholder="Full Address"></textarea>
                  </div>

                  <div style="flex: 0 0 30%;" class="mt-3">
                    <label for="supplier_contact_person" class="form-label">Contact Person</label>
                    <input type="text" name="supplier[contact_person]" id="supplier_contact_person" class="form-control" placeholder="Contact Person Name">
                  </div>

                  <div style="flex: 0 0 30%;" class="mt-3">
                    <label for="supplier_type" class="form-label">Supplier Type</label>
                    <select name="supplier[type]" id="supplier_type" class="form-select">
                      <option value="">Select Type</option>
                      <option value="manufacturer">Manufacturer</option>
                      <option value="distributor">Distributor</option>
                      <option value="wholesaler">Wholesaler</option>
                      <option value="retailer">Retailer</option>
                    </select>
                  </div>

                  <div class="col-md-6 mb-3 mt-3">
                    <label class="form-label">Categories</label>
                    <select id="supplier_categories" name="supplier[categories][]" class="form-select" multiple="multiple">
                      <?php
                      $catResult = $conn->query("SELECT id, name FROM category WHERE parent_id IS NULL ORDER BY name ASC");
                      while ($cat = $catResult->fetch_assoc()) {
                        echo "<option value=\"{$cat['id']}\">{$cat['name']}</option>";
                      }
                      ?>
                    </select>
                    <small class="text-muted">Select one or more categories for this supplier</small>
                  </div>
                </div>

                <input type="hidden" name="created_at" value="<?= date('Y-m-d H:i:s') ?>">
                <input type="hidden" name="updated_at" value="<?= date('Y-m-d H:i:s') ?>">

                <div class="mt-4 d-flex gap-2">
                  <button type="submit" name="submit" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-check2-circle"></i> Save Supplier
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

      const box = document.getElementById("errorBox");
      if (box) setTimeout(() => {
        box.style.opacity = "0";
        setTimeout(() => box.remove(), 500);
      }, 3000);
    });
  </script>

</body>

</html>