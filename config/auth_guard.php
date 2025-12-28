<?php
// auth_guard.php

// ---- SAFE SESSION START ----
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// ---- LOAD DB CONFIG ----
if (!defined('DB_CONFIG_LOADED')) {
  define('DB_CONFIG_LOADED', true);
  include_once __DIR__ . '/db_config.php';
}

// ---- LOAD PERMISSIONS FOR A ROLE ----
function loadRolePermissions($role_id)
{
  $conn = connectDB();

  $stmt = $conn->prepare("
        SELECT permission_name
        FROM role_permission_matrix
        WHERE role_id = ? AND assigned = 1
    ");
  $stmt->bind_param("i", $role_id);
  $stmt->execute();

  $result = $stmt->get_result();
  $permissions = [];

  while ($row = $result->fetch_assoc()) {
    $permissions[] = $row['permission_name'];
  }

  $stmt->close();
  $conn->close();

  return $permissions;
}

// ---- DENY ACCESS FUNCTION ----
function denyAccess($redirectUrl = null)
{
  $_SESSION['fail_message'] = "You don't have permission to access this page.";

  if ($redirectUrl) {
    header("Location: $redirectUrl");
  } else {
    header("Location: ../error/403.php");
  }
  exit();
}

// ---- REQUIRE SPECIFIC PERMISSION ----
function requirePermission($permission, $redirectUrl = null)
{
  // Not logged in
  if (!isset($_SESSION['user_id'])) {
    $loginUrl = $redirectUrl ?? '../auth/login.php';
    header("Location: $loginUrl");
    exit();
  }

  // Role not set
  if (!isset($_SESSION['role_id'])) {
    denyAccess($redirectUrl);
  }

  // Cache permissions
  if (!isset($_SESSION['cached_permissions'])) {
    $_SESSION['cached_permissions'] = loadRolePermissions($_SESSION['role_id']);
  }

  if (!in_array($permission, $_SESSION['cached_permissions'])) {
    denyAccess($redirectUrl);
  }
}

// ---- HELPER: CHECK IF USER HAS PERMISSION ----
function hasPermission($permission)
{
  if (!isset($_SESSION['cached_permissions'])) {
    $_SESSION['cached_permissions'] = loadRolePermissions($_SESSION['role_id']);
  }
  return in_array($permission, $_SESSION['cached_permissions']);
}
