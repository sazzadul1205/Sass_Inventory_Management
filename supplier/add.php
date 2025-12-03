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

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

  <div class="app-wrapper">

    <!-- Navbar -->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!-- Sidebar -->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!-- Main -->
    <main class="app-main">

      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid">
          <h3 class="mb-0">Add New Supplier</h3>
        </div>
      </div>

      <!-- Error message -->
      <?php if (!empty($formError)): ?>
        <div id="errorBox" class="alert alert-danger text-center">
          <?= htmlspecialchars($formError) ?>
        </div>
      <?php endif; ?>

      <div class="app-content-body mt-3">
        <div class="container-fluid">
          <div class="card shadow-sm rounded-3">
            <div class="card-body">
              <h4 class="mb-4">Enter Supplier Information</h4>

              <form method="post">

                <div class="row">

                  <!-- Supplier Name -->
                  <div class="col-md-4 mb-3">
                    <label for="name" class="form-label">Supplier Name *</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                  </div>

                  <!-- Phone -->
                  <div class="col-md-4 mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control">
                  </div>

                  <!-- Email -->
                  <div class="col-md-4 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control">
                  </div>

                </div>

                <button type="submit" name="submit" class="btn btn-primary mt-2">
                  Add Supplier
                </button>

              </form>

            </div>
          </div>
        </div>
      </div>

    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>

  </div>

  <!-- Fade-out error box -->
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