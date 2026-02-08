<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

// role validation
if (!isset($_SESSION['user_id']) || !isset($_SESSION['employee_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// allowed ip
$allowedIPs = [
    '123.456.78.90',  // actual ip address
    '::1',            // Localhost
    '127.0.0.1'       // Localhost
];

// get user IP address
$userIP = $_SERVER['REMOTE_ADDR'];

// for proxy ip
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $userIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

// validate IP
if (!in_array($userIP, $allowedIPs)) {
    echo json_encode([
        'success' => false,
        'message' => "Restricted Access! You must be connected to the office Wi-Fi. (Your IP: $userIP)"
    ]);
    exit;
}


$employee_id = $_SESSION['employee_id'];
$input = json_decode(file_get_contents("php://input"), true);
$type = $input['type'] ?? '';

if ($type === 'in') {
    // time in logic
    try {
        // check if clocked in 
        $stmt = $pdo->prepare("SELECT attendance_id FROM attendance WHERE employee_id = ? AND DATE(time_in) = CURDATE()");
        $stmt->execute([$employee_id]);

        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You have already clocked in today.']);
            exit;
        }

        // determine status
        $status = (date('H:i') > '09:00') ? 'Late' : 'Present';

        // insert record
        $insert = $pdo->prepare("
            INSERT INTO attendance (employee_id, time_in, status, created_at) 
            VALUES (?, NOW(), ?, NOW())
        ");
        $insert->execute([$employee_id, $status]);

        echo json_encode(['success' => true, 'message' => 'Time In recorded successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($type === 'out') {
    // time out logic
    try {
        $stmt = $pdo->prepare("
            SELECT attendance_id, time_in 
            FROM attendance 
            WHERE employee_id = ? AND DATE(time_in) = CURDATE() AND time_out IS NULL 
            LIMIT 1
        ");
        $stmt->execute([$employee_id]);
        $record = $stmt->fetch();

        if (!$record) {
            echo json_encode(['success' => false, 'message' => 'No active Time In record found for today.']);
            exit;
        }

        // Calculate Total Hours
        $timeIn = new DateTime($record['time_in']);
        $timeOut = new DateTime();
        $interval = $timeIn->diff($timeOut);
        $totalHours = $interval->h + ($interval->i / 60);

        // Update Record
        $update = $pdo->prepare("
            UPDATE attendance 
            SET time_out = NOW(), total_hours = ? 
            WHERE attendance_id = ?
        ");
        $update->execute([$totalHours, $record['attendance_id']]);

        echo json_encode(['success' => true, 'message' => 'Time Out recorded! See you tomorrow.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action type.']);
}
