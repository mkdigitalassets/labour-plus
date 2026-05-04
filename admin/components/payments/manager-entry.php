<?php
if (!isset($conn)) {
    include(file_exists('backend/config.php') ? 'backend/config.php' : '../../backend/config.php');
}

$payment_id = $_GET['id'] ?? "";
$row = [
    'manager_id' => '', 'tehsil_id' => '', 'district_id' => '',
    'payment_date' => date('Y-m-d'), 'amount' => '', 
    'payment_method' => 'Cash', 'purpose' => ''
];

if (!empty($payment_id)) {
    $payment_id = mysqli_real_escape_string($conn, $payment_id);
    $res = $conn->query("SELECT p.*, st.tehsil_id, t.district_id 
                         FROM manager_payments p 
                         LEFT JOIN staff st ON p.manager_id = st.staff_id 
                         LEFT JOIN tehsils t ON st.tehsil_id = t.tehsil_id
                         WHERE p.payment_id = '$payment_id'");
    if ($res && $res->num_rows > 0) $row = $res->fetch_assoc();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><?php echo $payment_id ? 'Update' : 'Add'; ?> Manager Payment</h4>
    <button class="btn btn-light border rounded-pill px-4" onclick="loadContent('components/payments/manager-list.php')">Back</button>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 20px;">
    <div class="card-body p-4">
        <form id="managerPaymentForm">
            <input type="hidden" name="payment_id" value="<?php echo $payment_id; ?>">
            <!-- Salary.js auto-fill triggers -->
            <input type="hidden" id="old_tehsil_val" value="<?php echo $row['tehsil_id']; ?>">
            <input type="hidden" id="old_role_val" value="Manager">
            <input type="hidden" id="old_staff_val" value="<?php echo $row['manager_id']; ?>">

            <div class="row g-4">
                <div class="col-md-4">
                    <label class="small fw-bold text-muted">DISTRICT</label>
                    <select id="salary_district" class="form-select bg-light border-0" style="height: 50px; border-radius: 12px;">
                        <option value="">-- Select District --</option>
                        <?php
                        $d_res = $conn->query("SELECT * FROM districts WHERE status = 'Active'");
                        while ($d = $d_res->fetch_assoc()) {
                            $sel = ($row['district_id'] == $d['district_id']) ? 'selected' : '';
                            echo "<option value='{$d['district_id']}' $sel>{$d['district_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="small fw-bold text-muted">TEHSIL</label>
                    <select id="salary_tehsil" class="form-select bg-light border-0" style="height: 50px; border-radius: 12px;">
                        <option value="">-- Select Tehsil --</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="small fw-bold text-muted">STAFF TYPE</label>
                    <select id="staff_role" class="form-select bg-light border-0" style="height: 50px; border-radius: 12px;" disabled>
                        <option value="Manager" selected>Manager</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="small fw-bold text-muted">MANAGER NAME</label>
                    <select name="manager_id" id="staff_name_list" class="form-select bg-light border-0" style="height: 50px; border-radius: 12px;" required>
                        <option value="">-- Select Name --</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="small fw-bold text-muted">DATE</label>
                    <input type="date" name="payment_date" class="form-control bg-light border-0" value="<?php echo $row['payment_date']; ?>" style="height: 50px; border-radius: 12px;">
                </div>

                <div class="col-md-6">
                    <label class="small fw-bold text-muted">AMOUNT</label>
                    <input type="number" name="amount" class="form-control bg-light border-0" placeholder="Enter Amount" value="<?php echo $row['amount']; ?>" style="height: 50px; border-radius: 12px;" required>
                </div>

                <div class="col-md-6">
                    <label class="small fw-bold text-muted">METHOD</label>
                    <select name="payment_method" class="form-select bg-light border-0" style="height: 50px; border-radius: 12px;">
                        <option value="Cash" <?php echo ($row['payment_method'] == 'Cash') ? 'selected' : ''; ?>>Cash</option>
                        <option value="Bank" <?php echo ($row['payment_method'] == 'Bank') ? 'selected' : ''; ?>>Bank Transfer</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="small fw-bold text-muted">PURPOSE / REMARKS</label>
                    <textarea name="purpose" class="form-control bg-light border-0" rows="3" style="border-radius: 12px;"><?php echo $row['purpose']; ?></textarea>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow">
                        <i class="ri-save-line me-1"></i> Save Payment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>