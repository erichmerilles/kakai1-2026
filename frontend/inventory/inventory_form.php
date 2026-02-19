<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// role validation
if (!isset($_SESSION['user_id'])) {
  header('Location: ../../index.php');
  exit;
}

$activeModule = 'inventory';

// add/edit logic
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isEdit = ($id > 0);

// check permissions
if ($isEdit) {
  requirePermission('inv_edit');
} else {
  requirePermission('inv_add');
}

// initialize empty item data
$item = [
  'item_name' => '',
  'category_id' => '',
  'quantity' => 0,
  'unit_price' => 0.00,
  'supplier_id' => '',
  'reorder_level' => 10,
  'status' => 'Available',
  'image_path' => '' // Added image_path
];

$errorMsg = '';
$successMsg = '';

// handle add category form submission

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
  $newCatName = trim($_POST['new_category_name']);

  if (!empty($newCatName)) {
    try {
      // check for duplicate
      $check = $pdo->prepare("SELECT category_id FROM categories WHERE category_name = ?");
      $check->execute([$newCatName]);

      if ($check->rowCount() > 0) {
        $errorMsg = "Category '$newCatName' already exists.";
      } else {
        $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->execute([$newCatName]);
        $successMsg = "Category '$newCatName' added successfully!";
      }
    } catch (PDOException $e) {
      $errorMsg = "Error adding category: " . $e->getMessage();
    }
  } else {
    $errorMsg = "Category name cannot be empty.";
  }
}

// handle add/edit item form submission

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
  $name = trim($_POST['item_name']);
  $category_id = intval($_POST['category_id']);
  $qty = intval($_POST['quantity']);
  $price = floatval($_POST['unit_price']);
  $supplier_id = intval($_POST['supplier_id']);
  $reorder = intval($_POST['reorder_level']);

  // status logic
  $status = ($qty == 0) ? 'Out of Stock' : (($qty <= $reorder) ? 'Low Stock' : 'Available');

  // --- FILE UPLOAD LOGIC ---
  $finalImagePath = $_POST['current_image'] ?? ''; // Default to existing if editing

  // Check if a new file was uploaded
  if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['item_image']['tmp_name'];
    $fileName = $_FILES['item_image']['name'];

    // Get Extension
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');

    if (in_array($fileExtension, $allowedfileExtensions)) {
      // Create unique name
      $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

      // Define Target Directory
      $uploadFileDir = __DIR__ . '/../assets/uploads/';
      if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0755, true);
      }

      $dest_path = $uploadFileDir . $newFileName;

      if (move_uploaded_file($fileTmpPath, $dest_path)) {
        // Save RELATIVE path to DB
        $finalImagePath = 'assets/uploads/' . $newFileName;
      } else {
        $errorMsg = "There was an error moving the uploaded file.";
      }
    } else {
      $errorMsg = "Upload failed. Allowed types: jpg, png, jpeg, webp.";
    }
  }

  // validation
  if (empty($name) || empty($category_id)) {
    $errorMsg = "Item Name and Category are required.";
    $item = $_POST;
  } else {
    try {
      if ($isEdit) {
        // update logic
        $sql = "UPDATE inventory 
                SET item_name = ?, category_id = ?, quantity = ?, unit_price = ?, supplier_id = ?, reorder_level = ?, status = ?, image_path = ?
                WHERE item_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $category_id, $qty, $price, $supplier_id, $reorder, $status, $finalImagePath, $id]);

        $successMsg = "Item updated successfully!";
        $item = $_POST;
        $item['image_path'] = $finalImagePath;
      } else {
        // insert logic
        $sql = "INSERT INTO inventory (item_name, category_id, quantity, unit_price, supplier_id, reorder_level, status, image_path, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $category_id, $qty, $price, $supplier_id, $reorder, $status, $finalImagePath]);

        $successMsg = "New item added successfully!";
        // reset form
        $item = [
          'item_name' => '',
          'category_id' => '',
          'quantity' => 0,
          'unit_price' => 0.00,
          'supplier_id' => '',
          'reorder_level' => 10,
          'status' => 'Available',
          'image_path' => ''
        ];
      }
    } catch (PDOException $e) {
      $errorMsg = "Database Error: " . $e->getMessage();
      $item = $_POST;
    }
  }
}

// fetch dropdown data
$suppliers = [];
$categories = [];

try {
  $supStmt = $pdo->query("SELECT * FROM suppliers ORDER BY supplier_name ASC");
  $suppliers = $supStmt->fetchAll(PDO::FETCH_ASSOC);

  $catStmt = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC");
  $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  // handle error
}

// fetch data if editing
if ($isEdit && $_SERVER['REQUEST_METHOD'] !== 'POST') {
  try {
    $stmt = $pdo->prepare("SELECT * FROM inventory WHERE item_id = ?");
    $stmt->execute([$id]);
    $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($fetched) {
      $item = $fetched;
    } else {
      die("Item not found.");
    }
  } catch (PDOException $e) {
    die("Error fetching item: " . $e->getMessage());
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="icon" type="image/png" href="../assets/images/logo.png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $isEdit ? 'Edit Item' : 'Add Item' ?> | KakaiOne</title>
  <?php include '../includes/links.php'; ?>
</head>

<body class="bg-light">

  <?php include '../includes/sidebar.php'; ?>

  <main id="main-content" class="main-content-wrapper">
    <div class="container-fluid">

      <div class="d-flex justify-content-between align-items-start mb-4">
        <h3 class="fw-bold text-dark mt-2">
          <i class="bi bi-box-seam me-2 text-warning"></i>
          <?= $isEdit ? 'Edit Inventory Item' : 'Add New Item' ?>
        </h3>

        <div class="d-flex flex-column gap-2 text-end">
          <a href="inventory_overview.php" class="btn btn-secondary btn-sm px-3">
            <i class="bi bi-arrow-left"></i> Back to List
          </a>
          <button type="button" class="btn btn-outline-warning text-dark btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-circle"></i> Add Category
          </button>
        </div>
      </div>

      <?php if ($successMsg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i> <?= $successMsg ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if ($errorMsg): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $errorMsg ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-warning bg-opacity-10 border-bottom border-warning border-opacity-25">
          <h6 class="mb-0 fw-bold text-dark">Item Details</h6>
        </div>
        <div class="card-body p-4">

          <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">

              <div class="col-md-12 mb-2">
                <label class="form-label fw-bold">Product Image</label>
                <div class="d-flex align-items-center gap-3">
                  <?php if (!empty($item['image_path'])): ?>
                    <div class="border rounded p-1" style="width: 80px; height: 80px;">
                      <img src="/kakai1/frontend/<?= $item['image_path'] ?>" alt="Current" class="w-100 h-100" style="object-fit: cover;">
                    </div>
                  <?php endif; ?>

                  <div class="flex-grow-1">
                    <input type="file" name="item_image" class="form-control" accept="image/*">
                    <input type="hidden" name="current_image" value="<?= htmlspecialchars($item['image_path'] ?? '') ?>">
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Item Name <span class="text-danger">*</span></label>
                <input type="text" name="item_name" class="form-control" value="<?= htmlspecialchars($item['item_name']) ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                <select name="category_id" class="form-select" required>
                  <option value="">Select Category</option>
                  <?php foreach ($categories as $cat): ?>
                    <?php $selected = ($item['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>
                    <option value="<?= $cat['category_id'] ?>" <?= $selected ?>>
                      <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <?php if (empty($categories)): ?>
                  <div class="form-text text-danger">
                    No categories found. Use the "Add Category" button above.
                  </div>
                <?php endif; ?>
              </div>

              <div class="col-md-4">
                <label class="form-label fw-bold">Current Stock</label>
                <input type="number" name="quantity" class="form-control" value="<?= $item['quantity'] ?>" min="0" required>
              </div>

              <div class="col-md-4">
                <label class="form-label fw-bold">Unit Price (₱)</label>
                <input type="number" step="0.01" name="unit_price" class="form-control" value="<?= $item['unit_price'] ?>" min="0">
              </div>

              <div class="col-md-4">
                <label class="form-label fw-bold">Supplier</label>
                <select name="supplier_id" class="form-select" required>
                  <option value="">Select Supplier</option>
                  <?php foreach ($suppliers as $sup): ?>
                    <?php $selected = ($item['supplier_id'] == $sup['supplier_id']) ? 'selected' : ''; ?>
                    <option value="<?= $sup['supplier_id'] ?>" <?= $selected ?>>
                      <?= htmlspecialchars($sup['supplier_name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Reorder Level</label>
                <input type="number" name="reorder_level" class="form-control" value="<?= $item['reorder_level'] ?>" min="1">
                <div class="form-text">Flag item as "Low Stock" when quantity drops below this.</div>
              </div>

              <div class="col-12 mt-4 text-end">
                <a href="inventory_overview.php" class="btn btn-light border me-2">Cancel</a>
                <button type="submit" class="btn btn-warning px-4 fw-bold">
                  <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Update Item' : 'Save Item' ?>
                </button>
              </div>

            </div>
          </form>

        </div>
      </div>

    </div>
  </main>

  <div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <form method="POST" class="modal-content">
        <div class="modal-header bg-warning py-2">
          <h6 class="modal-title fw-bold">Add New Category</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add_category">

          <div class="mb-2">
            <label class="form-label small fw-bold">Category Name</label>
            <input type="text" name="new_category_name" class="form-control" required placeholder="e.g. Frozen Goods">
          </div>
        </div>
        <div class="modal-footer p-1 bg-light">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-dark">Add Category</button>
        </div>
      </form>
    </div>
  </div>

</body>

</html>