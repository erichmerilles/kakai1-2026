<?php
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$item_name      = $_POST['item_name'] ?? '';
$category_id    = $_POST['category_id'] ?? null;
$quantity       = $_POST['quantity'] ?? 0;
$unit_price     = $_POST['unit_price'] ?? 0;
$reorder_level  = $_POST['reorder_level'] ?? 10;
$supplier_id    = $_POST['supplier_id'] ?? null;

if (empty($item_name)) {
    echo json_encode(["success" => false, "message" => "Item name is required"]);
    exit;
}

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
    echo json_encode(["success" => true, "message" => "Item added successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Insert failed"]);
}
?>
