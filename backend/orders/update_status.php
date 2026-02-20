<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

// check permissions
if (!hasPermission('order_status_update')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: You do not have permission to update order statuses.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$order_id = intval($input['order_id'] ?? 0);
$status = trim($input['status'] ?? '');

if (!$order_id || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Missing order ID or status.']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param('si', $status, $order_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Order status updated successfully.']);
    } else {
        // returns true because if the status is same
        echo json_encode(['success' => true, 'message' => 'No changes made or order not found.']);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
