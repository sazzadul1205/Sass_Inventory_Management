<?php include_once __DIR__ . '/../config/db_config.php'; ?>
<?php
session_start();
$formError = "";
?>

<?php

$conn = connectDB();


$user_id = $_GET['id'];

// Prepare and execute the delete statement
$stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "User deleted successfully!";
    header("Location: users.php");
} else {
    $_SESSION['fail_message'] = "Failed to delete user!";
    header("Location: users.php");
}

$stmt->close();
