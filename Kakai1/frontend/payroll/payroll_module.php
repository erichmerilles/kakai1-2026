<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// set active module
$activeModule = 'payroll';

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
  <title>KakaiOne | Payroll Management</title>

  <?php include '../includes/links.php'; ?>
</head>

<body>

  <?php include '../includes/sidebar.php'; ?>

  <div id="dashboardContainer">
    <main id="main-content" style="margin-left: 250px; padding: 25px; transition: margin-left 0.3s;">
      <div class="container-fluid">
        <h3 class="fw-bold mb-4"><i class="bi bi-wallet2 me-2"></i>Payroll Management</h3>

        <div class="card mb-4 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title mb-3 fw-bold text-secondary">Generate Payroll (Custom Cutoff)</h5>
            <div class="row g-2 align-items-end">
              <div class="col-md-3">
                <label class="form-label small text-muted">Start Date</label>
                <input type="date" id="startDate" class="form-control" />
              </div>
              <div class="col-md-3">
                <label class="form-label small text-muted">End Date</label>
                <input type="date" id="endDate" class="form-control" />
              </div>
              <div class="col-md-3">
                <button id="generateBtn" class="btn btn-warning w-100 fw-bold text-dark">
                  <i class="bi bi-gear-wide-connected me-1"></i> Generate
                </button>
              </div>
            </div>
            <div id="genMessage" class="mt-3"></div>
          </div>
        </div>

        <div class="card shadow-sm border-0">
          <div class="card-header bg-dark text-white">
            <i class="bi bi-clock-history me-2"></i>Payroll Runs History
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0" id="runsTable">
                <thead class="table-light">
                  <tr>
                    <th>Run ID</th>
                    <th>Period</th>
                    <th>Created At</th>
                    <th>Total Gross</th>
                    <th>Deductions</th>
                    <th>Total Net</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>

  <script>
    // load history
    async function loadRuns() {
      try {
        const res = await fetch('../../backend/payroll/get_runs.php').then(r => r.json());
        const tbody = document.querySelector('#runsTable tbody');
        tbody.innerHTML = '';

        if (!res.success || !res.data) {
          tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No payroll runs found.</td></tr>';
          return;
        }

        res.data.forEach(r => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
                        <td><span class="badge bg-secondary">#${r.payroll_id}</span></td>
                        <td>${r.start_date} <i class="bi bi-arrow-right-short text-muted"></i> ${r.end_date}</td>
                        <td class="small text-muted">${r.created_at}</td>
                        <td>₱${Number(r.total_gross).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                        <td class="text-danger">-₱${Number(r.total_deductions).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                        <td class="fw-bold text-success">₱${Number(r.total_net).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="viewRun(${r.payroll_id})">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </td>`;
          tbody.appendChild(tr);
        });
      } catch (err) {
        console.error("Error loading runs", err);
      }
    }

    // generate payroll
    document.getElementById('generateBtn').addEventListener('click', async () => {
      const start = document.getElementById('startDate').value;
      const end = document.getElementById('endDate').value;
      const msg = document.getElementById('genMessage');

      msg.innerHTML = '';

      if (!start || !end) {
        msg.innerHTML = '<div class="alert alert-warning py-2"><i class="bi bi-exclamation-triangle me-2"></i>Please select both start and end dates.</div>';
        return;
      }

      // loading state
      const btn = document.getElementById('generateBtn');
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...';

      try {
        const res = await fetch('../../backend/payroll/generate_payroll.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            start_date: start,
            end_date: end
          })
        }).then(r => r.json());

        if (res.success) {
          msg.innerHTML = `<div class="alert alert-success py-2"><i class="bi bi-check-circle me-2"></i>Payroll Generated! Run ID: <strong>${res.payroll_id}</strong></div>`;
          loadRuns();
        } else {
          msg.innerHTML = `<div class="alert alert-danger py-2"><i class="bi bi-x-circle me-2"></i>${res.message || 'Error generating payroll.'}</div>`;
        }
      } catch (error) {
        msg.innerHTML = `<div class="alert alert-danger py-2">Server error occurred. Check console.</div>`;
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    });

    // view details
    function viewRun(id) {
      // NOTE: Ideally, create a real view_payroll.php page instead of document.writing HTML.
      // Keeping your existing logic for now but wrapped cleaner.
      const win = window.open('', '_blank', 'width=1000,height=800');

      const html = `
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <title>Payroll Run #${id}</title>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>body { background: #fff9f2; font-family: sans-serif; padding: 20px; }</style>
            </head>
            <body>
                <div class="container">
                    <div id="root" class="text-center mt-5">
                        <div class="spinner-border text-warning" role="status"></div>
                        <p>Loading Payroll Data...</p>
                    </div>
                </div>

                <script>
                    (async function() {
                        try {
                            const res = await fetch('../../backend/payroll/get_entries.php?payroll_id=${id}');
                            const data = await res.json();
                            const root = document.getElementById('root');

                            if (!data.success) {
                                root.innerHTML = '<div class="alert alert-danger">Failed to load data: ' + (data.message || 'Unknown error') + '</div>';
                                return;
                            }

                            let html = '<div class="d-flex justify-content-between align-items-center mb-4">';
                            html += '<h3 class="fw-bold">Payroll Run #${id}</h3>';
                            html += '<button onclick="window.print()" class="btn btn-secondary btn-sm">Print Report</button>';
                            html += '</div>';
                            
                            html += '<table class="table table-bordered table-striped bg-white">';
                            html += '<thead class="table-dark"><tr><th>Employee</th><th>Gross Pay</th><th>Deductions/Adv</th><th>Net Pay</th><th>Action</th></tr></thead>';
                            html += '<tbody>';

                            data.data.forEach(row => {
                                html += '<tr>';
                                html += '<td class="fw-bold">' + row.first_name + ' ' + row.last_name + '</td>';
                                html += '<td>₱' + Number(row.gross_pay).toFixed(2) + '</td>';
                                html += '<td class="text-danger">₱' + Number(row.cash_advance).toFixed(2) + '</td>';
                                html += '<td class="fw-bold text-success">₱' + Number(row.net_pay).toFixed(2) + '</td>';
                                html += '<td><a href="../../backend/payroll/payslip.php?payroll_id=${id}&employee_id=' + row.employee_id + '" target="_blank" class="btn btn-sm btn-outline-dark">Print Payslip</a></td>';
                                html += '</tr>';
                            });

                            html += '</tbody></table>';
                            root.innerHTML = html;
                            root.classList.remove('text-center', 'mt-5'); // Align top-left
                        } catch (e) {
                            document.getElementById('root').innerHTML = 'Error loading data.';
                        }
                    })();
                <\/script>
            </body>
            </html>
            `;

      win.document.write(html);
      win.document.close();
    }

    loadRuns();
  </script>
</body>

</html>