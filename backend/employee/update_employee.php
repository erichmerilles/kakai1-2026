<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

header('Content-Type: application/json');

// check permissions
if (!hasPermission('emp_edit')) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized: You do not have permission to edit employees.']);
  exit;
}

// get employee ID from POST data
$empId = $_POST['employee_id'] ?? null;

if (!$empId) {
  echo json_encode(['success' => false, 'message' => 'Employee ID is missing.']);
  exit;
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$contact    = trim($_POST['contact_number'] ?? '');
$position   = trim($_POST['position'] ?? '');
$daily_rate = floatval($_POST['daily_rate'] ?? 0);
$status     = trim($_POST['status'] ?? 'Active');
$date_hired = $_POST['date_hired'] ?? null;
$role       = trim($_POST['role'] ?? 'Employee');

// handle optional password update
$newPassword = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

try {
  $pdo->beginTransaction();

  // update employees table
  $stmtEmp = $pdo->prepare("
        UPDATE employees
        SET first_name=?, last_name=?, email=?, contact_number=?, position=?, daily_rate=?, status=?, date_hired=?
        WHERE employee_id=?
    ");
  $stmtEmp->execute([$first_name, $last_name, $email, $contact, $position, $daily_rate, $status, $date_hired, $empId]);

  // update users table
  if ($newPassword) {
    // update status and password
    $stmtUser = $pdo->prepare("UPDATE users SET status=?, password=? WHERE employee_id=?");
    $stmtUser->execute([$status, $newPassword, $empId]);
  } else {
    // update status
    $stmtUser = $pdo->prepare("UPDATE users SET status=? WHERE employee_id=?");
    $stmtUser->execute([$status, $empId]);
  }

  // commit transaction
  $pdo->commit();

  // return success response
  echo json_encode(['success' => true, 'message' => 'Employee updated successfully!']);
} catch (Exception $e) {
  $pdo->rollBack();
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
