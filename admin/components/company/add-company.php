<?php
if (!isset($conn)) {
    if (file_exists('backend/config.php')) {
        include('backend/config.php');
    } else {
        include('../../backend/config.php');
    }
}

// 1. Pehle $row ko default values ke sath initialize karein
$id = $_GET['id'] ?? '';
$isEdit = !empty($id);
$row = [
    'company_name' => '',
    'district_id' => '',
    'tehsil_id' => '',
    'status' => 'Active'
];

// 2. Agar ID mojood hai, to database se real data fetch karein
if ($isEdit) {
    $safe_id = $conn->real_escape_string($id);
    $res = $conn->query("SELECT * FROM companies WHERE company_id = '$safe_id'");

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
    }
}

$pageTitle = $isEdit ? "Edit Company" : "Add New Company";
?>

<style>
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        display: none;
    }
</style>

<div class="row mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold m-0 text-dark"><?php echo $pageTitle ?></h3>
    </div>
    <div class="col-md-6 d-flex justify-content-end align-items-center">
        <button class="btn btn-light border shadow-sm px-4 py-2 rounded-pill d-flex align-items-center gap-2"
            onclick="loadContent('components/company/company.php')"
            style="transition: all 0.3s ease; font-weight: 500;"
            onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.transform='translateY(-2px)';"
            onmouseout="this.style.backgroundColor='#ffffff'; this.style.transform='translateY(0)';">
            <i class="ri-arrow-left-line text-primary" style="font-size: 18px;"></i>
            <span class="text-dark">Back to List</span>
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
    <form id="companyForm">
        <input type="hidden" name="company_id" value="<?= $id ?>">
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark small">District</label>
                <select name="district_id" id="dist_select" class="form-select select2 py-2" required style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                    <option value="" class="text-dark">Select District</option>
                    <?php
                    $dists = $conn->query("SELECT * FROM districts WHERE status='Active'");
                    while ($d = $dists->fetch_assoc()) {
                        $sel = ($d['district_id'] == $row['district_id']) ? 'selected' : '';
                        echo "<option value='{$d['district_id']}' $sel>{$d['district_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark small">Tehsil</label>
                <select name="tehsil_id" id="teh_select" class="form-select select2 py-2" required style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                    <option value="">First Select District</option>
                    <?php if ($id):
                        $tehs = $conn->query("SELECT * FROM tehsils WHERE district_id='{$row['district_id']}'");
                        while ($t = $tehs->fetch_assoc()) {
                            $sel = ($t['tehsil_id'] == $row['tehsil_id']) ? 'selected' : '';
                            echo "<option value='{$t['tehsil_id']}' $sel>{$t['tehsil_name']}</option>";
                        }
                    endif; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark small">Company Name</label>
                <input type="text" name="company_name" class="form-control py-2" value="<?= $row['company_name'] ?>" required style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark small">Status</label>
                <select name="status" class="form-select py-2" style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                    <option value="Active" <?= $row['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= $row['status'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-12 mt-4 ">
                <button type="submit" class="btn btn-primary px-5 py-2 shadow-sm" style="border-radius: 12px; background: #6366f1; border: none;">Save Company</button>
                <button type="button" class="btn btn-primary px-4 py-2 shadow-sm " style="border-radius: 12px; background-color: white !important; border: 1px solid #e2e8f0; color: #475569;"
                    onclick="loadContent('components/company/company.php')">
                    Cancel
                </button>
            </div>
        </div>
    </form>
</div>