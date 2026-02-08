<?php
/*session_start();
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents("php://input"), true);
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT user_id, employee_id, username, password, role, status FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    $user = $result->fetch_assoc();

    if (strtolower(trim($user['status'])) !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Your account is inactive. Please contact admin.']);
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
        exit;
    }

    // Create session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['employee_id'] = $user['employee_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    // Update last_login
    $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $update->bind_param("i", $user['user_id']);
    $update->execute();

    // Redirect based on role
    $redirect = ($user['role'] === 'Admin') 
        ? '../../frontend/dashboard/admin_dashboard.php' 
        : '../../frontend/dashboard/employee_dashboard.php';

    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'redirect' => $redirect
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}*/

session_start();
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

try {
    // 1. Get Input
    $input = json_decode(file_get_contents("php://input"), true);
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        exit;
    }

    // 2. Fetch User
    $stmt = $pdo->prepare("SELECT user_id, employee_id, username, password, role, status FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // 3. Check if user exists
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    // 4. Check Status
    if (strtolower(trim($user['status'])) !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Your account is inactive. Please contact admin.']);
        exit;
    }

    // 5. Verify Password
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
        exit;
    }

    // 6. Set Session Variables (FIXED HERE)
    $_SESSION['user_id'] = $user['user_id'];      // Fixed variable name
    $_SESSION['employee_id'] = $user['employee_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    // 7. Fetch Permissions
    try {
        $permStmt = $pdo->prepare("SELECT * FROM employee_permissions WHERE employee_id = ?");
        $permStmt->execute([$user['employee_id']]);
        $perms = $permStmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['permissions'] = $perms ? $perms : [];
    } catch (Exception $e) {
        $_SESSION['permissions'] = [];
    }

    // 8. Update Last Login
    $update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $update->execute([$user['user_id']]);

    // 9. Redirect Logic
    $redirect = ($user['role'] === 'Admin')
        ? '../../frontend/dashboard/admin_dashboard.php'
        : '../../frontend/dashboard/employee_dashboard.php';

    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'redirect' => $redirect
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
