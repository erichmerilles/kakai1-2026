<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function hasPermission($key)
{
    // admin acces
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
        return true;
    }

    // chech employee permissions
    if (isset($_SESSION['permissions']) && is_array($_SESSION['permissions'])) {
        // premission check
        if (isset($_SESSION['permissions'][$key]) && $_SESSION['permissions'][$key] == 1) {
            return true;
        }
    }
    return false;
}

function requirePermission($key)
{
    if (!hasPermission($key)) {
        header("Location: /kakai1/frontend/dashboard/employee_dashboard.php");
        exit;
    }
}
