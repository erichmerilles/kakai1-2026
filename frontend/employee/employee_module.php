<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// set active module
$activeModule = 'employee';

// role validation
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
  header('Location: ../../index.php');
  exit;
}

// fetch employees
try {
  $stmt = $pdo->prepare("
      SELECT employee_id, first_name, last_name, position, status, date_hired, contact_number, email
      FROM employees 
      WHERE role = 'Employee' 
      ORDER BY date_hired DESC
    ");
  $stmt->execute();
  $employees = $stmt->fetchAll();
} catch (PDOException $e) {
  $employees = [];
}

// attendance summary
$attendanceStats = ['on_time' => 0, 'late' => 0, 'absent' => 0];
try {
  $stmt = $pdo->query("SELECT status, COUNT(*) AS total FROM attendance GROUP BY status");
  while ($row = $stmt->fetch()) {
    $key = strtolower(str_replace(' ', '_', $row['status']));
    if (isset($attendanceStats[$key])) {
      $attendanceStats[$key] = $row['total'];
    }
  }
} catch (PDOException $e) {
}

// leave requests
$leaveRequests = [];
try {
  $stmt = $pdo->query("
      SELECT lr.leave_id, u.username, lr.leave_type, lr.start_date, lr.end_date, lr.status
      FROM leave_requests lr
      JOIN users u ON lr.employee_id = u.employee_id
      WHERE lr.status = 'Pending'
    ");
  $leaveRequests = $stmt->fetchAll();
} catch (PDOException $e) {
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="icon" type="image/png" href="../assets/images/logo.png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KakaiOne | Employee Management</title>
  <?php include '../includes/links.php'; ?>
</head>

<body>

  <?php include '../includes/sidebar.php'; ?>

  <div id="dashboardContainer">
    <main id="main-content" style="margin-left: 250px; padding: 25px; transition: margin-left 0.3s;">
      <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="fw-bold">
            <i class="bi bi-people-fill me-2 text-warning"></i>Employee Management
          </h3>
          <div class="d-flex flex-column gap-3" style="max-width: 250px;">
            <a href="employee_form.php" class="btn btn-warning">
              <i class="bi bi-person-plus"></i> Add Employee
            </a>
          </div>
        </div>

        <div class="card mb-4 shadow-sm">
          <div class="card-header bg-dark text-white">
            <i class="bi bi-list-ul me-2"></i>Employee Directory
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped align-middle">
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Position</th>
                    <th>Contact #</th>
                    <th>Status</th>
                    <th>Date Hired</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($employees)): ?>
                    <?php foreach ($employees as $emp): ?>
                      <tr>
                        <td><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                        <td><?= htmlspecialchars($emp['position']); ?></td>
                        <td><?= htmlspecialchars($emp['contact_number'] ?? 'N/A'); ?></td>
                        <td>
                          <span class="badge bg-<?= $emp['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                            <?= htmlspecialchars($emp['status']); ?>
                          </span>
                        </td>
                        <td><?= date('Y-m-d', strtotime($emp['date_hired'])); ?></td>
                        <td>
                          <button class="btn btn-sm btn-info text-white" onclick="viewEmployee(<?= $emp['employee_id']; ?>)" title="View">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button class="btn btn-sm btn-warning" onclick="editEmployee(<?= $emp['employee_id']; ?>)" title="Edit">
                            <i class="bi bi-pencil"></i>
                          </button>
                          <button class="btn btn-sm btn-danger" onclick="confirmDeactivate(<?= $emp['employee_id']; ?>)" title="Deactivate">
                            <i class="bi bi-x-circle"></i>
                          </button>
                          <a href="access_control.php?id=<?= $emp['employee_id']; ?>" class="btn btn-sm btn-dark" title="Access Control">
                            <i class="bi bi-shield-lock"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="6" class="text-center text-muted">No employees found.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="card mb-4 shadow-sm">
          <div class="card-header bg-dark text-white">
            <i class="bi bi-bar-chart me-2"></i>Attendance Summary
          </div>
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-8">
                <canvas id="attendanceChart" height="100"></canvas>
              </div>
              <div class="col-md-4">
                <p><i class="bi bi-check-circle text-success"></i> On-Time: <?= $attendanceStats['on_time']; ?></p>
                <p><i class="bi bi-clock text-warning"></i> Late: <?= $attendanceStats['late']; ?></p>
                <p><i class="bi bi-x-circle text-danger"></i> Absent: <?= $attendanceStats['absent']; ?></p>
              </div>
            </div>
          </div>
        </div>

        <div class="card mb-4 shadow-sm">
          <div class="card-header bg-dark text-white">
            <i class="bi bi-envelope-paper me-2"></i>Leave Requests
          </div>
          <div class="card-body">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Employee</th>
                  <th>Type</th>
                  <th>Date Requested</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($leaveRequests)): ?>
                  <?php foreach ($leaveRequests as $req): ?>
                    <tr>
                      <td><?= htmlspecialchars($req['username']); ?></td>
                      <td><?= htmlspecialchars($req['leave_type']); ?></td>
                      <td><?= htmlspecialchars($req['start_date'] . " to " . $req['end_date']); ?></td>
                      <td>
                        <a href="../../backend/employee/approve_request.php?id=<?= $req['leave_id']; ?>" class="btn btn-sm btn-success"><i class="bi bi-check-circle"></i></a>
                        <a href="../../backend/employee/decline_request.php?id=<?= $req['leave_id']; ?>" class="btn btn-sm btn-danger"><i class="bi bi-x-circle"></i></a>
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

      </div>
    </main>
  </div>

  <div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title"><i class="bi bi-person-badge me-2"></i>Employee Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
            <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 80px; height: 80px; font-size: 2rem;">
              <i class="bi bi-person"></i>
            </div>
            <h5 class="mt-2 fw-bold" id="view_fullname">Loading...</h5>
            <p class="text-muted mb-0" id="view_position">Loading...</p>
            <span class="badge bg-success" id="view_status">Active</span>
          </div>
          <hr>
          <div class="row g-3">
            <div class="col-6">
              <small class="text-muted">Email Address</small>
              <p class="fw-bold mb-0" id="view_email">-</p>
            </div>
            <div class="col-6">
              <small class="text-muted">Contact Number</small>
              <p class="fw-bold mb-0" id="view_contact">-</p>
            </div>
            <div class="col-6">
              <small class="text-muted">Date Hired</small>
              <p class="fw-bold mb-0" id="view_hired">-</p>
            </div>
            <div class="col-6">
              <small class="text-muted">Daily Rate</small>
              <p class="fw-bold mb-0 text-success" id="view_rate">-</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h5 class="modal-title text-dark"><i class="bi bi-pencil-square me-2"></i>Edit Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form id="editEmployeeForm">
          <div class="modal-body">
            <input type="hidden" name="employee_id" id="edit_employee_id">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="edit_email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" id="edit_contact_number" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">Position</label>
                <input type="text" name="position" id="edit_position" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" id="edit_status" class="form-select">
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                  <option value="On Leave">On Leave</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Daily Rate (₱)</label>
                <input type="number" step="0.01" name="daily_rate" id="edit_daily_rate" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Date Hired</label>
                <input type="date" name="date_hired" id="edit_date_hired" class="form-control" required>
              </div>

              <div class="col-12 mt-3">
                <hr>
                <p class="small text-muted mb-2">Leave password blank if you don't want to change it.</p>
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" placeholder="********">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // view modal
    async function viewEmployee(id) {
      const modal = new bootstrap.Modal(document.getElementById('viewEmployeeModal'));
      modal.show();

      try {
        const res = await fetch(`../../backend/employee/get_employee.php?id=${id}`);
        const json = await res.json();

        if (json.success) {
          const data = json.data;
          document.getElementById('view_fullname').innerText = `${data.first_name} ${data.last_name}`;
          document.getElementById('view_position').innerText = data.position;

          const badge = document.getElementById('view_status');
          badge.innerText = data.status;
          badge.className = `badge bg-${data.status === 'Active' ? 'success' : 'secondary'}`;

          document.getElementById('view_email').innerText = data.email || 'N/A';
          document.getElementById('view_contact').innerText = data.contact_number || 'N/A';
          document.getElementById('view_hired').innerText = data.date_hired;
          document.getElementById('view_rate').innerText = '₱' + parseFloat(data.daily_rate || 0).toFixed(2);
        }
      } catch (error) {
        console.error(error);
        alert('Failed to fetch details.');
      }
    }

    // edit modal
    async function editEmployee(id) {
      try {
        const res = await fetch(`../../backend/employee/get_employee.php?id=${id}`);
        const json = await res.json();

        if (json.success) {
          const data = json.data;

          // prefill form
          document.getElementById('edit_employee_id').value = data.employee_id;
          document.getElementById('edit_first_name').value = data.first_name;
          document.getElementById('edit_last_name').value = data.last_name;
          document.getElementById('edit_email').value = data.email || '';
          document.getElementById('edit_contact_number').value = data.contact_number || '';
          document.getElementById('edit_position').value = data.position;
          document.getElementById('edit_status').value = data.status;
          document.getElementById('edit_daily_rate').value = data.daily_rate;
          document.getElementById('edit_date_hired').value = data.date_hired;

          // show modal
          const modal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
          modal.show();
        }
      } catch (error) {
        console.error(error);
        alert('Failed to load for editing.');
      }
    }

    // handle form submit
    document.getElementById('editEmployeeForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const formData = new FormData(this);

      try {
        const res = await fetch('../../backend/employee/update_employee.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: data.message,
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message
          });
        }
      } catch (err) {
        console.error(err);
        Swal.fire('Error', 'Something went wrong.', 'error');
      }
    });

    // deactivate logic
    function confirmDeactivate(empId) {
      Swal.fire({
        title: 'Deactivate Employee?',
        text: "This will disable the employee's account.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, deactivate'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = `../../backend/employee/delete_employee.php?id=${empId}`;
        }
      });
    }

    // chart logic
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['On Time', 'Late', 'Absent'],
        datasets: [{
          data: [<?= $attendanceStats['on_time']; ?>, <?= $attendanceStats['late']; ?>, <?= $attendanceStats['absent']; ?>],
          backgroundColor: ['#198754', '#ffc107', '#dc3545']
        }]
      },
      options: {
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>

</body>

</html>

<!-- old code -->

<?php
/*session_start();
require_once __DIR__ . '/../../config/db.php';
include '../includes/links.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
  header('Location: ../auth/login.php');
  exit;
}

// Fetch Employees
$employeeQuery = $conn->query("
  SELECT e.employee_id, e.first_name, e.last_name, e.position, e.status, e.date_hired
  FROM employees e
  ORDER BY e.date_hired DESC
");
$employees = $employeeQuery ? $employeeQuery->fetch_all(MYSQLI_ASSOC) : [];

// Attendance Summary
$attendanceStats = ['on_time' => 0, 'late' => 0, 'absent' => 0];
$aQuery = $conn->query("SELECT status, COUNT(*) AS total FROM attendance GROUP BY status");
if ($aQuery) {
  while ($row = $aQuery->fetch_assoc()) {
    $attendanceStats[strtolower(str_replace(' ', '_', $row['status']))] = $row['total'];
  }
}

// Leave Requests
$reqQuery = $conn->query("
  SELECT lr.leave_id, u.username, lr.leave_type, lr.start_date, lr.end_date, lr.status
  FROM leave_requests lr
  JOIN users u ON lr.employee_id = u.employee_id
  WHERE lr.status = 'Pending'
");
$leaveRequests = $reqQuery ? $reqQuery->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KakaiOne | Employee Management</title>
  <?php include '../includes/links.php'; ?>
  <?php include 'e_sidebar.php'; ?>
</head>
<body>

    <!-- MAIN CONTENT -->
<div id="dashboardContainer">
    <main id="main-content">

      <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">
              <i class="bi bi-people-fill me-2 text-warning"></i>Employee Management
            </h3>

            <div class="d-flex flex-column gap-3" style="max-width: 250px;">
              <a href="../dashboard/admin_dashboard.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
              </a>
              <a href="employee_form.php" class="btn btn-warning">
                <i class="bi bi-person-plus"></i> Add Employee
              </a>
            </div>
        </div>

        <!-- EMPLOYEE DIRECTORY -->
        <div class="card mb-4 shadow-sm">
          <div class="card-header bg-dark text-white">
            <i class="bi bi-list-ul me-2"></i>Employee Directory
          </div>

          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped align-middle">
                <thead>
                  <tr>
                    <!--<th>ID</th>-->
                    <th>Full Name</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Date Hired</th>
                    <th>Action</th>
                  </tr>
                </thead>

                <tbody>
                  <?php if (!empty($employees)): ?>
                    <?php foreach ($employees as $emp): ?>
                      <tr>
                        <!--<td><?= $emp['employee_id']; ?></td>-->
                        <td><?= $emp['first_name'].' '.$emp['last_name']; ?></td>
                        <td><?= $emp['position']; ?></td>

                        <td>
                          <span class="badge bg-<?= $emp['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                            <?= $emp['status']; ?>
                          </span>
                        </td>

                        <td><?= date('Y-m-d', strtotime($emp['date_hired'])); ?></td>

                        <td>
                          <a href="view_employee.php?id=<?= $emp['employee_id']; ?>" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i>
                          </a>
                          <a href="employee_form.php?id=<?= $emp['employee_id']; ?>" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                          </a>
                          <button class="btn btn-sm btn-danger" onclick="confirmDeactivate(<?= $emp['employee_id']; ?>)">
                          <i class="bi bi-x-circle"></i>
                          </button>
                          <a href="access_control.php?id=<?= $row['employee_id']; ?>" class="btn btn-sm btn-warning">
                              <i class="bi bi-shield-lock"></i> Access
                          </a>
                        </td>

                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                      <tr><td colspan="6" class="text-center">No employees found.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ATTENDANCE SUMMARY -->
        <div class="card mb-4 shadow-sm">
          <div class="card-header bg-dark text-white">
            <i class="bi bi-bar-chart me-2"></i>Attendance Summary
          </div>

          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-8">
                <canvas id="attendanceChart" height="100"></canvas>
              </div>

              <div class="col-md-4">
                <p><i class="bi bi-check-circle text-success"></i> On-Time: <?= $attendanceStats['on_time']; ?></p>
                <p><i class="bi bi-clock text-warning"></i> Late: <?= $attendanceStats['late']; ?></p>
                <p><i class="bi bi-x-circle text-danger"></i> Absent: <?= $attendanceStats['absent']; ?></p>
              </div>
            </div>
          </div>
        </div>

        <!-- LEAVE REQUESTS -->
        <div class="card mb-4 shadow-sm">
          <div class="card-header bg-dark text-white">
            <i class="bi bi-envelope-paper me-2"></i>Leave Requests
          </div>

          <div class="card-body">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Employee</th>
                  <th>Type</th>
                  <th>Date Requested</th>
                  <th>Action</th>
                </tr>
              </thead>

              <tbody>
                <?php if (!empty($leaveRequests)): ?>
                  <?php foreach ($leaveRequests as $req): ?>
                    <tr>
                      <td><?= $req['username']; ?></td>
                      <td><?= $req['leave_type']; ?></td>
                      <td><?= $req['start_date']." to ".$req['end_date']; ?></td>

                      <td>
                        <a href="../../backend/employees/approve_request.php?id=<?= $req['leave_id']; ?>" class="btn btn-sm btn-success"><i class="bi bi-check-circle"></i></a>
                        <a href="../../backend/employees/decline_request.php?id=<?= $req['leave_id']; ?>" class="btn btn-sm btn-danger"><i class="bi bi-x-circle"></i></a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="4" class="text-center">No pending requests.</td></tr>
                <?php endif; ?>
              </tbody>

            </table>
          </div>
        </div>

      </div>

    </main>

</div>

<script>
const ctx = document.getElementById('attendanceChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['On Time', 'Late', 'Absent'],
    datasets: [{
      data: [<?= $attendanceStats['on_time']; ?>, <?= $attendanceStats['late']; ?>, <?= $attendanceStats['absent']; ?>],
      backgroundColor: ['#198754', '#ffc107', '#dc3545']
    }]
  },
  options: { plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
});

function confirmDeactivate(empId) {
  Swal.fire({
    title: 'Deactivate Employee?',
    text: "This will disable the employee's account but keep their record in the system.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, deactivate',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = `employee_form.php?id=${empId}&action=deactivate`;
    }
  });
}

</script>

</body>
</html>*/
?>