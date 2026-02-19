<?php
// role validation
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/auth_check.php';

$activeModule = isset($activeModule) ? $activeModule : 'dashboard';
$currentPage = basename($_SERVER['PHP_SELF']);
$baseUrl = '/kakai1/frontend';

$dashboardLink = ($_SESSION['role'] === 'Admin')
    ? $baseUrl . '/dashboard/admin_dashboard.php'
    : $baseUrl . '/dashboard/employee_dashboard.php';
?>

<nav id="sidebar">

    <div class="text-center mb-4 logo-container">
        <img src="<?= $baseUrl ?>/assets/images/logo.jpg" alt="Logo" class="sidebar-logo">
        <h5 class="fw-bold text-light mt-2">KakaiOne</h5>
        <p class="small text-light mb-3">
            <?= htmlspecialchars($_SESSION['role'] ?? 'User') ?> Panel
        </p>
    </div>

    <div class="nav flex-column flex-grow-1">

        <a href="<?= $dashboardLink ?>" class="nav-link mb-2 <?= $activeModule === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>

        <?php if (hasPermission('emp_view')): ?>
            <a href="<?= $baseUrl ?>/employee/employee_module.php" class="nav-link mb-2 <?= $activeModule === 'employee' ? 'active' : '' ?>">
                <i class="bi bi-people-fill me-2"></i>Employees
            </a>
            <?php if ($activeModule === 'employee'): ?>
                <div class="ms-3 mb-2 ps-2 border-start border-secondary">
                    <a href="<?= $baseUrl ?>/employee/employee_module.php" class="nav-link py-1 sub-link <?= $currentPage == 'employee_module.php' ? 'active-sub' : 'text-light' ?>">
                        <i class="bi bi-caret-right-fill"></i> Overview
                    </a>
                    <?php if (hasPermission('att_view')): ?>
                        <a href="<?= $baseUrl ?>/attendance/attendance_page.php" class="nav-link py-1 sub-link <?= $currentPage == 'attendance_page.php' ? 'active-sub' : 'text-light' ?>">
                            <i class="bi bi-caret-right-fill"></i> Attendance
                        </a>
                    <?php endif; ?>
                    <a href="<?= $baseUrl ?>/requests/leave_requests.php" class="nav-link py-1 sub-link <?= $currentPage == 'leave_requests.php' ? 'active-sub' : 'text-light' ?>">
                        <i class="bi bi-caret-right-fill"></i> Leaves
                    </a>
                    <a href="<?= $baseUrl ?>/requests/cash_advance.php" class="nav-link py-1 sub-link <?= $currentPage == 'cash_advance.php' ? 'active-sub' : 'text-light' ?>">
                        <i class="bi bi-caret-right-fill"></i> Cash Advance
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (hasPermission('inv_view')): ?>
            <a href="<?= $baseUrl ?>/inventory/inventory_overview.php" class="nav-link mb-2 <?= $activeModule === 'inventory' ? 'active' : '' ?>">
                <i class="bi bi-box-seam me-2"></i>Inventory
            </a>
            <?php if ($activeModule === 'inventory'): ?>
                <div class="ms-3 mb-2 ps-2 border-start border-secondary">

                    <a href="<?= $baseUrl ?>/inventory/inventory_overview.php" class="nav-link py-1 sub-link <?= $currentPage == 'inventory_overview.php' ? 'active-sub' : 'text-light' ?>">
                        <i class="bi bi-caret-right-fill"></i> Overview
                    </a>

                    <?php if (hasPermission('inv_add') || hasPermission('inv_edit')): ?>
                        <a href="<?= $baseUrl ?>/inventory/inventory_form.php" class="nav-link py-1 sub-link <?= $currentPage == 'inventory_form.php' ? 'active-sub' : 'text-light' ?>">
                            <i class="bi bi-caret-right-fill"></i> Add/Edit Item
                        </a>

                        <a href="<?= $baseUrl ?>/inventory/manage_categories.php" class="nav-link py-1 sub-link <?= $currentPage == 'manage_categories.php' ? 'active-sub' : 'text-light' ?>">
                            <i class="bi bi-caret-right-fill"></i> Categories
                        </a>
                        
                        <a href="<?= $baseUrl ?>/inventory/manage_suppliers.php" class="nav-link py-1 sub-link <?= $currentPage == 'manage_suppliers.php' ? 'active-sub' : 'text-light' ?>">
                            <i class="bi bi-caret-right-fill"></i> Suppliers
                        </a>
                    <?php endif; ?>

                    <?php if (hasPermission('inv_stock_in') || hasPermission('inv_stock_out')): ?>
                        <a href="<?= $baseUrl ?>/inventory/inventory_movements.php" class="nav-link py-1 sub-link <?= $currentPage == 'inventory_movements.php' ? 'active-sub' : 'text-light' ?>">
                            <i class="bi bi-caret-right-fill"></i> Stock Movements
                        </a>
                    <?php endif; ?>

                    <a href="<?= $baseUrl ?>/inventory/inventory_analytics.php" class="nav-link py-1 sub-link <?= $currentPage == 'inventory_analytics.php' ? 'active-sub' : 'text-light' ?>">
                        <i class="bi bi-caret-right-fill"></i> Analytics
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (hasPermission('payroll_view')): ?>
            <a href="<?= $baseUrl ?>/payroll/payroll_module.php" class="nav-link mb-2 <?= $activeModule === 'payroll' ? 'active' : '' ?>">
                <i class="bi bi-cash-coin me-2"></i>Payroll
            </a>
        <?php endif; ?>

        <?php if (hasPermission('order_view')): ?>
            <a href="<?= $baseUrl ?>/ordering/ordering_module.php" class="nav-link mb-2 <?= $activeModule === 'ordering' ? 'active' : '' ?>">
                <i class="bi bi-cart-check me-2"></i>Ordering
            </a>
        <?php endif; ?>


        <?php if ($_SESSION['role'] !== 'Admin'): ?>

            <div class="mt-2 mb-2 border-top border-secondary pt-2">
                <small class="text-white-50 ms-3 text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">My Request</small>
            </div>

            <a href="<?= $baseUrl ?>/requests/cash_advance.php" class="nav-link mb-2 <?= $currentPage === 'cash_advance.php' ? 'active' : '' ?>">
                <i class="bi bi-wallet2 me-2"></i>Cash Advance
            </a>

        <?php endif; ?>
    </div>

    <div class="mt-auto">
        <form action="/kakai1/backend/auth/logout.php" method="POST">
            <button class="btn btn-outline-light w-100 btn-sm mt-3">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </button>
        </form>
        <p class="text-center text-secondary small mt-3 mb-0">© 2025 KakaiOne</p>
    </div>
</nav>