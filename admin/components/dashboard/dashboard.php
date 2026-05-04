<?php
// Database connection
if (!isset($conn)) {
    if (file_exists('backend/config.php')) {
        include('backend/config.php');
    } else {
        include('../../backend/config.php');
    }
}

/** 
 * 1. TOTAL INCOME 
 */
$sql_income = "SELECT SUM(amount) as total_received FROM manager_income";
$result_income = $conn->query($sql_income);
$display_income = ($result_income && $row = $result_income->fetch_assoc()) ? ($row['total_received'] ?? 0) : 0;

/** 
 * 2. MANAGER EXPENSES (General)
 */
$expense_query = "SELECT SUM(amount) as total_gen_expense FROM manager_expenses";
$expense_result = $conn->query($expense_query);
$manager_expense = ($expense_result && $row = $expense_result->fetch_assoc()) ? ($row['total_gen_expense'] ?? 0) : 0;

/** 
 * 3. ACTUAL PAID SALARY (Cash Outflow)
 * Hum wo 'paid_amount' sum kar rahe hain jo staff ko di ja chuki hai (Paid + Partially Paid)
 */
$salary_paid_query = "SELECT SUM(paid_amount) as total_cash_out FROM salaries WHERE payment_status IN ('Paid', 'Partially Paid')";
$salary_paid_result = $conn->query($salary_paid_query);
$total_cash_out_salary = ($salary_paid_result && $row = $salary_paid_result->fetch_assoc()) ? ($row['total_cash_out'] ?? 0) : 0;

/** 
 * 4. TOTAL EXPENSE & PROFIT CALCULATION
 */
$total_expense = $total_cash_out_salary + $manager_expense;
$net_profit = $display_income - $total_expense;

/**
 * 5. PENDING LIABILITIES
 * Un sab records ka 'remaining_balance' jo mukammal 'Paid' nahi hain
 */
$pending_query = "SELECT SUM(remaining_balance) as total_liabilities FROM salaries WHERE payment_status != 'Paid'";
$pending_result = $conn->query($pending_query);
$display_pending = ($pending_result && $row = $pending_result->fetch_assoc()) ? ($row['total_liabilities'] ?? 0) : 0;

// Styling Logic
if ($net_profit < 0) {
    $card_bg = "linear-gradient(135deg, #fecaca 0%, #ef4444 100%)";
    $text_color = "#ffffff";
    $card_label = "Total Loss";
} else {
    $card_bg = "linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)";
    $card_label = "Net Profit";
    $text_color = "#ffffff";
}
?>

<div class="row g-4 mb-5">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm p-4 h-100"
            style="background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%); border: 1px solid rgba(59, 130, 246, 0.1) !important; border-radius: 24px;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div
                    style="background: #eff6ff; color: #3b82f6; width: 56px; height: 56px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 8px 16px rgba(59, 130, 246, 0.1);">
                    <i class="ri-wallet-3-line"></i>
                </div>
                <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2"
                    style="font-size: 0.75rem;">
                    <i class="ri-arrow-up-line"></i> 12%
                </span>
            </div>
            <p class="text-secondary mb-1 fw-medium" style="font-size: 0.9rem;">Total Income</p>
            <h3 class="fw-bold m-0 text-dark"><?php echo number_format($display_income); ?> <span class="fs-6 fw-normal text-muted">PKR</span>
            </h3>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm p-4 h-100"
            style="background: linear-gradient(135deg, #ffffff 0%, #fff1f2 100%); border: 1px solid rgba(239, 68, 68, 0.1) !important; border-radius: 24px;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div
                    style="background: #fef2f2; color: #ef4444; width: 56px; height: 56px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 8px 16px rgba(239, 68, 68, 0.1);">
                    <i class="ri-refund-2-line"></i>
                </div>
                <span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2"
                    style="font-size: 0.75rem;">
                    <i class="ri-arrow-down-line"></i> 5%
                </span>
            </div>
            <p class="text-secondary mb-1 fw-medium" style="font-size: 0.9rem;">Total Expenses</p>
            <h3 class="fw-bold m-0 text-dark"><?php echo number_format($total_expense); ?> <span class="fs-6 fw-normal text-muted">PKR</span>
            </h3>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm p-4 h-100"
            style="background: <?php echo $card_bg; ?>; border-radius: 24px; color: <?php echo $text_color; ?>; transition: all 0.5s ease;">

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div style="background: rgba(255, 255, 255, 0.2); color: white; width: 56px; height: 56px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; backdrop-filter: blur(4px);">
                    <i class="<?php echo ($net_profit < 0) ? 'ri-error-warning-line' : 'ri-line-chart-line'; ?>"></i>
                </div>
                <i class="ri-more-2-fill opacity-50"></i>
            </div>

            <p class="opacity-75 mb-1 fw-medium" style="font-size: 0.9rem;">
                <?php echo $card_label; ?>
            </p>

            <h3 class="fw-bold m-0">
                <?php echo number_format($net_profit); ?>
                <span class="fs-6 fw-normal opacity-75">PKR</span>
            </h3>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm p-4 h-100" style="background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%); border: 1px solid rgba(245, 158, 11, 0.1) !important; border-radius: 24px;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div style="background: #fffbeb; color: #f59e0b; width: 56px; height: 56px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 8px 16px rgba(245, 158, 11, 0.1);">
                    <i class="ri-history-line"></i>
                </div>
                <?php if ($display_pending > 0): ?>
                    <span class="text-warning small fw-bold">Action Needed</span>
                <?php endif; ?>
            </div>
            <p class="text-secondary mb-1 fw-medium" style="font-size: 0.9rem;">Pending Salaries</p>
            <h3 class="fw-bold m-0 text-dark"><?php echo number_format($display_pending); ?> <span class="fs-6 fw-normal text-muted">PKR</span></h3>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-xl-8">
        <!-- dashboard.php mein isay replace karein -->
        <!-- Is container ko lazmi height dein -->
        <div class="card table-modern p-4">
            <h5 class="fw-semibold mb-4 text-dark">Fuel Cost Trend</h5>
            <div class="chart-box" style="position: relative; height: 350px; width: 100%;">
                <!-- Style attribute add kiya gaya hai -->
                <canvas id="fuelTrendChart" style="width: 100%; height: 100%;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card table-modern p-4 h-100">
            <h5 class="fw-semibold mb-4 text-dark">Quick Actions</h5>
            <div class="d-grid gap-3">
                <!-- Add New Income -->
                <button onclick="loadContent('components/expenses/manager-income.php', event)" class="btn btn-modern btn-modern-primary">
                    <i class="ri-add-circle-line me-2"></i> Add New Income
                </button>

                <!-- Record Expense -->
                <button onclick="loadContent('components/expenses/expense-history.php', event)" class="btn btn-modern btn-modern-outline text-danger">
                    <i class="ri-indeterminate-circle-line me-2"></i> Record Expense
                </button>

                <!-- Record Fuel -->
                <button onclick="loadContent('components/fuel/record_fuel.php', event)" class="btn btn-modern btn-modern-outline">
                    <i class="ri-gas-station-fill me-2"></i> Record Fuel
                </button>

                <!-- Generate Monthly Report -->
                <button onclick="loadContent('components/reports/monthly_report.php', event)" class="btn btn-modern btn-modern-outline">
                    <i class="ri-file-list-3-line me-2"></i> Generate Monthly Report
                </button>
            </div>
        </div>
    </div>
</div>

<div class="table-modern">
    <div class="p-4 d-flex justify-content-between align-items-center border-bottom border-light">
        <h5 class="fw-semibold m-0 text-dark">Recent Transactions</h5>
        <button class="btn btn-sm btn-modern-outline">View All</button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount (PKR)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>18 Apr, 2026</td>
                    <td><span
                            class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">Machinery</span>
                    </td>
                    <td class="text-secondary">Rental - Burewala Site (Dumper)</td>
                    <td class="fw-semibold text-success">+ 45,000</td>
                    <td><span class="d-flex align-items-center text-success"><i
                                class="ri-checkbox-circle-fill me-1"></i> Completed</span></td>
                </tr>
                <tr>
                    <td>17 Apr, 2026</td>
                    <td><span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2">Fuel</span>
                    </td>
                    <td class="text-secondary">Diesel Refill - Dumper 10cm (BRW-789)</td>
                    <td class="fw-semibold text-danger">- 12,000</td>
                    <td><span class="d-flex align-items-center text-success"><i
                                class="ri-checkbox-circle-fill me-1"></i> Completed</span></td>
                </tr>
                <tr>
                    <td>16 Apr, 2026</td>
                    <td><span class="badge bg-info-subtle text-info rounded-pill px-3 py-2">Pump</span></td>
                    <td class="text-secondary">Daily Revenue - Alipur Station</td>
                    <td class="fw-semibold text-success">+ 85,000</td>
                    <td><span class="d-flex align-items-center text-warning"><i
                                class="ri-time-fill me-1"></i> Pending</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>