<?php
session_start();
date_default_timezone_set('Asia/Manila');

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

// calculate employee stats for KPI cards
$totalEmployees = count($employees);
$activeEmployees = 0;
foreach ($employees as $emp) {
  if (strtolower($emp['status']) === 'active') {
    $activeEmployees++;
  }
}

// daily attendance summary
$attendanceStats = ['on_time' => 0, 'late' => 0, 'absent' => 0];
$today = date('Y-m-d');

try {
  $stmt = $pdo->prepare("
        SELECT a.attendance_id, a.status
        FROM employees e
        LEFT JOIN attendance a ON a.attendance_id = (
            SELECT MAX(attendance_id) 
            FROM attendance sub_a 
            WHERE sub_a.employee_id = e.employee_id AND DATE(sub_a.time_in) = ?
        )
        WHERE e.status = 'Active' AND e.role = 'Employee'
  ");
  $stmt->execute([$today]);
  $dailyRecords = $stmt->fetchAll();

  foreach ($dailyRecords as $record) {
    if (empty($record['attendance_id'])) {
      // no attendance record means absent
      $attendanceStats['absent']++;
    } else {
      $status = strtolower($record['status']);
      if ($status === 'present') {
        $attendanceStats['on_time']++;
      } elseif ($status === 'late') {
        $attendanceStats['late']++;
      } elseif ($status === 'absent') {
        $attendanceStats['absent']++;
      }
    }
  }
} catch (PDOException $e) {
}

// fetch pending overtime requests
$pendingOTRequests = [];
try {
  $stmt = $pdo->query("
        SELECT a.attendance_id, e.first_name, e.last_name, a.time_in, a.pending_overtime 
        FROM attendance a
        JOIN employees e ON a.employee_id = e.employee_id
        WHERE a.pending_overtime > 0
        ORDER BY a.time_in ASC
    ");
  $pendingOTRequests = $stmt->fetchAll();
} catch (PDOException $e) {
}
$pendingOTCount = count($pendingOTRequests);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="icon" type="image/png" href="../assets/images/logo.png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KakaiOne | Employee Management</title>
  <?php include '../includes/links.php'; ?>
  <style>
    .stat-card {
      border-left: 4px solid;
      transition: transform 0.2s;
      border-radius: 8px;
    }

    .stat-card:hover {
      transform: translateY(-3px);
    }

    .border-left-primary {
      border-left-color: #0d6efd !important;
    }

    .border-left-success {
      border-left-color: #198754 !important;
    }

    .border-left-warning {
      border-left-color: #ffc107 !important;
    }

    .border-left-danger {
      border-left-color: #dc3545 !important;
    }

    @media print {

      #sidebar,
      .btn,
      .input-group,
      .chart-section {
        display: none !important;
      }

      #main-content {
        margin-left: 0 !important;
        padding: 0 !important;
      }

      .col-lg-8 {
        width: 100% !important;
      }
    }
  </style>
</head>

<body class="bg-light">

  <?php include '../includes/sidebar.php'; ?>

  <div id="dashboardContainer">
    <main id="main-content" style="margin-left: 250px; padding: 25px; transition: margin-left 0.3s;">
      <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-end mb-4">
          <div>
            <h3 class="fw-bold text-dark mb-1">
              <i class="bi bi-people-fill me-2 text-warning"></i>Employee Management
            </h3>
            <p class="text-muted mb-0">Manage staff records, access control, and overtime approvals.</p>
          </div>
          <div class="d-flex gap-2 text-end">
            <button type="button" class="btn btn-warning shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
              <i class="bi bi-person-plus me-1"></i> Add Employee
            </button>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm border-left-primary h-100 py-2">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Employees</div>
                    <div class="h4 mb-0 fw-bold text-dark"><?= $totalEmployees; ?></div>
                  </div>
                  <div class="col-auto"><i class="bi bi-people fa-2x text-primary opacity-50 fs-1"></i></div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm border-left-success h-100 py-2">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs fw-bold text-success text-uppercase mb-1">Active Staff</div>
                    <div class="h4 mb-0 fw-bold text-dark"><?= $activeEmployees; ?></div>
                  </div>
                  <div class="col-auto"><i class="bi bi-person-check fa-2x text-success opacity-50 fs-1"></i></div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm border-left-danger h-100 py-2">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs fw-bold text-danger text-uppercase mb-1">Today's Lates</div>
                    <div class="h4 mb-0 fw-bold text-dark"><?= $attendanceStats['late']; ?></div>
                  </div>
                  <div class="col-auto"><i class="bi bi-clock-history fa-2x text-danger opacity-50 fs-1"></i></div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm border-left-warning h-100 py-2">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending Overtime</div>
                    <div class="h4 mb-0 fw-bold text-dark"><?= $pendingOTCount; ?></div>
                  </div>
                  <div class="col-auto"><i class="bi bi-clock fa-2x text-warning opacity-50 fs-1"></i></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">

          <div class="col-lg-8">
            <div class="card mb-4 shadow-sm border-0">
              <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <span class="fw-bold"><i class="bi bi-list-ul me-2"></i>Employee Directory</span>
                <div class="input-group input-group-sm w-50">
                  <input type="text" id="employeeSearch" class="form-control" placeholder="Search employees...">
                  <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                </div>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-striped align-middle mb-0" id="employeeTable">
                    <thead class="table-light">
                      <tr>
                        <th class="ps-4"><i class="bi bi-person-circle"></i> Full Name</th>
                        <th><i class="bi bi-briefcase"></i> Position</th>
                        <th><i class="bi bi-telephone"></i> Contact #</th>
                        <th><i class="bi bi-activity"></i> Status</th>
                        <th><i class="bi bi-calendar-event"></i> Date Hired</th>
                        <th class="text-end pe-4"><i class="bi bi-gear"></i> Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($employees)): ?>
                        <?php foreach ($employees as $emp): ?>
                          <tr>
                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                            <td><?= htmlspecialchars($emp['position']); ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($emp['contact_number'] ?? 'N/A'); ?></td>
                            <td>
                              <span class="badge bg-<?= (strtolower($emp['status']) === 'active') ? 'success' : 'secondary'; ?>">
                                <?= htmlspecialchars($emp['status']); ?>
                              </span>
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($emp['date_hired'])); ?></td>
                            <td class="text-end pe-4">
                              <button class="btn btn-sm btn-info text-white shadow-sm" onclick="viewEmployee(<?= $emp['employee_id']; ?>)" title="View">
                                <i class="bi bi-eye"></i>
                              </button>
                              <button class="btn btn-sm btn-warning shadow-sm" onclick="editEmployee(<?= $emp['employee_id']; ?>)" title="Edit">
                                <i class="bi bi-pencil"></i>
                              </button>
                              <button class="btn btn-sm btn-danger shadow-sm" onclick="confirmDeactivate(<?= $emp['employee_id']; ?>)" title="Deactivate">
                                <i class="bi bi-x-circle"></i>
                              </button>
                              <a href="access_control.php?id=<?= $emp['employee_id']; ?>" class="btn btn-sm btn-dark shadow-sm" title="Access Control">
                                <i class="bi bi-shield-lock"></i>
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="6" class="text-center text-muted py-4">No employees found.</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4 chart-section">

            <div class="card mb-4 shadow-sm border-0">
              <div class="card-header bg-dark text-white py-3">
                <span class="fw-bold"><i class="bi bi-pie-chart-fill me-2"></i>Today's Attendance</span>
              </div>
              <div class="card-body">
                <div class="d-flex justify-content-center mb-3">
                  <canvas id="attendanceChart" style="max-height: 200px;"></canvas>
                </div>
                <div class="d-flex justify-content-around text-center border-top pt-3">
                  <div>
                    <h5 class="fw-bold text-success mb-0"><?= $attendanceStats['on_time']; ?></h5>
                    <small class="text-muted">On Time</small>
                  </div>
                  <div>
                    <h5 class="fw-bold text-warning mb-0"><?= $attendanceStats['late']; ?></h5>
                    <small class="text-muted">Late</small>
                  </div>
                  <div>
                    <h5 class="fw-bold text-danger mb-0"><?= $attendanceStats['absent']; ?></h5>
                    <small class="text-muted">Absent</small>
                  </div>
                </div>
              </div>
            </div>

            <div class="card mb-4 shadow-sm border-warning">
              <div class="card-header bg-warning text-dark py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-clock-history me-2"></i>Pending Overtime</span>
                <span class="badge bg-dark rounded-pill"><?= $pendingOTCount ?></span>
              </div>
              <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                  <?php if (!empty($pendingOTRequests)): ?>
                    <?php foreach ($pendingOTRequests as $ot): ?>
                      <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <div>
                          <h6 class="mb-1 fw-bold text-dark"><?= htmlspecialchars($ot['first_name'] . ' ' . $ot['last_name']) ?></h6>
                          <div class="small text-muted mt-1">
                            <?= date('M d, Y', strtotime($ot['time_in'])) ?> &bull;
                            <strong class="text-danger"><?= number_format($ot['pending_overtime'], 2) ?> hrs excess</strong>
                          </div>
                        </div>
                        <div class="d-flex flex-column gap-2 pe-1">
                          <a href="../attendance/attendance_page.php" class="btn btn-sm btn-outline-primary" title="Review in Attendance">Review <i class="bi bi-arrow-right-short"></i></a>
                        </div>
                      </li>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <li class="list-group-item text-center text-muted py-4">
                      <i class="bi bi-check2-circle fs-3 d-block text-success opacity-50 mb-2"></i>
                      No pending overtime to review.
                    </li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>

          </div>
        </div>

      </div>
    </main>
  </div>

  <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content shadow">
        <div class="modal-header bg-warning">
          <h5 class="modal-title fw-bold text-dark"><i class="bi bi-person-plus me-2"></i>Add New Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form id="addEmployeeForm">
          <div class="modal-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">First Name <span class="text-danger">*</span></label>
                <input type="text" name="first_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Contact Number</label>
                <input type="text" name="contact_number" class="form-control">
              </div>

              <div class="col-12 mt-4">
                <hr class="text-muted opacity-25">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Position <span class="text-danger">*</span></label>
                <input type="text" name="position" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Status</label>
                <select name="status" class="form-select fw-bold">
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Base Hourly Rate (₱) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light">₱</span>
                  <input type="number" step="0.01" name="daily_rate" class="form-control" required>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Date Hired <span class="text-danger">*</span></label>
                <input type="date" name="date_hired" class="form-control" value="<?= date('Y-m-d') ?>" required>
              </div>

              <div class="col-12 mt-4">
                <div class="bg-light p-3 rounded border">
                  <label class="form-label fw-bold text-dark"><i class="bi bi-shield-lock me-1"></i> Initial Password <span class="text-danger">*</span></label>
                  <input type="password" name="password" class="form-control" placeholder="Set a temporary password" required>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer bg-light border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning fw-bold text-dark px-4"><i class="bi bi-plus-circle me-1"></i> Add Employee</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content shadow">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title fw-bold"><i class="bi bi-person-badge me-2"></i>Employee Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="text-center mb-4">
            <div class="bg-secondary bg-opacity-25 rounded-circle d-inline-flex align-items-center justify-content-center text-primary mb-2 shadow-sm" style="width: 80px; height: 80px; font-size: 2.5rem;">
              <i class="bi bi-person-fill"></i>
            </div>
            <h4 class="fw-bold mb-0 text-dark" id="view_fullname">Loading...</h4>
            <p class="text-muted mb-2" id="view_position">Loading...</p>
            <span class="badge bg-success px-3 py-1 rounded-pill" id="view_status">Active</span>
          </div>

          <div class="bg-light rounded p-3 border">
            <div class="row g-3">
              <div class="col-6">
                <small class="text-muted d-block mb-1">Email Address</small>
                <span class="fw-bold text-dark" id="view_email">-</span>
              </div>
              <div class="col-6">
                <small class="text-muted d-block mb-1">Contact Number</small>
                <span class="fw-bold text-dark" id="view_contact">-</span>
              </div>
              <div class="col-6">
                <small class="text-muted d-block mb-1">Date Hired</small>
                <span class="fw-bold text-dark" id="view_hired">-</span>
              </div>
              <div class="col-6">
                <small class="text-muted d-block mb-1">Base Rate</small>
                <span class="fw-bold text-success" id="view_rate">-</span>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light border-0">
          <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content shadow">
        <div class="modal-header bg-warning">
          <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square me-2"></i>Edit Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form id="editEmployeeForm">
          <div class="modal-body p-4">
            <input type="hidden" name="employee_id" id="edit_employee_id">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">First Name <span class="text-danger">*</span></label>
                <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="edit_email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Contact Number</label>
                <input type="text" name="contact_number" id="edit_contact_number" class="form-control">
              </div>

              <div class="col-12 mt-4">
                <hr class="text-muted opacity-25">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Position <span class="text-danger">*</span></label>
                <input type="text" name="position" id="edit_position" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Status</label>
                <select name="status" id="edit_status" class="form-select fw-bold">
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Base Hourly Rate (₱) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light">₱</span>
                  <input type="number" step="0.01" name="daily_rate" id="edit_daily_rate" class="form-control" required>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Date Hired <span class="text-danger">*</span></label>
                <input type="date" name="date_hired" id="edit_date_hired" class="form-control" required>
              </div>

              <div class="col-12 mt-4">
                <div class="bg-light p-3 rounded border">
                  <p class="small text-muted mb-2"><i class="bi bi-info-circle me-1"></i>Leave password blank if you don't want to change it.</p>
                  <label class="form-label fw-bold text-dark"><i class="bi bi-shield-lock me-1"></i> New Password</label>
                  <input type="password" name="password" class="form-control" placeholder="********">
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer bg-light border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning fw-bold text-dark px-4"><i class="bi bi-save me-1"></i> Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // search filter
    document.getElementById('employeeSearch').addEventListener('keyup', function() {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll('#employeeTable tbody tr');

      rows.forEach(row => {
        if (row.cells.length === 1) return; // skip empty row
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    // chart initialization
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['On Time', 'Late', 'Absent'],
        datasets: [{
          data: [<?= $attendanceStats['on_time']; ?>, <?= $attendanceStats['late']; ?>, <?= $attendanceStats['absent']; ?>],
          backgroundColor: ['#198754', '#ffc107', '#dc3545'],
          borderWidth: 0,
          hoverOffset: 4
        }]
      },
      options: {
        cutout: '70%',
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });

    // view modal logic
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
          badge.className = `badge px-3 py-1 rounded-pill bg-${data.status.toLowerCase() === 'active' ? 'success' : 'secondary'}`;

          document.getElementById('view_email').innerText = data.email || 'N/A';
          document.getElementById('view_contact').innerText = data.contact_number || 'N/A';
          document.getElementById('view_hired').innerText = data.date_hired;

          // Note: using 'daily_rate' field to store hourly rate due to db mapping
          document.getElementById('view_rate').innerText = '₱ ' + parseFloat(data.daily_rate || 0).toFixed(2) + ' / hr';
        }
      } catch (error) {
        console.error(error);
        alert('Failed to fetch details.');
      }
    }

    // edit modal logic
    async function editEmployee(id) {
      try {
        const res = await fetch(`../../backend/employee/get_employee.php?id=${id}`);
        const json = await res.json();

        if (json.success) {
          const data = json.data;
          document.getElementById('edit_employee_id').value = data.employee_id;
          document.getElementById('edit_first_name').value = data.first_name;
          document.getElementById('edit_last_name').value = data.last_name;
          document.getElementById('edit_email').value = data.email || '';
          document.getElementById('edit_contact_number').value = data.contact_number || '';
          document.getElementById('edit_position').value = data.position;
          document.getElementById('edit_status').value = data.status;
          document.getElementById('edit_daily_rate').value = data.daily_rate;
          document.getElementById('edit_date_hired').value = data.date_hired;

          const modal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
          modal.show();
        }
      } catch (error) {
        console.error(error);
        alert('Failed to load for editing.');
      }
    }

    // handle Add employee form submit
    document.getElementById('addEmployeeForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      try {
        const res = await fetch('../../backend/employee/add_employee.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Employee Added!',
            text: data.message,
            timer: 1500,
            showConfirmButton: false
          }).then(() => location.reload());
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message
          });
        }
      } catch (err) {
        console.error(err);
        Swal.fire('Error', 'Something went wrong while adding the employee.', 'error');
      }
    });

    // handle edit employee form submit
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
          }).then(() => location.reload());
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
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, deactivate'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = `../../backend/employee/delete_employee.php?id=${empId}`;
        }
      });
    }
  </script>
</body>

</html>