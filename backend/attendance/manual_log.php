<?php
session_start();
date_default_timezone_set('Asia/Manila');

require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

// role validation
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$employee_id = $_POST['employee_id'] ?? null;
$time_in = $_POST['time_in'] ?? null;
$time_out = $_POST['time_out'] ?? null;
$manual_status = $_POST['status'] ?? 'Present';

if (!$employee_id || !$time_in) {
    echo json_encode(['success' => false, 'message' => 'Employee and Time In are required.']);
    exit;
}

try {
    $timeInParsed = strtotime($time_in);
    $timeInHourMinute = date('H:i', $timeInParsed);

    // auto determine status based on time_in
    $status = ($timeInHourMinute > '07:00') ? 'Late' : 'Present';

    $totalHours = 0;
    $finalTimeOut = null;

    if (!empty($time_out)) {
        $t2 = strtotime($time_out);
        if ($t2 > $timeInParsed) {
            $diff = $t2 - $timeInParsed;
            $totalHours = round($diff / 3600, 2);
            $finalTimeOut = $time_out;
        } else {
            echo json_encode(['success' => false, 'message' => 'Time Out cannot be before Time In.']);
            exit;
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO attendance (employee_id, time_in, time_out, status, total_hours, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$employee_id, $time_in, $finalTimeOut, $status, $totalHours]);

    echo json_encode(['success' => true, 'message' => 'Manual attendance log saved successfully.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
