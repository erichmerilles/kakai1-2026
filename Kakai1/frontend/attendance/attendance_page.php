<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// role validation
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}
requirePermission('att_view'); // permission check
$activeModule = 'employee';
$filterDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// fetch attendance logs
$attendanceLogs = [];
try {
    // join with employees table to get names
    $stmt = $pdo->prepare("
        SELECT a.attendance_id, e.first_name, e.last_name, e.employee_code, 
               a.time_in, a.time_out, a.status, a.total_hours
        FROM attendance a
        JOIN employees e ON a.employee_id = e.employee_id
        WHERE DATE(a.time_in) = ?
        ORDER BY a.time_in DESC
    ");
    $stmt->execute([$filterDate]);
    $attendanceLogs = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorMsg = "Error fetching data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Log | KakaiOne</title>
    <?php include '../includes/links.php'; ?>
</head>

<body class="bg-light">

    <?php include '../includes/sidebar.php'; ?>

    <main id="main-content" style="margin-left: 250px; padding: 25px; transition: margin-left 0.3s;">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark"><i class="bi bi-calendar-check-fill me-2 text-warning"></i>Attendance Log</h3>
                    <p class="text-muted mb-0">Records for <strong><?= date('F j, Y', strtotime($filterDate)) ?></strong></p>
                </div>

                <form method="GET" class="d-flex gap-2">
                    <input type="date" name="date" class="form-control" value="<?= $filterDate ?>" onchange="this.form.submit()">
                    <button type="submit" class="btn btn-warning text-dark fw-semibold"><i class="bi bi-search"></i></button>
                    <?php if ($filterDate !== date('Y-m-d')): ?>
                        <a href="attendance_page.php" class="btn btn-secondary" title="Reset to Today"><i class="bi bi-arrow-counterclockwise"></i></a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="ps-4 py-3">Employee</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Total Hours</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($attendanceLogs)): ?>
                                    <?php foreach ($attendanceLogs as $log): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 40px; height: 40px;">
                                                        <?= strtoupper(substr($log['first_name'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?></h6>
                                                        <small class="text-muted"><?= htmlspecialchars($log['employee_code']) ?></small>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="fw-medium text-dark">
                                                    <?= date('h:i A', strtotime($log['time_in'])) ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?php if ($log['time_out']): ?>
                                                    <span class="fw-medium text-dark">
                                                        <?= date('h:i A', strtotime($log['time_out'])) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary text-light">-- : --</span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?= $log['total_hours'] ? number_format($log['total_hours'], 2) . ' hrs' : '-' ?>
                                            </td>

                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                if ($log['status'] === 'Present') $statusClass = 'success';
                                                elseif ($log['status'] === 'Late') $statusClass = 'warning';
                                                elseif ($log['status'] === 'Absent') $statusClass = 'danger';
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?> rounded-pill px-3">
                                                    <?= htmlspecialchars($log['status']) ?>
                                                </span>
                                            </td>

                                            <td class="text-end pe-4">
                                                <?php if (hasPermission('att_approve')): ?>
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="editAttendance(<?= $log['attendance_id'] ?>)" title="Edit Record">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-calendar-x display-6 d-block mb-3 opacity-50"></i>
                                            No attendance records found for this date.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editAttendanceForm">
                    <div class="modal-body">
                        <input type="hidden" name="attendance_id" id="edit_attendance_id">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Time In</label>
                            <input type="datetime-local" name="time_in" id="edit_time_in" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Time Out</label>
                            <input type="datetime-local" name="time_out" id="edit_time_out" class="form-control">
                            <small class="text-muted">Clear this if the employee hasn't clocked out yet.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="Present">Present</option>
                                <option value="Late">Late</option>
                                <option value="Absent">Absent</option>
                                <option value="Half Day">Half Day</option>
                            </select>
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
        // open modal and fetch data
        async function editAttendance(id) {
            try {
                // fetch record details
                const res = await fetch(`../../backend/attendance/get_attendance.php?id=${id}`);
                const json = await res.json();

                if (json.success) {
                    const data = json.data;
                    document.getElementById('edit_attendance_id').value = data.attendance_id;

                    // value format
                    document.getElementById('edit_time_in').value = data.time_in_fmt;
                    document.getElementById('edit_time_out').value = data.time_out_fmt;
                    document.getElementById('edit_status').value = data.status;

                    const modal = new bootstrap.Modal(document.getElementById('editAttendanceModal'));
                    modal.show();
                } else {
                    Swal.fire('Error', json.message, 'error');
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Failed to fetch record details.', 'error');
            }
        }

        // hanlde form submit
        document.getElementById('editAttendanceForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            try {
                const res = await fetch('../../backend/attendance/update_attendance.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Something went wrong.', 'error');
            }
        });
    </script>

</body>

</html>