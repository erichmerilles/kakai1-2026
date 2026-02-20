<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// set active module
$activeModule = 'ordering';

// role validation
if (!isset($_SESSION['user_id'])) {
  header('Location: ../auth/login.php');
  exit;
}

// check page permissions
requirePermission('order_view');

// check permissions
$canCreateOrder = hasPermission('order_create');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="icon" type="image/png" href="../assets/images/logo.png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KakaiOne | Ordering</title>
  <?php include '../includes/links.php'; ?>
  <style>
    .stat-card {
      border-left: 4px solid;
      transition: transform 0.2s;
      border-radius: 8px;
    }

    .stat-card:hover {
      transform: translateY(-3px);
    }

    .border-left-primary {
      border-left-color: #0d6efd !important;
    }

    .border-left-success {
      border-left-color: #198754 !important;
    }

    .border-left-warning {
      border-left-color: #ffc107 !important;
    }

    .border-left-info {
      border-left-color: #0dcaf0 !important;
    }

    @media print {

      #sidebar,
      .btn,
      .input-group {
        display: none !important;
      }

      #main-content {
        margin-left: 0 !important;
        padding: 0 !important;
      }
    }
  </style>
</head>

<body class="bg-light">

  <?php include '../includes/sidebar.php'; ?>

  <div id="dashboardContainer">
    <main id="main-content" style="margin-left: 250px; padding: 25px; transition: margin-left 0.3s;">
      <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-end mb-4">
          <div>
            <h3 class="fw-bold text-dark mb-1">
              <i class="bi bi-cart-check me-2 text-warning"></i>Ordering Dashboard
            </h3>
            <p class="text-muted mb-0">Manage customer orders, track sales, and process transactions.</p>
          </div>
          <div class="d-flex gap-2 text-end">
            <a href="order_list.php" class="btn btn-secondary shadow-sm">
              <i class="bi bi-list-ul"></i> View All Orders
            </a>

            <?php if ($canCreateOrder): ?>
              <a href="order_create.php" class="btn btn-warning shadow-sm">
                <i class="bi bi-plus-lg"></i> Create New Order
              </a>
            <?php endif; ?>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm border-left-success h-100 py-2">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs fw-bold text-success text-uppercase mb-1">Sales Today</div>
                    <div class="h4 mb-0 fw-bold text-dark" id="salesToday">
                      <span class="spinner-border spinner-border-sm text-success" role="status"></span>
                    </div>
                  </div>
                  <div class="col-auto"><i class="bi bi-cash-coin fa-2x text-success opacity-50 fs-1"></i></div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm border-left-primary h-100 py-2">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Orders</div>
                    <div class="h4 mb-0 fw-bold text-dark" id="totalOrders">
                      <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                    </div>
                  </div>
                  <div class="col-auto"><i class="bi bi-receipt fa-2x text-primary opacity-50 fs-1"></i></div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm border-left-warning h-100 py-2">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">Open Orders</div>
                    <div class="h4 mb-0 fw-bold text-dark" id="openOrders">
                      <span class="spinner-border spinner-border-sm text-warning" role="status"></span>
                    </div>
                  </div>
                  <div class="col-auto"><i class="bi bi-hourglass-split fa-2x text-warning opacity-50 fs-1"></i></div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm border-left-info h-100 py-2">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs fw-bold text-info text-uppercase mb-1">Active Products</div>
                    <div class="h4 mb-0 fw-bold text-dark" id="totalProducts">
                      <span class="spinner-border spinner-border-sm text-info" role="status"></span>
                    </div>
                  </div>
                  <div class="col-auto"><i class="bi bi-box-seam fa-2x text-info opacity-50 fs-1"></i></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="card mb-4 shadow-sm">
              <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul me-2"></i>Recent Orders</span>
                <div class="input-group input-group-sm w-25 min-w-200">
                  <input type="text" id="orderSearch" class="form-control" placeholder="Search orders...">
                  <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                </div>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-striped align-middle mb-0" id="recentOrdersTable">
                    <thead class="table-light">
                      <tr>
                        <th class="ps-3">Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total Amount</th>
                        <th>Date & Time</th>
                        <th class="text-end pe-3">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                          <div class="spinner-border text-primary" role="status"></div>
                          <div class="mt-2">Loading orders...</div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>

  <script>
    // search filter 
    document.getElementById('orderSearch').addEventListener('keyup', function() {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll('#recentOrdersTable tbody tr');

      rows.forEach(row => {
        if (row.cells.length === 1) return;
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    // load dashboard stats and recent orders
    async function loadOverview() {
      try {
        const [pRes, oRes, sRes] = await Promise.all([
          fetch('../../backend/orders/get_products.php').then(r => r.json()),
          fetch('../../backend/orders/get_orders.php').then(r => r.json()),
          fetch('../../backend/orders/get_orders.php?recent=1').then(r => r.json())
        ]);

        // udpate total products stat
        document.getElementById('totalProducts').innerText = pRes.success ? pRes.data.length : '0';

        // update orders stats
        let totalOrderCount = 0;
        let openOrderCount = 0;
        let salesToday = 0;

        if (oRes.success && oRes.data) {
          totalOrderCount = oRes.data.length;

          const today = new Date().toISOString().slice(0, 10);

          oRes.data.forEach(r => {
            // count open orders
            if (r.status !== 'Delivered' && r.status !== 'Cancelled' && r.status !== 'Completed') {
              openOrderCount++;
            }
            // count today's sales
            if (r.order_date && r.order_date.indexOf(today) === 0 && r.payment_status === 'Paid') {
              salesToday += parseFloat(r.total_amount || 0);
            }
          });
        }

        document.getElementById('totalOrders').innerText = totalOrderCount;
        document.getElementById('openOrders').innerText = openOrderCount;
        document.getElementById('salesToday').innerText = '₱ ' + salesToday.toLocaleString(undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });

        // update recent orders table
        const tbody = document.querySelector('#recentOrdersTable tbody');
        tbody.innerHTML = '';

        if (sRes.success && sRes.data && sRes.data.length > 0) {
          // show only the 8 most recent orders
          sRes.data.slice(0, 8).forEach(r => {
            const tr = document.createElement('tr');
            const customerName = r.full_name ? r.full_name : '<span class="text-muted fst-italic">Walk-in Customer</span>';

            // determine status badge
            let statusBadge = 'secondary';
            if (r.status === 'Pending') statusBadge = 'warning text-dark';
            if (r.status === 'Processing') statusBadge = 'info text-dark';
            if (r.status === 'Completed' || r.status === 'Delivered') statusBadge = 'success';
            if (r.status === 'Cancelled') statusBadge = 'danger';

            tr.innerHTML = `
                            <td class="ps-3 fw-bold">#${r.order_id}</td>
                            <td>${customerName}</td>
                            <td><span class="badge bg-${statusBadge}">${r.status}</span></td>
                            <td class="fw-bold">₱${Number(r.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                            <td class="text-muted small">${r.order_date}</td>
                            <td class="text-end pe-3">
                                <a class="btn btn-sm btn-info text-white" href="order_view.php?id=${r.order_id}" title="View Details">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        `;
            tbody.appendChild(tr);
          });
        } else {
          tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No recent orders found.</td></tr>`;
        }
      } catch (error) {
        console.error("Error loading dashboard data:", error);
        document.querySelector('#recentOrdersTable tbody').innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle me-2"></i>Error loading data. Check console.</td></tr>`;
      }
    }

    // initialize dashboard
    loadOverview();
  </script>
</body>

</html>