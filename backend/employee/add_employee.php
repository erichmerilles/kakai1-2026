<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

header('Content-Type: application/json');

// check permissions
if (!hasPermission('emp_add')) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized: You do not have permission to add employees.']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // retrieve and sanitize input
  $first_name = trim($_POST['first_name'] ?? '');
  $last_name  = trim($_POST['last_name'] ?? '');
  $email      = trim($_POST['email'] ?? '');
  $contact    = trim($_POST['contact_number'] ?? '');
  $position   = trim($_POST['position'] ?? '');
  $status     = trim($_POST['status'] ?? 'Active');
  $daily_rate = floatval($_POST['daily_rate'] ?? 0);
  $date_hired = $_POST['date_hired'] ?? date('Y-m-d');
  $password   = $_POST['password'] ?? '';

  $role = 'Employee';
  $username = trim($first_name . ' ' . $last_name);

  if (empty($first_name) || empty($last_name) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'First Name, Last Name, and Password are required.']);
    exit;
  }

  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  try {
    $pdo->beginTransaction();

    // insert into employees table
    $stmtEmp = $pdo->prepare("
            INSERT INTO employees 
            (first_name, last_name, email, contact_number, position, daily_rate, status, date_hired, role, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

    $stmtEmp->execute([
      $first_name,
      $last_name,
      $email,
      $contact,
      $position,
      $daily_rate,
      $status,
      $date_hired,
      $role,
      $hashedPassword
    ]);

    $employee_id = $pdo->lastInsertId();

    // insert into users table
    $stmtUser = $pdo->prepare("
            INSERT INTO users 
            (employee_id, username, password, role, status, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

    $stmtUser->execute([
      $employee_id,
      $username,
      $hashedPassword,
      $role,
      $status
    ]);

    // initialize default permissions
    $stmtPerms = $pdo->prepare("INSERT INTO employee_permissions (employee_id) VALUES (?)");
    $stmtPerms->execute([$employee_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Employee and User Account created successfully!']);
  } catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
