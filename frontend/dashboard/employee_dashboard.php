<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// set active module
$activeModule = 'dashboard';

// role validation
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];

// check today attendance status
$attendanceStatus = 'clock_in';

try {
    // check for record created today
    $stmt = $pdo->prepare("
        SELECT * FROM attendance 
        WHERE employee_id = ? AND DATE(time_in) = CURDATE() 
        LIMIT 1
    ");
    $stmt->execute([$employee_id]);
    $todayLog = $stmt->fetch();

    if ($todayLog) {
        if ($todayLog['time_out'] === NULL) {
            $attendanceStatus = 'clock_out';
        } else {
            $attendanceStatus = 'completed';
        }
    }
} catch (PDOException $e) {
    // handle error
}

// fetch recent attendance history
try {
    $histStmt = $pdo->prepare("
        SELECT * FROM attendance 
        WHERE employee_id = ? 
        ORDER BY time_in DESC 
        LIMIT 5
    ");
    $histStmt->execute([$employee_id]);
    $history = $histStmt->fetchAll();
} catch (PDOException $e) {
    $history = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KakaiOne | Employee Dashboard</title>
    <?php include '../includes/links.php'; ?>
</head>

<body>

    <?php include '../includes/sidebar.php'; ?>

    <div id="dashboardContainer">
        <main id="main-content" style="margin-left: 250px; padding: 25px; transition: margin-left 0.3s;">
            <div class="container-fluid">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold mb-0">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Employee'); ?>!</h3>
                        <p class="text-muted">Here is your daily overview.</p>
                    </div>
                    <div>
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill">
                            <i class="bi bi-briefcase-fill me-1"></i> <?= htmlspecialchars($_SESSION['role'] ?? 'Staff'); ?>
                        </span>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="clock-card h-100 d-flex flex-column justify-content-center">
                            <div class="mb-2"><i class="bi bi-clock-history fs-1"></i></div>
                            <h5 class="text-uppercase tracking-wider">Current Time</h5>

                            <div class="digital-clock" id="liveClock">00:00:00 AM</div>
                            <div class="date-display" id="liveDate">Loading date...</div>

                            <div class="mt-3">
                                <?php if ($attendanceStatus === 'clock_in'): ?>
                                    <button onclick="handleAttendance('in')" class="btn btn-light text-success btn-attendance shadow">
                                        <i class="bi bi-play-circle-fill me-2"></i> Time In
                                    </button>
                                <?php elseif ($attendanceStatus === 'clock_out'): ?>
                                    <button onclick="handleAttendance('out')" class="btn btn-light text-danger btn-attendance shadow">
                                        <i class="bi bi-stop-circle-fill me-2"></i> Time Out
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-attendance disabled" disabled>
                                        <i class="bi bi-check-circle-fill me-2"></i> Completed
                                    </button>
                                    <p class="mt-2 small text-warning-light">You have completed your shift for today.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-journal-text me-2 text-warning"></i>Recent Attendance</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Date</th>
                                                <th>Time In</th>
                                                <th>Time Out</th>
                                                <th>Total Hrs</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($history)): ?>
                                                <?php foreach ($history as $log): ?>
                                                    <tr>
                                                        <td class="ps-4 fw-bold"><?= date('M d, Y', strtotime($log['time_in'])); ?></td>
                                                        <td class="text-success">
                                                            <?= date('h:i A', strtotime($log['time_in'])); ?>
                                                        </td>
                                                        <td class="text-danger">
                                                            <?= $log['time_out'] ? date('h:i A', strtotime($log['time_out'])) : '--:--'; ?>
                                                        </td>
                                                        <td>
                                                            <?= $log['total_hours'] ? number_format($log['total_hours'], 2) : '-'; ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $badge = 'secondary';
                                                            if ($log['status'] == 'Present') $badge = 'success';
                                                            if ($log['status'] == 'Late') $badge = 'warning';
                                                            ?>
                                                            <span class="badge bg-<?= $badge; ?>"><?= $log['status']; ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted">No attendance records yet.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white text-end border-0">
                                <a href="../attendance/my_attendance.php" class="btn btn-sm btn-outline-warning text-dark">View Full History <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        // live clock update
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            document.getElementById('liveClock').textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
            document.getElementById('liveDate').textContent = now.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // attendance logic 
        async function handleAttendance(type) {
            const actionText = type === 'in' ? 'Time In' : 'Time Out';
            const result = await Swal.fire({
                title: `Confirm ${actionText}?`,
                text: `Are you sure you want to ${actionText} now?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: `Yes, ${actionText}`
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('../../backend/attendance/log_attendance.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            type: type
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire('Success!', data.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Something went wrong', 'error');
                    }
                } catch (error) {
                    console.error(error);
                    Swal.fire('Error', 'Unable to connect to server.', 'error');
                }
            }
        }
    </script>
</body>

</html>