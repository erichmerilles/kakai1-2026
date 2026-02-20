<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

// check permissions
if (!hasPermission('payroll_view')) {
    echo json_encode(['success' => false]);
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT payroll_id, DATE_FORMAT(start_date, '%b %d, %Y') as start_date, DATE_FORMAT(end_date, '%b %d, %Y') as end_date, 
        DATE_FORMAT(created_at, '%M %d, %Y %h:%i %p') as created_at, total_gross, total_deductions, total_net, is_published 
        FROM payroll_runs ORDER BY payroll_id DESC
    ");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}