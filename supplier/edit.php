<?php include_once __DIR__ . '/../config/db_config.php'; ?>
<?php
session_start();
$formError = "";
?>

<!doctype html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Add Supplier | Sass Inventory Management System</title>

  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />

  <!-- Bootstrap & Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css">
</head>


<?php
$supplier = null;

if (!isset($_GET['id']) || empty($_GET['id'])) {
  die("Invalid supplier ID.");
}

$id = intval($_GET['id']);

// Fetch supplier
$conn = connectDB();
$query = "SELECT * FROM supplier WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$supplier = $result->fetch_assoc();

if (!$supplier) {
  die("Supplier not found.");
}
$stmt->close();

// Handle Update
if (isset($_POST['submit'])) {

  $name   = trim($_POST['name']);
  $phone  = trim($_POST['phone']);
  $email  = trim($_POST['email']);
  $updated_at = date('Y-m-d H:i:s');

  if (empty($name)) {
    $formError = "Supplier name is required.";
  } else {

    $query = "UPDATE supplier SET name = ?, phone = ?, email = ?, updated_at = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $name, $phone, $email, $updated_at, $id);

    if ($stmt->execute()) {
      $_SESSION['success_message'] = "Supplier updated successfully!";
      header("Location: index.php");
      exit;
    } else {
      $formError = "Failed to update supplier: " . $stmt->error;
    }

    $stmt->close();
  }
}

$conn->close();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Edit Supplier | Sass Inventory Management System</title>

  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap & Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css">
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

  <div class="app-wrapper">

    <?php include_once '../Inc/Navbar.php'; ?>
    <?php include_once '../Inc/Sidebar.php'; ?>

    <main class="app-main">

      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid">
          <h3 class="mb-0">Edit Supplier</h3>
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
              <h4 class="mb-4">Update Supplier Information</h4>

              <form method="post">

                <div class="row">

                  <!-- Supplier Name -->
                  <div class="col-md-4 mb-3">
                    <label for="name" class="form-label">Supplier Name *</label>
                    <input type="text" name="name" id="name" class="form-control"
                      value="<?= htmlspecialchars($supplier['name']) ?>" required>
                  </div>

                  <!-- Phone -->
                  <div class="col-md-4 mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control"
                      value="<?= htmlspecialchars($supplier['phone']) ?>">
                  </div>

                  <!-- Email -->
                  <div class="col-md-4 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control"
                      value="<?= htmlspecialchars($supplier['email']) ?>">
                  </div>

                </div>

                <button type="submit" name="submit" class="btn btn-primary mt-2">
                  Update Supplier
                </button>

              </form>

            </div>
          </div>
        </div>
      </div>

    </main>

    <?php include_once '../Inc/Footer.php'; ?>

  </div>

  <script>
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