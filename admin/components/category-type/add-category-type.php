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

$type_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
$row = ['type_name' => '', 'status' => 'Active'];

if (!empty($type_id)) {
    $res = $conn->query("SELECT * FROM expense_category_types WHERE type_id = '$type_id'");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
    }
}
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold m-0 text-dark"><?php echo $type_id ? 'Edit' : 'Add New'; ?> Category Type</h3>
    </div>
    <div class="col-md-6 d-flex justify-content-end align-items-center">
        <button class="btn btn-light border rounded-pill px-4 shadow-sm"
            onclick="loadContent('components/category-type/category-type.php')">
            <i class="ri-arrow-left-line me-1"></i> Back
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm p-4" style="border-radius: 24px;">
    <form id="categoryTypeForm">
        <input type="hidden" name="type_id" value="<?php echo $type_id; ?>">
        <input type="hidden" name="action" value="save_category_type">

        <div class="row g-4">
            <div class="col-md-8">
                <label class="form-label fw-semibold text-secondary small">Category Type Name</label>
                <input type="text" name="type_name" class="form-control py-2"
                    value="<?php echo $row['type_name']; ?>" placeholder="e.g. Fuel, Office, Maintenance" required
                    style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold text-secondary small">Status</label>
                <select name="status" class="form-select py-2" style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                    <option value="Active" <?php echo ($row['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo ($row['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="col-12 mt-4">
                <button type="submit" id="submitBtn" class="btn btn-primary px-5 py-2">
                    <i class="ri-save-line me-2"></i>Save Record
                </button>
            </div>
        </div>
    </form>
</div>

<script>

</script>