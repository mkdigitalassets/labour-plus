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

$v_type_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
$row = ['description' => '', 'fuel_type' => 'Diesel', 'status' => 'active'];

if (!empty($v_type_id)) {
    $res = $conn->query("SELECT * FROM vehicle_types WHERE v_type_id = '$v_type_id'");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
    }
}
?>
<div class="row mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold m-0 text-dark"><?php echo $v_type_id ? 'Edit' : 'Add New'; ?> Vehicle Type</h3>
        <p class="text-muted small pt-2">Register or update vehicle categories for the system</p>
    </div>
    <div class="col-md-6 d-flex justify-content-end align-items-center">
        <button class="btn btn-light border shadow-sm px-4 py-2 rounded-pill d-flex align-items-center gap-2"
            onclick="loadContent('components/vehicle_type/vehicle-type.php')">
            <i class="ri-arrow-left-line text-primary"></i>
            <span class="text-dark">Back to List</span>
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
    <form id="vTypeForm">
        <input type="hidden" name="v_type_id" value="<?php echo $v_type_id; ?>">

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Vehicle Type Name</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="ri-truck-line"></i></span>
                    <input type="text" name="description" class="form-control bg-light border-0 py-2"
                        value="<?php echo $row['description']; ?>" required placeholder="e.g. Dumper, Tractor" style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Fuel Type</label>
                <select name="fuel_type" class="form-select bg-light border-0 py-2" style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">>
                    <option value="Diesel" <?php echo ($row['fuel_type'] == 'Diesel') ? 'selected' : ''; ?>>Diesel</option>
                    <option value="Petrol" <?php echo ($row['fuel_type'] == 'Petrol') ? 'selected' : ''; ?>>Petrol</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold small text-muted">Status</label>
                <select name="status" class="form-select bg-light border-0 py-2" style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">>
                    <option value="active" <?php echo ($row['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($row['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="col-12 mt-4 text-end">
                <button type="submit" id="saveBtn" class="btn btn-primary px-5 py-2 shadow-sm"
                    style="border-radius: 12px; background: #6366f1; border:none; transition: 0.3s;">
                    <i class="ri-save-3-line me-1"></i> <span id="btnText">Save Vehicle Type</span>
                </button>
            </div>
        </div>
    </form>
</div>
