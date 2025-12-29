<?php


// $Project_URL = "http://localhost/PWAD-68-1293312/Sass_Inventory_Management/";
$Project_URL = "http://localhost/Sass_Inventory_Management/";


function getUserPermissions($roleId, $conn)
{
  $permissions = [];

  $stmt = $conn->prepare("
        SELECT permission_name 
        FROM role_permission_matrix 
        WHERE role_id = ? AND assigned = 1
    ");
  $stmt->bind_param("i", $roleId);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $permissions[] = $row['permission_name'];
  }

  return $permissions;
}
