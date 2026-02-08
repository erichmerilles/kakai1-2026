<?php
/*session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../auth/login.php');
  exit;
}

if (!isset($_GET['id'])) {
  header('Location: employee_module.php');
  exit;
}

$empId = mysqli_real_escape_string($conn, $_GET['id']);
$successMsg = '';
$errorMsg = '';

$query = "
  SELECT e.employee_id, e.full_name, e.position, e.status AS emp_status,
         u.username, u.role, u.status AS user_status
  FROM employees e
  LEFT JOIN users u ON e.employee_id = u.employee_id
  WHERE e.employee_id = '$empId'
";
$result = $conn->query($query);
$employee = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
  $position = mysqli_real_escape_string($conn, $_POST['position']);
  $role = mysqli_real_escape_string($conn, $_POST['role']);
  $status = mysqli_real_escape_string($conn, $_POST['status']);
  $newPassword = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

  $conn->begin_transaction();
  try {
    $updateEmp = $conn->query("
      UPDATE employees
      SET full_name='$fullname', position='$position', status='$status'
      WHERE employee_id='$empId'
    ");

    $updateUser = $conn->query("
      UPDATE users
      SET role='$role', status='$status' " . ($newPassword ? ", password='$newPassword'" : "") . "
      WHERE employee_id='$empId'
    ");

    if ($updateEmp && $updateUser) {
      $conn->commit();
      $successMsg = "Employee updated successfully.";
    } else {
      throw new Exception("Error updating employee.");
    }
  } catch (Exception $e) {
    $conn->rollback();
    $errorMsg = "Failed to update: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Employee | KakaiOne</title>
  <?php include '../includes/links.php'; ?>
</head>
<body class="bg-light">

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Employee</h3>
    <a href="employee_module.php" class="btn btn-secondary">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <?php if ($successMsg): ?>
        <div class="alert alert-success"><?= $successMsg ?></div>
      <?php elseif ($errorMsg): ?>
        <div class="alert alert-danger"><?= $errorMsg ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Full Name</label>
            <input type="text" name="fullname" class="form-control" value="<?= $employee['full_name'] ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Position</label>
            <input type="text" name="position" class="form-control" value="<?= $employee['position'] ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Role</label>
            <select name="role" class="form-select" required>
              <option <?= $employee['role']=='employee'?'selected':'' ?>>employee</option>
              <option <?= $employee['role']=='manager'?'selected':'' ?>>manager</option>
              <option <?= $employee['role']=='supervisor'?'selected':'' ?>>supervisor</option>
              <option <?= $employee['role']=='admin'?'selected':'' ?>>admin</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select">
              <option value="active" <?= $employee['user_status']=='active'?'selected':'' ?>>Active</option>
              <option value="inactive" <?= $employee['user_status']=='inactive'?'selected':'' ?>>Inactive</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Reset Password (Optional)</label>
            <input type="password" name="password" class="form-control" placeholder="Enter new password">
          </div>
          <div class="col-md-12">
            <button type="submit" class="btn btn-warning fw-semibold">
              <i class="bi bi-save"></i> Update Employee
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

</body>
</html>*/

session_start();
require_once __DIR__ . '/../../config/db.php';

// security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
  header('Location: ../auth/login.php');
  exit;
}

if (!isset($_GET['id'])) {
  header('Location: employee_module.php');
  exit;
}

$empId = intval($_GET['id']);
$successMsg = '';
$errorMsg = '';

// fetch employee data
try {
  $stmt = $pdo->prepare("
    SELECT e.employee_id, e.first_name, e.last_name, e.email, e.contact_number, 
           e.position, e.daily_rate, e.status AS emp_status,
           u.username, u.role, u.status AS user_status
    FROM employees e
    LEFT JOIN users u ON e.employee_id = u.employee_id
    WHERE e.employee_id = ?
  ");
  $stmt->execute([$empId]);
  $employee = $stmt->fetch();

  if (!$employee) {
    echo "Employee not found.";
    exit;
  }
} catch (PDOException $e) {
  die("Error fetching data: " . $e->getMessage());
}

// handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $first_name = trim($_POST['first_name']);
  $last_name  = trim($_POST['last_name']);
  $contact    = trim($_POST['contact_number']);
  $position   = trim($_POST['position']);
  $daily_rate = floatval($_POST['daily_rate']);
  $role       = trim($_POST['role']);
  $status     = trim($_POST['status']);
  $newPassword = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

  try {
    $pdo->beginTransaction();

    // update employees table
    $stmtEmp = $pdo->prepare("
      UPDATE employees
      SET first_name=?, last_name=?, contact_number=?, position=?, daily_rate=?, status=?
      WHERE employee_id=?
    ");
    $stmtEmp->execute([$first_name, $last_name, $contact, $position, $daily_rate, $status, $empId]);

    // update users table
    if ($newPassword) {
      $stmtUser = $pdo->prepare("UPDATE users SET role=?, status=?, password=? WHERE employee_id=?");
      $stmtUser->execute([$role, $status, $newPassword, $empId]);
    } else {
      $stmtUser = $pdo->prepare("UPDATE users SET role=?, status=? WHERE employee_id=?");
      $stmtUser->execute([$role, $status, $empId]);
    }

    $pdo->commit();
    $successMsg = "Employee updated successfully.";
    
    // refresh data to show updates immediately
    $employee['first_name'] = $first_name;
    $employee['last_name'] = $last_name;
    $employee['contact_number'] = $contact;
    $employee['position'] = $position;
    $employee['daily_rate'] = $daily_rate;
    $employee['role'] = $role;
    $employee['user_status'] = $status;

  } catch (Exception $e) {
    $pdo->rollBack();
    $errorMsg = "Failed to update: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Employee | KakaiOne</title>
  <?php include '../includes/links.php'; ?>
</head>
<body class="bg-light">

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Employee</h3>
    <a href="employee_module.php" class="btn btn-secondary">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <?php if ($successMsg): ?>
        <div class="alert alert-success"><?= $successMsg ?></div>
      <?php elseif ($errorMsg): ?>
        <div class="alert alert-danger"><?= $errorMsg ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">First Name</label>
            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($employee['first_name']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Last Name</label>
            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($employee['last_name']) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Contact Number</label>
            <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($employee['contact_number'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Daily Rate (₱)</label>
            <input type="number" step="0.01" name="daily_rate" class="form-control" value="<?= htmlspecialchars($employee['daily_rate'] ?? '500.00') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Position</label>
            <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($employee['position']) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Role</label>
            <select name="role" class="form-select" required>
              <option value="Employee" <?= $employee['role']=='Employee'?'selected':'' ?>>Employee</option>
              <option value="Admin" <?= $employee['role']=='Admin'?'selected':'' ?>>Admin</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select">
              <option value="Active" <?= $employee['user_status']=='Active'?'selected':'' ?>>Active</option>
              <option value="Inactive" <?= $employee['user_status']=='Inactive'?'selected':'' ?>>Inactive</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Reset Password (Optional)</label>
            <input type="password" name="password" class="form-control" placeholder="Enter new password">
          </div>

          <div class="col-md-12 mt-4">
            <button type="submit" class="btn btn-warning fw-semibold px-4">
              <i class="bi bi-save"></i> Save Changes
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

</body>
</html>