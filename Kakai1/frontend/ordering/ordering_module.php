<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// set active module
$activeModule = 'ordering';

// role validation
if (!isset($_SESSION['user_id'])) {
  header('Location: ../auth/login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KakaiOne | Ordering</title>
  <?php include '../includes/links.php'; ?>
</head>

<body>

  <?php include '../includes/sidebar.php'; ?>

  <div id="dashboardContainer">
    <main id="main-content" style="margin-left: 250px; padding: 25px; transition: margin-left 0.3s;">
      <div class="container-fluid">
        <h3 class="fw-bold mb-3"><i class="bi bi-cart me-2"></i>Ordering</h3>

        <div class="row">
          <div class="col-md-4">
            <div class="module-card">
              <h6>Total Products</h6>
              <h3 id="totalProducts">—</h3>
              <a href="order_create.php" class="btn btn-warning btn-sm mt-2">Create New Order</a>
            </div>
          </div>
          <div class="col-md-4">
            <div class="module-card">
              <h6>Open Orders</h6>
              <h3 id="openOrders">—</h3>
              <a href="order_list.php" class="btn btn-secondary btn-sm mt-2">View Orders</a>
            </div>
          </div>
          <div class="col-md-4">
            <div class="module-card">
              <h6>Sales Today</h6>
              <h3 id="salesToday">₱0.00</h3>
            </div>
          </div>
        </div>

        <div class="module-card mt-4">
          <h5>Recent Orders</h5>
          <div class="table-responsive">
            <table class="table table-striped" id="recentOrders">
              <thead class="table-light">
                <tr>
                  <th>Order</th>
                  <th>Customer</th>
                  <th>Status</th>
                  <th>Total</th>
                  <th>Date</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>

  <script>
    async function loadOverview() {
      try {
        const [pRes, oRes, sRes] = await Promise.all([
          fetch('../../backend/orders/get_products.php').then(r => r.json()),
          fetch('../../backend/orders/get_orders.php').then(r => r.json()),
          fetch('../../backend/orders/get_orders.php?recent=1').then(r => r.json())
        ]);

        // update stats
        document.getElementById('totalProducts').innerText = pRes.success ? pRes.data.length : '—';

        const open = oRes.success ? oRes.data.filter(x => x.status !== 'Delivered' && x.status !== 'Cancelled').length : 0;
        document.getElementById('openOrders').innerText = open;

        // calculate sales today
        let salesToday = 0;
        if (oRes.success) {
          const today = new Date().toISOString().slice(0, 10);
          oRes.data.forEach(r => {
            if (r.order_date && r.order_date.indexOf(today) === 0 && r.payment_status === 'Paid') {
              salesToday += parseFloat(r.total_amount || 0);
            }
          });
        }
        document.getElementById('salesToday').innerText = '₱' + salesToday.toFixed(2);

        // update recent orders table
        const tbody = document.querySelector('#recentOrders tbody');
        tbody.innerHTML = '';
        if (sRes.success) {
          sRes.data.slice(0, 8).forEach(r => {
            const tr = document.createElement('tr');
            const customerName = r.full_name ? r.full_name : 'Guest';
            const statusBadge = r.status === 'Pending' ? 'warning' : 'success';

            tr.innerHTML = `
                            <td>#${r.order_id}</td>
                            <td>${customerName}</td>
                            <td><span class="badge bg-${statusBadge}">${r.status}</span></td>
                            <td>₱${Number(r.total_amount).toFixed(2)}</td>
                            <td>${r.order_date}</td>
                            <td><a class="btn btn-sm btn-primary" href="order_view.php?id=${r.order_id}">View</a></td>
                        `;
            tbody.appendChild(tr);
          });
        }
      } catch (error) {
        console.error("Error loading dashboard data:", error);
      }
    }

    loadOverview();
  </script>
</body>

</html>