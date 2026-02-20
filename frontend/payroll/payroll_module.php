<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// set active module
$activeModule = 'payroll';

// role validation
if (!isset($_SESSION['user_id'])) {
  header('Location: ../auth/login.php');
  exit;
}

// check page level permissions
requirePermission('payroll_view');

// check permissions
$canGenerate = hasPermission('payroll_generate');
$canPrint = hasPermission('payslip_print');

// fetch payroll summary stats for KPI cards
$payrollStats = [
  'total_runs' => 0,
  'total_payout' => 0,
  'latest_gross' => 0,
  'total_deductions' => 0
];

try {
  // total runs
  $stmt1 = $pdo->query("SELECT COUNT(*) FROM payroll_runs");
  $payrollStats['total_runs'] = $stmt1->fetchColumn() ?: 0;

  // sum of all net payouts and deductions
  $stmt2 = $pdo->query("SELECT SUM(total_net), SUM(total_deductions) FROM payroll_runs");
  $sums = $stmt2->fetch(PDO::FETCH_NUM);
  if ($sums) {
    $payrollStats['total_payout'] = $sums[0] ?: 0;
    $payrollStats['total_deductions'] = $sums[1] ?: 0;
  }

  // latest gross payout
  $stmt3 = $pdo->query("SELECT total_gross FROM payroll_runs ORDER BY created_at DESC LIMIT 1");
  $payrollStats['latest_gross'] = $stmt3->fetchColumn() ?: 0;
} catch (PDOException $e) {
  // handle database errors
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="icon" type="image/png" href="../assets/images/logo.png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KakaiOne | Payroll Management</title>

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

    .border-left-danger {
      border-left-color: #dc3545 !important;
    }

    @media print {

      #sidebar,
      .btn,
      .input-group,
      .generate-section {
        display: none !important;
      }

      #main-content {
        margin-left: 0 !important;
        padding: 0 !important;
      }

      .col-lg-8,
      .col-lg-12 {
        width: 100% !important;
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
              <i class="bi bi-wallet2 me-2 text-warning"></i>Payroll Management
            </h3>
            <p class="text-muted mb-0">Generate payslips, track disbursements, and manage deductions.</p>
          </div>
          <div class="d-flex gap-2">
            <?php if ($canPrint): ?>
              <button onclick="window.print()" class="btn btn-secondary shadow-sm">
                <i class="bi bi-printer"></i> Print Report
              </button>
            <?php endif; ?>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm border-left-primary h-100 py-2">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Payroll Runs</div>
                    <div class="h4 mb-0 fw-bold text-dark"><?= $payrollStats['total_runs']; ?></div>
                  </div>
                  <div class="col-auto"><i class="bi bi-calculator fa-2x text-primary opacity-50 fs-1"></i></div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm border-left-success h-100 py-2">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs fw-bold text-success text-uppercase mb-1">Total Net Payout</div>
                    <div class="h4 mb-0 fw-bold text-dark">₱ <?= number_format($payrollStats['total_payout'], 2); ?></div>
                  </div>
                  <div class="col-auto"><i class="bi bi-cash fa-2x text-success opacity-50 fs-1"></i></div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm border-left-warning h-100 py-2">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">Last Gross Payout</div>
                    <div class="h4 mb-0 fw-bold text-dark">₱ <?= number_format($payrollStats['latest_gross'], 2); ?></div>
                  </div>
                  <div class="col-auto"><i class="bi bi-wallet fa-2x text-warning opacity-50 fs-1"></i></div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm border-left-danger h-100 py-2">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs fw-bold text-danger text-uppercase mb-1">Total Deductions</div>
                    <div class="h4 mb-0 fw-bold text-dark">₱ <?= number_format($payrollStats['total_deductions'], 2); ?></div>
                  </div>
                  <div class="col-auto"><i class="bi bi-graph-down-arrow fa-2x text-danger opacity-50 fs-1"></i></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <?php if ($canGenerate): ?>
            <div class="col-lg-4 generate-section">
              <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                  <i class="bi bi-gear-fill me-2"></i>Generate Payroll
                </div>
                <div class="card-body p-4">
                  <p class="small text-muted mb-4">Select a custom cutoff date to calculate employee attendance, deductions, and final net pay.</p>

                  <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Start Date <span class="text-danger">*</span></label>
                    <input type="date" id="startDate" class="form-control" />
                  </div>
                  <div class="mb-4">
                    <label class="form-label fw-bold small text-dark">End Date <span class="text-danger">*</span></label>
                    <input type="date" id="endDate" class="form-control" />
                  </div>

                  <button id="generateBtn" class="btn btn-warning w-100 fw-bold text-dark py-2">
                    <i class="bi bi-gear-wide-connected me-1"></i> Process Payroll
                  </button>

                  <div id="genMessage" class="mt-3"></div>
                </div>
              </div>
            </div>
            <div class="col-lg-8">
            <?php else: ?>
              <div class="col-lg-12">
              <?php endif; ?>

              <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-clock-history me-2"></i>Payroll Runs History</span>
                  <div class="input-group input-group-sm w-50">
                    <input type="text" id="tableSearch" class="form-control" placeholder="Search period or run ID...">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                  </div>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0" id="runsTable">
                      <thead class="table-light">
                        <tr>
                          <th class="ps-3">Run ID</th>
                          <th>Period</th>
                          <th>Gross Pay</th>
                          <th>Deductions</th>
                          <th>Total Net</th>
                          <th class="text-end pe-3">Action</th>
                        </tr>
                      </thead>
                      <tbody>
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
    // table search filter
    document.getElementById('tableSearch').addEventListener('keyup', function() {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll('#runsTable tbody tr');

      rows.forEach(row => {
        if (row.cells.length === 1) return; // skip empty state row
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    // load history
    async function loadRuns() {
      try {
        const res = await fetch('../../backend/payroll/get_runs.php').then(r => r.json());
        const tbody = document.querySelector('#runsTable tbody');
        tbody.innerHTML = '';

        if (!res.success || !res.data || res.data.length === 0) {
          tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No payroll runs found.</td></tr>';
          return;
        }

        res.data.forEach(r => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
                        <td class="ps-3"><span class="badge bg-secondary">#${r.payroll_id}</span></td>
                        <td>
                            <div class="fw-bold text-dark">${r.start_date} <i class="bi bi-arrow-right-short text-muted"></i> ${r.end_date}</div>
                            <div class="small text-muted">Ran on: ${r.created_at}</div>
                        </td>
                        <td class="text-muted">₱${Number(r.total_gross).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                        <td class="text-danger">-₱${Number(r.total_deductions).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                        <td class="fw-bold text-success fs-6">₱${Number(r.total_net).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                        <td class="text-end pe-3">
                            <button class="btn btn-sm btn-info text-white" onclick="viewRun(${r.payroll_id})" title="View Details">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </td>`;
          tbody.appendChild(tr);
        });
      } catch (err) {
        console.error("Error loading runs", err);
      }
    }

    <?php if ($canGenerate): ?>
      // generate payroll (Only bind if button exists)
      document.getElementById('generateBtn').addEventListener('click', async () => {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        const msg = document.getElementById('genMessage');

        msg.innerHTML = '';

        if (!start || !end) {
          msg.innerHTML = '<div class="alert alert-warning py-2 small"><i class="bi bi-exclamation-triangle me-2"></i>Please select both start and end dates.</div>';
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
            msg.innerHTML = `<div class="alert alert-success py-2 small"><i class="bi bi-check-circle me-2"></i>Payroll Generated! Run ID: <strong>${res.payroll_id}</strong></div>`;
            loadRuns();
          } else {
            msg.innerHTML = `<div class="alert alert-danger py-2 small"><i class="bi bi-x-circle me-2"></i>${res.message || 'Error generating payroll.'}</div>`;
          }
        } catch (error) {
          msg.innerHTML = `<div class="alert alert-danger py-2 small">Server error occurred. Check console.</div>`;
        } finally {
          btn.disabled = false;
          btn.innerHTML = originalText;
        }
      });
    <?php endif; ?>

    // view details
    function viewRun(id) {
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
                        <p class="mt-2">Loading Payroll Data...</p>
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
                            
                            // Check print permissions via PHP injection into JS string
                            <?php if ($canPrint): ?>
                                html += '<button onclick="window.print()" class="btn btn-secondary btn-sm">Print Master Report</button>';
                            <?php endif; ?>
                            
                            html += '</div>';
                            html += '<table class="table table-bordered table-striped bg-white align-middle">';
                            html += '<thead class="table-dark"><tr><th>Employee</th><th>Gross Pay</th><th>Deductions/Adv</th><th>Net Pay</th><th class="text-center">Action</th></tr></thead>';
                            html += '<tbody>';

                            data.data.forEach(row => {
                                html += '<tr>';
                                html += '<td class="fw-bold">' + row.first_name + ' ' + row.last_name + '</td>';
                                html += '<td>₱' + Number(row.gross_pay).toFixed(2) + '</td>';
                                html += '<td class="text-danger">- ₱' + Number(row.cash_advance).toFixed(2) + '</td>';
                                html += '<td class="fw-bold text-success fs-5">₱' + Number(row.net_pay).toFixed(2) + '</td>';
                                
                                <?php if ($canPrint): ?>
                                    html += '<td class="text-center"><a href="../../backend/payroll/payslip.php?payroll_id=${id}&employee_id=' + row.employee_id + '" target="_blank" class="btn btn-sm btn-dark">Print Payslip</a></td>';
                                <?php else: ?>
                                    html += '<td class="text-center"><span class="text-muted small">Restricted</span></td>';
                                <?php endif; ?>
                                    
                                html += '</tr>';
                            });

                            html += '</tbody></table>';
                            root.innerHTML = html;
                            root.classList.remove('text-center', 'mt-5'); // Align top-left
                        } catch (e) {
                            document.getElementById('root').innerHTML = '<div class="alert alert-danger">Critical Error loading data.</div>';
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