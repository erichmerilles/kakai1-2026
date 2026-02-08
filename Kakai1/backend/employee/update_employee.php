<?php
// backend/employee/update_employee.php
session_start();
require_once __DIR__ . '/../../config/db.php';

// 1. Force JSON Response (Crucial for Modal)
header('Content-Type: application/json');

// 2. Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
  echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
  exit;
}

// 3. Get POST Data
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
$date_hired = $_POST['date_hired'] ?? null; // Added date_hired
$role       = trim($_POST['role'] ?? 'Employee'); // Optional if your modal has it

// Handle Password (Optional)
$newPassword = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

try {
  $pdo->beginTransaction();

  // 4. Update EMPLOYEES Table (Added email and date_hired to your query)
  $stmtEmp = $pdo->prepare("
        UPDATE employees
        SET first_name=?, last_name=?, email=?, contact_number=?, position=?, daily_rate=?, status=?, date_hired=?
        WHERE employee_id=?
    ");
  $stmtEmp->execute([$first_name, $last_name, $email, $contact, $position, $daily_rate, $status, $date_hired, $empId]);

  // 5. Update USERS Table (Sync Role/Status/Password)
  if ($newPassword) {
    // Update password if provided
    $stmtUser = $pdo->prepare("UPDATE users SET status=?, password=? WHERE employee_id=?");
    $stmtUser->execute([$status, $newPassword, $empId]);
  } else {
    // Update status only
    $stmtUser = $pdo->prepare("UPDATE users SET status=? WHERE employee_id=?");
    $stmtUser->execute([$status, $empId]);
  }

  // NOTE: If your modal has a Role dropdown, you can add `role=?` to the queries above. 
  // I kept it safe by only updating status/password to avoid breaking admins.

  $pdo->commit();

  // 6. Return Success JSON
  echo json_encode(['success' => true, 'message' => 'Employee updated successfully!']);
} catch (Exception $e) {
  $pdo->rollBack();
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
