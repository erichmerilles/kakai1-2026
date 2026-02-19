<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

//set active module
$activeModule = 'dashboard';

// role validation
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
  header('Location: ../../index.php');
  exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// employee directory
$employees = [];
if (isset($conn)) {
  $employeeQuery = $conn->query("
      SELECT user_id, employee_id, username, role, status, created_at 
      FROM users 
      WHERE role = 'Employee'
    ");
  $employees = $employeeQuery ? $employeeQuery->fetch_all(MYSQLI_ASSOC) : [];
}

// attendance approval requests
$attendanceRequests = [];
if (isset($conn)) {
  $requestQuery = $conn->query("
      SELECT a.attendance_id, u.username AS name, a.status, a.created_at
      FROM attendance a
      JOIN users u ON u.employee_id = a.employee_id
      WHERE a.status = 'Pending Approval'
      ORDER BY a.created_at DESC
    ");
  $attendanceRequests = $requestQuery ? $requestQuery->fetch_all(MYSQLI_ASSOC) : [];
}

// notifications
$notifications = [];

if (isset($conn)) {
  // employees that are not logged in
  $notClockedQuery = $conn->query("
      SELECT username 
      FROM users 
      WHERE role = 'employee' 
      AND employee_id NOT IN (
        SELECT employee_id FROM attendance WHERE DATE(created_at) = CURDATE()
      )
    ");
  if ($notClockedQuery && $notClockedQuery->num_rows > 0) {
    $notifications[] = "{$notClockedQuery->num_rows} employee(s) have not clocked in today.";
  }

  // ongoing shifts
  $ongoingQuery = $conn->query("
      SELECT username 
      FROM users 
      WHERE employee_id IN (
        SELECT employee_id FROM attendance WHERE DATE(created_at) = CURDATE() AND time_out IS NULL
      )
    ");
  if ($ongoingQuery && $ongoingQuery->num_rows > 0) {
    $notifications[] = "{$ongoingQuery->num_rows} employee(s) have ongoing shifts.";
  }
}

if (empty($notifications)) {
  $notifications[] = "No new notifications.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="icon" type="image/png" href="../assets/images/logo.png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KakaiOne | Admin Dashboard</title>
  <?php include '../includes/links.php'; ?>
</head>

<body>

  <?php include '../includes/sidebar.php'; ?>

  <div id="dashboardContainer">
    <main id="main-content" style="margin-left: 250px; padding: 25px; transition: margin-left 0.3s;">

      <div class="container-fluid">
        <h3 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h3>

        <div class="module-card">
          <h5><i class="bi bi-people-fill me-2 text-primary"></i>Employee Directory</h5>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th><i class="bi bi-person-circle"></i> Username</th>
                  <th><i class="bi bi-briefcase"></i> Role</th>
                  <th><i class="bi bi-activity"></i> Status</th>
                  <th><i class="bi bi-calendar-event"></i> Created</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($employees)): ?>
                  <?php foreach ($employees as $emp): ?>
                    <tr>
                      <td><?= htmlspecialchars($emp['username']); ?></td>
                      <td><?= htmlspecialchars($emp['role']); ?></td>
                      <td>
                        <span class="badge bg-<?= $emp['status'] === 'active' ? 'success' : 'secondary'; ?>">
                          <?= ucfirst($emp['status']); ?>
                        </span>
                      </td>
                      <td><?= date('Y-m-d', strtotime($emp['created_at'])); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="text-center text-muted">No employees found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="module-card mt-4">
          <h5><i class="bi bi-envelope-paper me-2 text-warning"></i>Attendance Approval Requests</h5>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>Employee Name</th>
                  <th>Status</th>
                  <th>Date Requested</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($attendanceRequests)): ?>
                  <?php foreach ($attendanceRequests as $req): ?>
                    <tr>
                      <td><?= htmlspecialchars($req['name']); ?></td>
                      <td><?= htmlspecialchars($req['status']); ?></td>
                      <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($req['created_at']))); ?></td>
                      <td>
                        <button class="btn btn-sm btn-success"><i class="bi bi-check-circle"></i> Approve</button>
                        <button class="btn btn-sm btn-danger"><i class="bi bi-x-circle"></i> Decline</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="4" class="text-center text-muted">No pending requests.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="module-card mt-4">
          <h5><i class="bi bi-bell me-2 text-info"></i>Notifications / Reminders</h5>
          <ul class="list-group">
            <?php foreach ($notifications as $note): ?>
              <li class="list-group-item"><i class="bi bi-info-circle text-primary me-2"></i><?= htmlspecialchars($note); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

    </main>
  </div>

</body>

</html>