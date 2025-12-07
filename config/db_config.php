<!-- db_config.php -->
<?php

// Database configuration
$DB_HOST = "localhost";
$DB_NAME = "sass_inventory";
$DB_USER = "root";
$DB_PASS = "";

// Connect to MySQL database
function connectDB()
{
  global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;

  $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  return $conn;
}

/**
 * Check if the current user has permission
 */
function can($permission, $userPermissions = null)
{
  global $USER_PERMISSIONS;
  $userPermissions = $userPermissions ?? $USER_PERMISSIONS;
  return in_array($permission, $userPermissions);
}

// $Project_URL = "http://localhost/PWAD-68-1293312/Sass_Inventory/";
$Project_URL = "http://localhost/Sass_Inventory_Management/";
