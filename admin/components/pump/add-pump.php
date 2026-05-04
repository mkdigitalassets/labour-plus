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

$isEdit = false;
$id = $pump_name = $owner_id = $contact_no = $status = $district_id = $tehsil_id = $address = $petrol_capacity = $diesel_capacity = "";

// 1. Check if Edit Mode
if (isset($_GET['id'])) {
    $isEdit = true;
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Pump ka data fetch karna
    $res = $conn->query("SELECT * FROM pumps WHERE pump_id = '$id'");
    if ($row = $res->fetch_assoc()) {
        $pump_name       = $row['pump_name'];
        $owner_id        = $row['owner_id'];
        $contact_no      = $row['contact_no'];
        $status          = $row['status'];
        $district_id     = $row['district_id'];
        $tehsil_id       = $row['tehsil_id'];
        $address         = $row['address'];
        $petrol_capacity = $row['petrol_capacity'];
        $diesel_capacity = $row['diesel_capacity'];
    }
}
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold m-0 text-dark"><?php echo $isEdit ? 'Edit Pump Details' : 'Register New Pump'; ?></h3>
            <p class="text-muted small m-0 pt-3">Enter pump details and link them with owners and locations.</p>
        </div>
        <button class="btn btn-outline-secondary rounded-pill px-4" onclick="loadContent('components/pump/pump-detail.php')">
            <i class="ri-arrow-left-line me-1"></i> Back to List
        </button>
    </div>

    <form id="addPumpForm">
        <input type="hidden" name="pump_id" value="<?php echo $id; ?>">

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm p-4" style="border-radius: 24px;">
                    <h5 class="fw-bold mb-4 text-primary"><i class="ri-information-fill"></i> Pump Basic Details</h5>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Pump Name / Title</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="ri-gas-station-line"></i></span>
                                <input type="text" name="pump_name" class="form-control bg-light border-start-0"
                                    placeholder="e.g. Al-Makkah Petroleum" value="<?php echo $pump_name; ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assign Owner</label>
                            <select name="owner_id" class="form-select select2-basic" required>
                                <option value="" disabled <?php echo !$isEdit ? 'selected' : ''; ?>>Select Owner...</option>
                                <?php
                                $owner_res = $conn->query("SELECT owner_id, owner_name FROM owners ORDER BY owner_name ASC");
                                while ($owner = $owner_res->fetch_assoc()) {
                                    $sel = ($owner['owner_id'] == $owner_id) ? "selected" : "";
                                    echo "<option value='" . $owner['owner_id'] . "' $sel>" . $owner['owner_name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Number</label>
                            <input type="tel" name="contact_no" class="form-control bg-light" value="<?php echo $contact_no; ?>" placeholder="+92 3xx xxxxxxx">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select bg-light">
                                <option value="active" <?php echo ($status == 'active') ? 'selected' : ''; ?>>Active / Operational</option>
                                <option value="inactive" <?php echo ($status == 'inactive') ? 'selected' : ''; ?>>Inactive / Pending</option>
                                <option value="maintenance" <?php echo ($status == 'maintenance') ? 'selected' : ''; ?>>Under Maintenance</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4 mt-4" style="border-radius: 24px;">
                    <h5 class="fw-bold mb-4 text-success"><i class="ri-map-pin-user-fill"></i> Location & Territory</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">District</label>
                            <select name="district_id" class="form-select" id="districtSelect" required>
                                <option value="" disabled <?php echo !$isEdit ? 'selected' : ''; ?>>Choose District...</option>
                                <?php
                                $dist_res = $conn->query("SELECT district_id, district_name FROM districts ORDER BY district_name ASC");
                                while ($dist = $dist_res->fetch_assoc()) {
                                    $sel = ($dist['district_id'] == $district_id) ? "selected" : "";
                                    echo "<option value='" . $dist['district_id'] . "' $sel>" . $dist['district_name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tehsil</label>
                            <select name="tehsil_id" class="form-select" id="tehsilSelect" required>
                                <option value="" disabled <?php echo !$isEdit ? 'selected' : ''; ?>>Select Tehsil...</option>
                                <?php
                                $teh_res = $conn->query("SELECT tehsil_id, tehsil_name FROM tehsils ORDER BY tehsil_name ASC");
                                while ($teh = $teh_res->fetch_assoc()) {
                                    $sel = ($teh['tehsil_id'] == $tehsil_id) ? "selected" : "";
                                    echo "<option value='" . $teh['tehsil_id'] . "' $sel>" . $teh['tehsil_name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Full Address / Landmark</label>
                            <textarea name="address" class="form-control bg-light" rows="2"
                                placeholder="Exact location details..."><?php echo $address; ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 24px; background: #f8fafc;">
                    <h5 class="fw-bold mb-3">Storage Capacity</h5>
                    <div class="mb-3">
                        <label class="small text-muted">Petrol Tank (Ltrs)</label>
                        <input type="number" name="petrol_capacity" class="form-control" value="<?php echo $petrol_capacity; ?>" placeholder="e.g. 15000">
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted">Diesel Tank (Ltrs)</label>
                        <input type="number" name="diesel_capacity" class="form-control" value="<?php echo $diesel_capacity; ?>" placeholder="e.g. 15000">
                    </div>
                    <hr>
                    <div class="alert alert-info border-0 small">
                        <i class="ri-lightbulb-line me-1"></i> Storage details are used for inventory tracking.
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary py-3 fw-bold"
                        style="border-radius: 15px; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);">
                        <i class="ri-save-3-line me-1"></i> <?php echo $isEdit ? 'Update Pump Record' : 'Save Pump Record'; ?>
                    </button>
                    <button type="button" class="btn btn-light py-3 text-muted" style="border-radius: 15px;" onclick="loadContent('components/pump/pump-detail.php')">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>