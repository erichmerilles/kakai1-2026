<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid attendance ID.']);
    exit;
}

try {
    // discards pending OT by setting it to 0
    $stmt = $pdo->prepare("
        UPDATE attendance 
        SET pending_overtime = 0 
        WHERE attendance_id = ?
    ");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Overtime declined. Regular hours capped at 5 PM.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record not found or already updated.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
