<?php
// 1. Connection Include
if (!isset($conn)) {
    // Agar $conn pehle se nahi bana (yani index.php se load nahi ho raha), to include karein
    // Hum file_exists check kar ke path set karte hain
    if (file_exists('backend/config.php')) {
        include('backend/config.php'); // Jab index.php se load ho
    } else {
        include('../../backend/config.php'); // Jab dashboard.php direct chale
    }
}

// 2. Initialize Variables (For Add/Edit Mode)
$tehsil_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
$row = ['tehsil_name' => '', 'district_id' => '', 'status' => 'Active'];

if (!empty($tehsil_id)) {
    $res = $conn->query("SELECT * FROM tehsils WHERE tehsil_id = '$tehsil_id'");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
    }
}
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold m-0 text-dark"><?php echo $tehsil_id ? 'Edit' : 'Add New'; ?> Tehsil</h3>
        <p class="text-muted small">Register or update tehsil details under a district</p>
    </div>
    <div class="col-md-6 d-flex justify-content-end align-items-center">
        <button class="btn btn-light border shadow-sm px-4 py-2 rounded-pill d-flex align-items-center gap-2"
            onclick="loadContent('components/tehsil/tehsil.php')">
            <i class="ri-arrow-left-line text-primary"></i>
            <span class="text-dark">Back to List</span>
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm p-4" style="border-radius: 24px;">
            <form id="tehsilForm" novalidate>
                <input type="hidden" name="tehsil_id" value="<?php echo $tehsil_id; ?>">

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small">Tehsil Name</label>
                        <input type="text" name="tehsil_name" class="form-control py-2"
                            placeholder="e.g. Burewala" required
                            value="<?php echo $row['tehsil_name']; ?>"
                            style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small">Assign District</label>
                        <select name="district_id" class="form-select py-2" required
                            style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                            <option value="" disabled <?php echo empty($row['district_id']) ? 'selected' : ''; ?>>Choose District...</option>
                            <?php
                            $districts = $conn->query("SELECT district_id, district_name FROM districts WHERE status='Active'");
                            if ($districts && $districts->num_rows > 0) {
                                while ($d = $districts->fetch_assoc()) {
                                    $selected = ($row['district_id'] == $d['district_id']) ? 'selected' : '';
                                    echo "<option value='" . $d['district_id'] . "' $selected>" . $d['district_name'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small">Status</label>
                        <select name="status" class="form-select py-2" style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                            <option value="Active" <?php echo ($row['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo ($row['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="col-12 mt-5">
                        <button type="submit" class="btn btn-primary px-5 py-2 shadow-sm d-flex align-items-center gap-2"
                            id="submitBtn" style="border-radius: 12px; background: #6366f1; border: none;">
                            <i class="ri-save-line"></i>
                            <span><?php echo $tehsil_id ? 'Update Record' : 'Save Tehsil'; ?></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card border-0 shadow-lg p-4 text-white"
            style="border-radius: 30px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);">
            <h5 class="fw-bold mb-3"><i class="ri-information-line me-2"></i> Quick Guide</h5>
            <ul class="list-unstyled small mb-0">
                <li class="mb-2"><i class="ri-check-line me-1"></i> Ensure District is selected.</li>
                <li class="mb-2"><i class="ri-check-line me-1"></i> Tehsil name must be unique.</li>
                <li><i class="ri-check-line me-1"></i> Set status to Active for visibility.</li>
            </ul>
        </div>
    </div>
</div>