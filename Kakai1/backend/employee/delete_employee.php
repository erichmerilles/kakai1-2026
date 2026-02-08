<?php
require_once __DIR__ . '/../../config/db.php';

if (!isset($_GET['id'])) {
  header('Location: employee_module.php');
  exit;
}

$empId = mysqli_real_escape_string($conn, $_GET['id']);

$conn->begin_transaction();
try {
  $conn->query("DELETE FROM users WHERE employee_id='$empId'");
  $conn->query("DELETE FROM employees WHERE employee_id='$empId'");
  $conn->commit();
  header('Location: employee_module.php?msg=deleted');
} catch (Exception $e) {
  $conn->rollback();
  header('Location: employee_module.php?error=delete_failed');
}
exit;
?>
