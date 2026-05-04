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

$is_edit = false;
$id = "";
$name = "";
$r_code = "";
$status = "";

// Agar URL mein 'id' hai, toh matlab hum edit kar rahe hain
if (isset($_GET['id'])) {
    $is_edit = true;
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    $res = $conn->query("SELECT * FROM districts WHERE district_id = '$id'");
    if ($res->num_rows > 0) {
        $data = $res->fetch_assoc();
        $name = $data['district_name'];
        $r_code = $data['region_code'];
        $status = $data['status'];
    }
}
?>

<div class="row">
    <div class="col-md-6">
        <h3 class="fw-bold m-0 text-dark"><?php echo $is_edit ? "Edit District" : "Add New District"; ?></h3>
        <p class="text-muted small"><?php echo $is_edit ? "Update the details of the selected district" : "Enter the details below to register a new district"; ?></p>

    </div>
    <div class="col-md-6 d-flex justify-content-end align-items-center">
        <button class="btn btn-light border shadow-sm px-4 py-2 rounded-pill d-flex align-items-center gap-2"
            onclick="loadContent('components/district/district.php')"
            style="transition: all 0.3s ease; font-weight: 500;"
            onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.transform='translateY(-2px)';"
            onmouseout="this.style.backgroundColor='#ffffff'; this.style.transform='translateY(0)';">
            <i class="ri-arrow-left-line text-primary" style="font-size: 18px;"></i>
            <span class="text-dark">Back</span>
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm p-4" style="border-radius: 24px;">
            <form id="districtForm" action="../admin/components/district/process.php" method="POST">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="district_id" value="<?php echo $id; ?>">
                <?php endif; ?>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small">District Name</label>
                        <input type="text" name="district_name" class="form-control py-2"
                            value="<?php echo $name; ?>" placeholder="e.g. Vehari" required
                            style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div class="invalid-feedback">District name is required.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small">Region Code</label>
                        <input type="text" name="region_code" class="form-control py-2"
                            value="<?php echo $r_code; ?>" placeholder="e.g. VH-01" required
                            style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div class="invalid-feedback">Please provide a region code.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small">Status</label>
                        <select name="status" class="form-select py-2" required
                            style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                            <option value="active" <?php echo ($status == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($status == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="col-12 mt-5 d-flex gap-2">
                        <?php if ($is_edit): ?>
                            <button type="submit" name="update_district" class="btn btn-modern btn-modern-primary px-5">
                                Update District
                            </button>
                        <?php else: ?>
                            <button type="submit" name="save_district" class="btn btn-modern btn-modern-primary px-5">
                                Save District
                            </button>
                        <?php endif; ?>

                        <button type="button" class="btn btn-modern btn-modern-outline px-4"
                            onclick="loadContent('components/district/district.php')">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card border-0 shadow-sm p-4 bg-primary-subtle"
            style="border-radius: 24px; border: 1px dashed #3b82f6 !important;">
            <h5 class="fw-bold text-primary mb-3"><i class="ri-information-line"></i> Quick Tips</h5>
            <ul class="small text-secondary ps-3">
                <li class="mb-2">Ensure the District Name is unique.</li>
                <li class="mb-2">Region codes are used for internal tracking.</li>
                <li>You can add Tehsils specifically after creating the district.</li>
            </ul>
        </div>
    </div>
</div>