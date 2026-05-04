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

$id = $_GET['id'] ?? '';
// Initializing all fields to avoid "undefined index" errors
$row = [
    'full_name' => '',
    'cnic' => '',
    'contact_number' => '',
    'account_number' => '',
    'account_title' => '',
    'account_type' => '',
    'district_id' => '',
    'tehsil_id' => '',
    'status' => 'active'
];

if ($id) {
    $row = $conn->query("SELECT * FROM vehicle_owners WHERE owner_id='$id'")->fetch_assoc();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0 text-dark">
        <i class="ri-user-add-line me-2 text-primary"></i>
        <?php echo (empty($owner_id)) ? "Register New Vehicle Owner" : "Edit Vehicle Owner Details"; ?>
    </h4>
    <button class="btn btn-light border rounded-pill px-4 shadow-sm"
        onclick="loadContent('components/owner/owner.php')"
        style="transition: all 0.3s ease;">
        <i class="ri-arrow-left-line me-1"></i> Back to List
    </button>
</div>

<div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
    <form id="ownerForm">
        <input type="hidden" name="owner_id" value="<?= $id ?>">

        <div class="row g-3">
            <div class="col-12 border-bottom pb-2 mb-2">
                <h6 class="fw-bold text-primary"><i class="ri-user-line me-2"></i>Personal Information</h6>
            </div>

            <div class="col-md-6">
                <label class="small fw-semibold form-label small text-muted">District</label>
                <select name="district_id" id="own_dist" class="form-select py-2" required style="border-radius: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0;">
                    <option value="">Select District</option>
                    <?php
                    $dists = $conn->query("SELECT * FROM districts WHERE status='active'");
                    while ($d = $dists->fetch_assoc()) {
                        $sel = ($d['district_id'] == $row['district_id']) ? 'selected' : '';
                        echo "<option value='{$d['district_id']}' $sel>{$d['district_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="fw-semibold form-label small text-muted">Tehsil</label>
                <select name="tehsil_id" id="own_teh" class="form-select py-2" required style="border-radius: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0;">
                    <option value="">First Select District</option>
                    <?php if ($id) {
                        $tehs = $conn->query("SELECT * FROM tehsils WHERE district_id='{$row['district_id']}'");
                        while ($t = $tehs->fetch_assoc()) {
                            $sel = ($t['tehsil_id'] == $row['tehsil_id']) ? 'selected' : '';
                            echo "<option value='{$t['tehsil_id']}' $sel>{$t['tehsil_name']}</option>";
                        }
                    } ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="fw-semibold form-label small text-muted">Full Name</label>
                <input type="text" name="full_name" class="form-control py-2" value="<?= $row['full_name'] ?>" required style="border-radius: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0;">
            </div>

            <div class="col-md-4">
                <label class="fw-semibold form-label small text-muted">CNIC</label>
                <input type="text" name="cnic" class="form-control py-2" value="<?= $row['cnic'] ?>" placeholder="36601-xxxxxxx-x" required style="border-radius: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0;">
            </div>

            <div class="col-md-4">
                <label class="fw-semibold form-label small text-muted">Contact Number</label>
                <input type="text" name="contact_number" class="form-control py-2" value="<?= $row['contact_number'] ?>" placeholder="03---------" required style="border-radius: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0;">
            </div>

            <div class="col-12 border-bottom pb-2 mt-5 mb-2">
                <h6 class="fw-bold text-primary"><i class="ri-bank-card-line me-2"></i>Payment Details</h6>
            </div>

            <div class="col-md-4">
                <label class="fw-semibold form-label small text-muted">Account Title</label>
                <input type="text" name="account_title" class="form-control py-2" value="<?= $row['account_title'] ?>" placeholder="Account Holder Name" style="border-radius: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0;">
            </div>

            <div class="col-md-4">
                <label class="fw-semibold form-label small text-muted">Account / Wallet Number</label>
                <input type="text" name="account_number" class="form-control py-2" value="<?= $row['account_number'] ?>" placeholder="0300xxxxxxx or IBAN" style="border-radius: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0;">
            </div>

            <div class="col-md-4">
                <label class="fw-semibold form-label small text-muted">Account Type</label>
                <select name="account_type" class="form-select py-2" style="border-radius: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0;">>
                    <option value="Cash" <?= $row['account_type'] == 'Cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="EasyPaisa" <?= $row['account_type'] == 'EasyPaisa' ? 'selected' : '' ?>>EasyPaisa</option>
                    <option value="JazzCash" <?= $row['account_type'] == 'JazzCash' ? 'selected' : '' ?>>JazzCash</option>
                    <option value="HBL" <?= $row['account_type'] == 'HBL' ? 'selected' : '' ?>>HBL Bank</option>
                    <option value="UBL" <?= $row['account_type'] == 'UBL' ? 'selected' : '' ?>>UBL Bank</option>
                    <option value="Other Bank" <?= $row['account_type'] == 'Other Bank' ? 'selected' : '' ?>>Other Bank</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="fw-semibold form-label small text-muted">Status</label>
                <select name="status" class="form-select py-2" style="border-radius: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0;">>
                    <option value="active" <?= $row['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $row['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <div class="col-12 text-end mt-4">
                <button type="submit" class="btn btn-primary px-5 shadow-sm" style="background-color:#6366f1; border:none; border-radius:12px;">
                    <i class="ri-save-line me-1"></i> <?php echo (!empty($id)) ? "Update Owner" : "Register Owner"; ?>
                </button>
            </div>
        </div>
    </form>
</div>