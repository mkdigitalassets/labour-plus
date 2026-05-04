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
$row = ['reg_no' => '', 'district_id' => '', 'tehsil_id' => '', 'owner_id' => '', 'company_id' => '', 'v_type_id' => '', 'meter_type' => 'KM', 'rental_status' => 'Non-Rental', 'company_rent' => 0, 'lp_rent' => 0, 'status' => 'active'];

if ($id) {
    $row = $conn->query("SELECT * FROM vehicles WHERE vehicle_id='$id'")->fetch_assoc();
}
?>

<div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
    <form id="vehicleForm">
        <input type="hidden" name="vehicle_id" value="<?= $id ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="small fw-bold">District</label>
                <select name="district_id" id="v_dist" class="form-select" required>
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

            <div class="col-md-4">
                <label class="small fw-bold">Tehsil</label>
                <select name="tehsil_id" id="v_teh" class="form-select" required>
                    <option value="">Select Tehsil</option>
                    <?php
                    if ($row['district_id']) {
                        $tehs = $conn->query("SELECT * FROM tehsils WHERE district_id='{$row['district_id']}' AND status='active'");
                        while ($t = $tehs->fetch_assoc()) {
                            $sel = ($t['tehsil_id'] == $row['tehsil_id']) ? 'selected' : '';
                            echo "<option value='{$t['tehsil_id']}' $sel>{$t['tehsil_name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="small fw-bold">Company</label>
                <select name="company_id" id="v_comp" class="form-select" required>
                    <option value="">Select Company</option>
                    <?php
                    if ($row['tehsil_id']) {
                        $comps = $conn->query("SELECT * FROM companies WHERE tehsil_id='{$row['tehsil_id']}' AND status='Active'");
                        while ($c = $comps->fetch_assoc()) {
                            $sel = ($c['company_id'] == $row['company_id']) ? 'selected' : '';
                            echo "<option value='{$c['company_id']}' $sel>{$c['company_name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="small fw-bold">Vehicle Type</label>
                <select name="v_type_id" class="form-select" required>
                     <option value="">----Select Type----</option>
                    <?php
                    $types = $conn->query("SELECT * FROM vehicle_types WHERE status='active'");
                    while ($t = $types->fetch_assoc()) {
                        $sel = ($t['v_type_id'] == $row['v_type_id']) ? 'selected' : '';
                        echo "<option value='{$t['v_type_id']}' $sel>{$t['description']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-8">
                <label class="small fw-bold">Vehicle Owner</label>
                <select name="owner_id" class="form-select" required>
                    <option value="">Select Owner</option>
                    <?php
                    $owners = $conn->query("SELECT * FROM vehicle_owners WHERE status='active'");
                    while ($o = $owners->fetch_assoc()) {
                        $sel = ($o['owner_id'] == $row['owner_id']) ? 'selected' : '';
                        echo "<option value='{$o['owner_id']}' $sel>{$o['full_name']} ({$o['cnic']})</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="small fw-bold">Registration No</label>
                <input type="text" name="reg_no" class="form-control" value="<?= $row['reg_no'] ?>" placeholder="LEA-1234" required>
            </div>

            <div class="col-md-4">
                <label class="small fw-bold">Working Unit</label>
                <select name="meter_type" class="form-select">
                    <option value="KM" <?= $row['meter_type'] == 'KM' ? 'selected' : '' ?>>Kilometers (KM)</option>
                    <option value="HR" <?= $row['meter_type'] == 'HR' ? 'selected' : '' ?>>Hours (HR)</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="small fw-bold">Rental Category</label>
                <select name="rental_status" id="rent_cat" class="form-select">
                    <option value="Non-Rental" <?= $row['rental_status'] == 'Non-Rental' ? 'selected' : '' ?>>Non-Rental</option>
                    <option value="Rental" <?= $row['rental_status'] == 'Rental' ? 'selected' : '' ?>>Rental Vehicle</option>
                    <option value="Exempted" <?= $row['rental_status'] == 'Exempted' ? 'selected' : '' ?>>Exempted Vehicle</option>
                </select>
            </div>

            <div id="rental_fields" class="row g-3 mt-1" style="display: <?= ($row['rental_status'] != 'Non-Rental') ? 'flex' : 'none' ?>;">
                <div class="col-md-6">
                    <label class="small fw-bold text-primary">Company Rent (Monthly)</label>
                    <input type="number" name="company_rent" class="form-control" value="<?= $row['company_rent'] ?>" step="0.01">
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold text-success">Labour Plus Rent (Monthly)</label>
                    <input type="number" name="lp_rent" class="form-control" value="<?= $row['lp_rent'] ?>" step="0.01">
                </div>
            </div>

            <div class="col-md-4">
                <label class="small fw-bold">Status</label>
                <select name="status" class="form-select">
                    <option value="active" <?= $row['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $row['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <div class="col-12 text-end mt-4">
                <button type="submit" class="btn btn-primary px-5 shadow" style="background:#6366f1; border:none; border-radius:12px;">
                    <?= $id ? 'Update Vehicle' : 'Register Vehicle' ?>
                </button>
            </div>
        </div>
    </form>
</div>