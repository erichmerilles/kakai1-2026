<?php
session_start();
date_default_timezone_set('Asia/Manila');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// set active module
$activeModule = 'employee';

// role validation
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

$is_admin = ($_SESSION['role'] === 'Admin');
$my_id = $_SESSION['employee_id'];

// handle actions
$actionMsg = '';
$actionType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // admin approval/rejection
    if (isset($_POST['request_id'], $_POST['action']) && $is_admin) {
        if (!hasPermission('att_approve')) {
            die("Unauthorized.");
        }

        $reqId = intval($_POST['request_id']);
        $action = $_POST['action'];

        try {
            $stmt = $pdo->prepare("UPDATE cash_advances SET status = ? WHERE cash_id = ?");
            if ($stmt->execute([$action, $reqId])) {
                $actionType = 'success';
                $actionMsg = "Request " . ($action === 'Approved' ? 'Approved' : 'Rejected') . " successfully.";
            }
        } catch (PDOException $e) {
            $actionType = 'danger';
            $actionMsg = "Error: " . $e->getMessage();
        }
    }

    // employee request submission
    if (isset($_POST['new_request']) && !$is_admin) {
        $amount = floatval($_POST['amount']);
        $reason = trim($_POST['reason']);

        try {
            $stmt = $pdo->prepare("INSERT INTO cash_advances (employee_id, amount, reason, status, created_at) VALUES (?, ?, ?, 'Pending', NOW())");
            if ($stmt->execute([$my_id, $amount, $reason])) {
                $actionType = 'success';
                $actionMsg = "Cash Advance request submitted! Please wait for admin approval.";
            }
        } catch (PDOException $e) {
            $actionType = 'danger';
            $actionMsg = "Submission failed: " . $e->getMessage();
        }
    }
}

// fetch stats for KPI Cards
$stats = ['Pending' => 0, 'Total_Amount' => 0];
try {
    $statStmt = $pdo->query("SELECT status, COUNT(*) as count, SUM(amount) as total FROM cash_advances GROUP BY status");
    $allStats = $statStmt->fetchAll();
    foreach ($allStats as $s) {
        if ($s['status'] == 'Pending') $stats['Pending'] = $s['count'];
        if ($s['status'] == 'Approved') $stats['Total_Amount'] = $s['total'];
    }
} catch (PDOException $e) {
}

// fetch requests
$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'Pending';
$allowedTabs = ['Pending', 'Approved', 'Rejected'];
if (!in_array($currentTab, $allowedTabs)) $currentTab = 'Pending';

try {
    if ($is_admin) {
        $stmt = $pdo->prepare("
            SELECT ca.cash_id, ca.amount, ca.reason, ca.status, ca.created_at,
                   e.first_name, e.last_name, e.employee_code, e.position
            FROM cash_advances ca
            JOIN employees e ON ca.employee_id = e.employee_id
            WHERE ca.status = ?
            ORDER BY ca.created_at DESC
        ");
        $stmt->execute([$currentTab]);
    } else {
        $stmt = $pdo->prepare("
            SELECT ca.cash_id, ca.amount, ca.reason, ca.status, ca.created_at,
                   e.first_name, e.last_name, e.employee_code, e.position
            FROM cash_advances ca
            JOIN employees e ON ca.employee_id = e.employee_id
            WHERE ca.employee_id = ?
            ORDER BY ca.created_at DESC
        ");
        $stmt->execute([$my_id]);
    }
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Advance | KakaiOne</title>
    <?php include '../includes/links.php'; ?>
    <style>
        .stat-card {
            border-left: 4px solid;
            border-radius: 8px;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            padding: 1rem 1.5rem;
        }

        .nav-tabs .nav-link.active {
            border-bottom: 3px solid #ffc107;
            color: #212529;
            font-weight: bold;
            background: none;
        }
    </style>
</head>

<body class="bg-light">
    <?php include '../includes/sidebar.php'; ?>

    <main id="main-content" style="margin-left: 250px; padding: 25px;">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-1">
                        <i class="bi bi-cash-coin me-2 text-warning"></i>Cash Advance Management
                    </h3>
                    <p class="text-muted mb-0"><?= $is_admin ? "Review and process employee financial requests." : "Track your requested advances and financial standing." ?></p>
                </div>
                <?php if (!$is_admin): ?>
                    <button class="btn btn-warning shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#requestCAModal">
                        <i class="bi bi-plus-lg me-1"></i> New Request
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($actionMsg): ?>
                <script>
                    Swal.fire({
                        icon: '<?= $actionType ?>',
                        title: '<?= $actionType == "success" ? "Done!" : "Error" ?>',
                        text: '<?= $actionMsg ?>'
                    });
                </script>
            <?php endif; ?>

            <?php if ($is_admin): ?>
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card stat-card border-0 shadow-sm border-left-warning h-100 py-2">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending Requests</div>
                                        <div class="h4 mb-0 fw-bold text-dark"><?= $stats['Pending'] ?></div>
                                    </div>
                                    <div class="col-auto"><i class="bi bi-hourglass-split fs-1 text-warning opacity-50"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card stat-card border-0 shadow-sm border-left-success h-100 py-2">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs fw-bold text-success text-uppercase mb-1">Total Approved Disbursed</div>
                                        <div class="h4 mb-0 fw-bold text-dark">₱ <?= number_format($stats['Total_Amount'], 2) ?></div>
                                    </div>
                                    <div class="col-auto"><i class="bi bi-wallet2 fs-1 text-success opacity-50"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs mb-3 bg-white shadow-sm rounded">
                    <li class="nav-item"><a class="nav-link <?= $currentTab === 'Pending' ? 'active' : '' ?>" href="?tab=Pending">Pending</a></li>
                    <li class="nav-item"><a class="nav-link <?= $currentTab === 'Approved' ? 'active' : '' ?>" href="?tab=Approved">Approved</a></li>
                    <li class="nav-item"><a class="nav-link <?= $currentTab === 'Rejected' ? 'active' : '' ?>" href="?tab=Rejected">Rejected</a></li>
                </ul>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <?php if ($is_admin): ?>
                                        <th class="ps-4">Employee</th>
                                    <?php endif; ?>
                                    <th class="<?= $is_admin ? '' : 'ps-4' ?>">Amount</th>
                                    <th>Reason</th>
                                    <th>Date Requested</th>
                                    <th>Status</th>
                                    <?php if ($is_admin && $currentTab === 'Pending'): ?>
                                        <th class="text-end pe-4">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($requests)): ?>
                                    <?php foreach ($requests as $req): ?>
                                        <tr>
                                            <?php if ($is_admin): ?>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-warning bg-opacity-10 text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                                            <?= strtoupper(substr($req['first_name'], 0, 1)) ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold mb-0 text-dark"><?= htmlspecialchars($req['first_name'] . ' ' . $req['last_name']) ?></div>
                                                            <small class="text-muted" style="font-size: 0.75rem;"><?= $req['employee_code'] ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                            <td class="<?= $is_admin ? '' : 'ps-4' ?> fw-bold text-dark">
                                                ₱ <?= number_format($req['amount'], 2) ?>
                                            </td>
                                            <td>
                                                <span class="d-inline-block text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($req['reason']) ?>">
                                                    <?= htmlspecialchars($req['reason']) ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small"><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                                            <td>
                                                <?php
                                                $b = 'secondary';
                                                if ($req['status'] === 'Approved') $b = 'success';
                                                elseif ($req['status'] === 'Rejected') $b = 'danger';
                                                elseif ($req['status'] === 'Pending') $b = 'warning text-dark';
                                                ?>
                                                <span class="badge rounded-pill bg-<?= $b ?>"><?= $req['status'] ?></span>
                                            </td>
                                            <?php if ($is_admin && $currentTab === 'Pending'): ?>
                                                <td class="text-end pe-4">
                                                    <button onclick="confirmAction(<?= $req['cash_id'] ?>, 'Approved')" class="btn btn-sm btn-success rounded-pill px-3">
                                                        <i class="bi bi-check-lg"></i> Approve
                                                    </button>
                                                    <button onclick="confirmAction(<?= $req['cash_id'] ?>, 'Rejected')" class="btn btn-sm btn-outline-danger rounded-pill px-3 ms-1">
                                                        <i class="bi bi-x-lg"></i> Reject
                                                    </button>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox display-6 d-block mb-2 opacity-25"></i>
                                            No cash advance records found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <form id="actionForm" method="POST" style="display:none;">
        <input type="hidden" name="request_id" id="reqIdInput">
        <input type="hidden" name="action" id="actionInput">
    </form>

    <div class="modal fade" id="requestCAModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>New Advance Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="new_request" value="1">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Requested Amount (₱)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">₱</span>
                            <input type="number" name="amount" class="form-control form-control-lg" placeholder="0.00" step="0.01" required min="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Reason for Request</label>
                        <textarea name="reason" class="form-control" rows="4" placeholder="Briefly explain why you need the advance..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function confirmAction(id, action) {
            Swal.fire({
                title: action + ' Request?',
                text: "Are you sure you want to " + action.toLowerCase() + " this cash advance?",
                icon: action === 'Approved' ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: action === 'Approved' ? '#198754' : '#dc3545',
                confirmButtonText: 'Yes, ' + action
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('reqIdInput').value = id;
                    document.getElementById('actionInput').value = action;
                    document.getElementById('actionForm').submit();
                }
            });
        }
    </script>
</body>

</html>