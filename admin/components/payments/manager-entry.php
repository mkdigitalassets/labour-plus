<?php
if (!isset($conn)) {
    include(file_exists('backend/config.php') ? 'backend/config.php' : '../../backend/config.php');
}

$payment_id = $_GET['id'] ?? "";
$row = [
    'payment_id' => '', 'manager_id' => '', 'tehsil_id' => '', 'district_id' => '',
    'payment_date' => date('Y-m-d'), 'amount' => '', 
    'payment_method' => 'Cash', 'purpose' => '', 'payment_proof' => '',
    'bank_name' => '', 'account_info' => ''
];

if (!empty($payment_id)) {
    // Query updated: employees ki jagah 'staff' table use ki hai
    $res = $conn->query("SELECT p.*, st.tehsil_id, t.district_id 
                         FROM manager_payments p 
                         LEFT JOIN staff st ON p.manager_id = st.staff_id 
                         LEFT JOIN tehsils t ON st.tehsil_id = t.tehsil_id
                         WHERE p.payment_id = '$payment_id'");
    if ($res && $res->num_rows > 0) $row = $res->fetch_assoc();
}
?>

<div class="card border-0 shadow-sm" style="border-radius: 20px;">
    <div class="card-body p-4 p-md-5">
        <!-- Form Tag with Data Attributes for JS -->
        <form id="managerPaymentForm" enctype="multipart/form-data"
              data-edit-mode="<?= !empty($row['payment_id']) ? 'true' : 'false' ?>"
              data-dist="<?= $row['district_id'] ?? '' ?>"
              data-teh="<?= $row['tehsil_id'] ?? '' ?>"
              data-mgr="<?= $row['manager_id'] ?? '' ?>">
            
            <input type="hidden" name="payment_id" value="<?= $row['payment_id'] ?? '' ?>">
            
            <div class="row g-4">
                <!-- Row 1: District, Tehsil, Role -->
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">District</label>
                    <select id="salary_district" name="district_id" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">
                        <option value="">-- Select District --</option>
                        <?php
                        $d_res = $conn->query("SELECT * FROM districts WHERE status = 'Active'");
                        while ($d = $d_res->fetch_assoc()) {
                            // Column 'district_id' use kiya hai dropdown values ke liye
                            $sel = ($row['district_id'] == $d['district_id']) ? 'selected' : '';
                            echo "<option value='{$d['district_id']}' $sel>{$d['district_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Tehsil</label>
                    <select id="salary_tehsil" name="tehsil_id" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">
                        <option value="">-- Select Tehsil --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Staff Type</label>
                    <select id="staff_role" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;" disabled>
                        <option value="Manager" selected>Manager</option>
                    </select>
                </div>

                <!-- Row 2: Manager Name, Date -->
                <div class="col-md-8">
                    <label class="form-label small fw-bold text-muted text-uppercase">Manager Name</label>
                    <select name="manager_id" id="staff_name_list" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;" required>
                        <option value="">-- Select Name --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Date</label>
                    <input type="date" name="payment_date" class="form-control border-0 shadow-sm bg-light" value="<?= $row['payment_date']; ?>" style="height: 50px; border-radius: 12px;">
                </div>

                <!-- Row 3: Amount, Method -->
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted text-uppercase">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control border-0 shadow-sm bg-light" value="<?= $row['amount']; ?>" style="height: 50px; border-radius: 12px;" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted text-uppercase">Method</label>
                    <select name="payment_method" id="payment_method" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">
                        <option value="Cash" <?= ($row['payment_method'] == 'Cash') ? 'selected' : ''; ?>>Cash</option>
                        <option value="Bank Transfer" <?= ($row['payment_method'] == 'Bank Transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                    </select>
                </div>

                <!-- Bank Details section -->
                <div id="bank_details_div" class="row g-3 mt-1 <?= ($row['payment_method'] == 'Bank Transfer') ? '' : 'd-none'; ?>">
                    <div class="col-md-6">
                        <div class="p-3 border rounded-4 bg-white shadow-sm">
                            <label class="small fw-bold text-muted">BANK NAME</label>
                            <input type="text" name="bank_name" class="form-control border-0 bg-light" value="<?= $row['bank_name'] ?>" placeholder="HBL, UBL etc.">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-4 bg-white shadow-sm">
                            <label class="small fw-bold text-muted">ACC / IBAN #</label>
                            <input type="text" name="account_info" class="form-control border-0 bg-light" value="<?= $row['account_info'] ?>" placeholder="Account details">
                        </div>
                    </div>
                </div>

                <!-- Proof Image Upload -->
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted text-uppercase">Payment Proof (Image)</label>
                    <div class="p-3 border rounded-4 bg-light shadow-sm d-flex align-items-center gap-3">
                        <input type="file" name="payment_proof" class="form-control border-0 bg-transparent">
                        <?php if(!empty($row['payment_proof'])): ?>
                            <a href="uploads/payments/<?= $row['payment_proof'] ?>" target="_blank" class="btn btn-sm btn-info text-white text-nowrap rounded-pill px-3">
                                <i class="ri-eye-line me-1"></i> View Proof
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold text-muted text-uppercase">Purpose / Remarks</label>
                    <textarea name="purpose" class="form-control border-0 shadow-sm bg-light" rows="3" style="border-radius: 12px;"><?= $row['purpose']; ?></textarea>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow">
                        <?= !empty($row['payment_id']) ? 'Update Payment' : 'Save Payment' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Bank Transfer toggle logic

</script>