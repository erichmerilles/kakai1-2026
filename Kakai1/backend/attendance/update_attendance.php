<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

// role validation
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_POST['attendance_id'] ?? null;
$timeIn = $_POST['time_in'] ?? null;
$timeOut = $_POST['time_out'] ?? null;
$status = $_POST['status'] ?? 'Present';

if (!$id || !$timeIn) {
    echo json_encode(['success' => false, 'message' => 'Time In is required']);
    exit;
}

try {
    $totalHours = 0;
    $finalTimeOut = null;

    // calculate total hours after time out
    if (!empty($timeOut)) {
        $t1 = strtotime($timeIn);
        $t2 = strtotime($timeOut);

        if ($t2 > $t1) {
            $diff = $t2 - $t1;
            $totalHours = round($diff / 3600, 2);
            $finalTimeOut = $timeOut;
        } else {
            echo json_encode(['success' => false, 'message' => 'Time Out cannot be before Time In']);
            exit;
        }
    }

    $stmt = $pdo->prepare("
        UPDATE attendance 
        SET time_in = ?, time_out = ?, status = ?, total_hours = ? 
        WHERE attendance_id = ?
    ");

    $stmt->execute([$timeIn, $finalTimeOut, $status, $totalHours, $id]);

    echo json_encode(['success' => true, 'message' => 'Attendance updated successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
