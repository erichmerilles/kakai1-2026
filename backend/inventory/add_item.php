<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../frontend/includes/auth_check.php';
require_once __DIR__ . '/../utils/logger.php';

header('Content-Type: application/json');

// check permissions
if (!hasPermission('inv_add')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: You do not have permission to add inventory items.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$item_name     = trim($_POST['item_name'] ?? '');
$category_id   = $_POST['category_id'] ?? null;
$quantity      = $_POST['quantity'] ?? 0;
$unit_price    = $_POST['unit_price'] ?? 0;
$reorder_level = $_POST['reorder_level'] ?? 10;
$supplier_id   = $_POST['supplier_id'] ?? null;

if (empty($item_name)) {
    echo json_encode(["success" => false, "message" => "Item name is required"]);
    exit;
}

try {
    $stmt = $conn->prepare("
        INSERT INTO inventory 
        (item_name, category_id, quantity, unit_price, reorder_level, supplier_id, status)
        VALUES (?, ?, ?, ?, ?, ?, 
            CASE 
                WHEN ? <= 0 THEN 'Out of Stock'
                WHEN ? <= ? THEN 'Low Stock'
                ELSE 'Available'
            END
        )
    ");

    $stmt->bind_param(
        "siidiiii",
        $item_name,
        $category_id,
        $quantity,
        $unit_price,
        $reorder_level,
        $supplier_id,
        $quantity,
        $quantity,
        $reorder_level
    );

    if ($stmt->execute()) {
        // log activity
        logActivity($pdo, $_SESSION['user_id'], 'Create', 'Inventory', "Added new inventory item: $item_name (Qty: $quantity)");

        echo json_encode(["success" => true, "message" => "Item added successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Insert failed: " . $stmt->error]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
