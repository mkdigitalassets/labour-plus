<?php
if (!isset($conn)) {
    // Agar $conn pehle se nahi bana (yani index.php se load nahi ho raha), to include karein
    // Hum file_exists check kar ke path set karte hain
    if (file_exists('backend/config.php')) {
        include('backend/config.php'); // Jab index.php se load ho
    } else {
        include('../../backend/config.php'); // Jab dashboard.php direct chale
    }
}

// 1. Pehle variables ko default values ke sath initialize karein (Error Se Bachne Ke Liye)
$salary_id = "";
$row = [
    'tehsil_id' => '',
    'staff_role' => '',
    'staff_id' => '',
    'district_id' => '',
    'salary_month' => '',
    'working_days' => '',
    'leaves' => '',
    'bonus_amount' => '',
    'fixed_salary' => '',
    'payment_method' => 'Cash',
    'bank_name' => '',
    'account_info' => '',
    'payment_status' => 'Pending'
];

// 2. Agar Edit ID milti hai toh database se data fetch karein
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $salary_id = mysqli_real_escape_string($conn, $_GET['id']);
    // Humne explicitly st.tehsil_id select kiya hai
    $edit_sql = "SELECT s.*, st.staff_name, st.tehsil_id, t.tehsil_name, t.district_id 
                 FROM salaries s 
                 LEFT JOIN staff st ON s.staff_id = st.staff_id 
                 LEFT JOIN tehsils t ON st.tehsil_id = t.tehsil_id
                 WHERE s.salary_id = '$salary_id'";
    $edit_res = $conn->query($edit_sql);
    if ($edit_res && $edit_res->num_rows > 0) {
        $row = $edit_res->fetch_assoc();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark"><?php echo $salary_id ? 'Update' : 'Pay'; ?> Staff Salary</h4>
        <p class="text-muted small m-0 pt-1">Manage monthly payments with bonus and deductions.</p>
    </div>
    <button class="btn btn-light border rounded-pill px-4 shadow-sm"
        onclick="loadContent('components/salary/salary-list.php')">
        <i class="ri-arrow-left-line me-1"></i> Back
    </button>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 20px;">
    <div class="card-body p-4 p-md-5">
        <form id="salaryForm">
            <input type="hidden" name="salary_id" value="<?php echo $salary_id; ?>">
            <input type="hidden" id="old_tehsil_val" value="<?php echo $row['tehsil_id']; ?>">
            <input type="hidden" id="old_role_val" value="<?php echo $row['staff_role']; ?>">
            <input type="hidden" id="old_staff_val" value="<?php echo $row['staff_id']; ?>">
            <div class="row g-4">

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Select District</label>
                    <select id="salary_district" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;" onchange="fetchTehsils(this.value)">
                        <option value="">-- Select District --</option>
                        <?php
                        $dist_sql = "SELECT * FROM districts WHERE status = 'Active'";
                        $dist_res = $conn->query($dist_sql);
                        while ($d = $dist_res->fetch_assoc()) {
                            // Agar edit mode hai toh sahi district select hoga
                            $selected = ($row['district_id'] == $d['district_id']) ? 'selected' : '';
                            echo "<option value='" . $d['district_id'] . "' $selected>" . $d['district_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Select Tehsil</label>
                    <select id="salary_tehsil" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">
                        <option value="">-- Select Tehsil --</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Staff Type</label>
                    <select name="staff_role" id="staff_role" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;" onchange="handleRoleChange(this.value)">
                        <option value="">-- Select Role --</option>
                        <option value="Manager" <?php echo ($row['staff_role'] == 'Manager') ? 'selected' : ''; ?>>Manager</option>
                        <option value="Driver" <?php echo ($row['staff_role'] == 'Driver') ? 'selected' : ''; ?>>Driver</option>
                        <option value="Operator" <?php echo ($row['staff_role'] == 'Operator') ? 'selected' : ''; ?>>Operator</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted text-uppercase">Staff Member Name</label>
                    <select name="staff_id" id="staff_name_list" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;" onchange="fetchFixedSalary(this.value)">
                        <option value="">-- Select Name --</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted text-uppercase">Salary Month</label>
                    <input type="date" name="salary_month" class="form-control border-0 shadow-sm bg-light" value="<?php echo $row['salary_month']; ?>" style="height: 50px; border-radius: 12px;">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Working Days</label>
                    <input type="number" name="working_days" class="form-control border-0 shadow-sm bg-light" placeholder="e.g. 26" value="<?php echo $row['working_days']; ?>" style="height: 50px; border-radius: 12px;">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Leaves</label>
                    <input type="number" name="leaves" class="form-control border-0 shadow-sm bg-light" placeholder="e.g. 4" value="<?php echo $row['leaves']; ?>" style="height: 50px; border-radius: 12px;">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-success text-uppercase">Bonus Amount</label>
                    <input type="number" name="bonus_amount" class="form-control border-0 shadow-sm bg-light" placeholder="0" value="<?php echo $row['bonus_amount']; ?>" style="height: 50px; border-radius: 12px;">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Fixed Salary (Base)</label>
                    <input type="number" name="fixed_salary" id="fixed_salary" class="form-control border-0 shadow-sm" readonly value="<?php echo $row['fixed_salary']; ?>" style="height: 50px; border-radius: 12px; background: #eef2ff; font-weight: bold;">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-danger text-uppercase"> Deduction</label>
                    <input type="number" name="deduction_amount" id="deduction_amount" class="form-control border-0 shadow-sm"
                        readonly style="height: 50px; border-radius: 12px; background: #fff5f5; color: #e11d48; font-weight: bold;" placeholder="0">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-dark text-uppercase">Total Salary Payable</label>
                    <input type="number" id="total_payable_readonly" class="form-control border-0 shadow-sm" readonly
                        style="height: 50px; border-radius: 12px; background: #f0fdf4; color: #166534; font-weight: 800;" placeholder="0" value="<?php echo $row['net_salary'] ?? ''; ?>">
                    <input type="hidden" name="net_salary" id="net_salary_hidden" value="<?php echo $row['net_salary'] ?? ''; ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-uppercase">Amount Paid Now</label>
                    <input type="number" name="paid_amount" id="paid_amount" class="form-control border-0 shadow-sm" required
                        style="height: 50px; border-radius: 12px; border: 2px solid #6365f16d !important;" placeholder="Enter amount given" value="<?php echo $row['paid_amount'] ?? ''; ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-danger text-uppercase">Remaining Balance</label>
                    <input type="number" name="remaining_balance" id="remaining_balance" class="form-control border-0 shadow-sm" readonly
                        style="height: 50px; border-radius: 12px; background: #fff1f2; color: #be123c; font-weight: bold;" placeholder="0" value="<?php echo $row['remaining_balance'] ?? ''; ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">
                        <option value="Cash" <?php echo ($row['payment_method'] == 'Cash') ? 'selected' : ''; ?>>Cash</option>
                        <option value="Bank" <?php echo ($row['payment_method'] == 'Bank') ? 'selected' : ''; ?>>Bank Transfer</option>
                    </select>
                </div>

                <div id="bankFields" class="row d-flex g-3 <?php echo ($row['payment_method'] == 'Bank') ? '' : 'd-none'; ?> mt-1">
                    <div class="col-md-6">
                        <div class="p-3 border rounded-4 bg-white shadow-sm">
                            <label class="form-label small fw-bold text-muted">BANK NAME</label>
                            <input type="text" name="bank_name" value="<?php echo $row['bank_name']; ?>" class="form-control border-0 bg-light" placeholder="HBL, UBL etc." style="border-radius: 8px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-4 bg-white shadow-sm">
                            <label class="form-label small fw-bold text-muted">ACC / IBAN #</label>
                            <input type="text" name="account_info" value="<?php echo $row['account_info']; ?>" class="form-control border-0 bg-light" placeholder="Account details" style="border-radius: 8px;">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted text-uppercase">Payment Status</label>
                    <select name="payment_status" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">
                        <option value="Pending" <?php echo ($row['payment_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="Paid" <?php echo ($row['payment_status'] == 'Paid') ? 'selected' : ''; ?>>Fully Paid</option>
                        <option value="Partial" <?php echo ($row['payment_status'] == 'Partial Paid') ? 'selected' : ''; ?>>Partially Paid</option>
                    </select>
                </div>

                <div class="col-12 text-end pt-3">
                    <hr class="opacity-5">
                    <button type="submit" class="btn btn-success px-5 py-2 rounded-pill shadow">
                        <i class="ri-save-line me-1"></i> <?php echo $salary_id ? 'Update Record' : 'Save Record'; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>