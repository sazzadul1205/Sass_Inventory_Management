<?php
// db_config.php

/* ===============================
   BASIC PHP ERROR LOGGING
================================ */
ini_set('display_errors', 0); // NEVER show errors to users
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_error.log');
error_reporting(E_ALL);

/* ===============================
   DATABASE CONFIG
================================ */
$DB_HOST = "localhost";
$DB_NAME = "sass_inventory";
$DB_USER = "root";
$DB_PASS = "";

/* ===============================
   PROJECT URL
================================ */
// $Project_URL = "http://localhost/Sass_Inventory_Management/";
$Project_URL = "https://billcorporation.org/Inventory/";

/* ===============================
   DATABASE CONNECTION
================================ */
function connectDB()
{
  global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;

  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

  try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8mb4');
    return $conn;
  } catch (mysqli_sql_exception $e) {
    error_log("DB CONNECTION ERROR: " . $e->getMessage());
    redirectDBError();
  }
}

/* ===============================
   PERMISSION CHECK
================================ */
function can($permission, $userPermissions = [])
{
  return in_array($permission, $userPermissions, true);
}

/* ===============================
   DB ERROR REDIRECT
================================ */
function redirectDBError()
{
  global $Project_URL;

  if (!headers_sent()) {
    header("Location: " . $Project_URL . "errors/db_not_connected.php");
  }
  exit;
}
