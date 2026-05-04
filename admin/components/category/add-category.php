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

$id = isset($_GET['id']) ? $_GET['id'] : '';
// Default values for new category
$row = ['category_name' => '', 'type_id' => '', 'status' => 'Active'];

if ($id) {
    // Edit mode: Fetch existing category details
    $res = $conn->query("SELECT * FROM expense_categories WHERE category_id = '$id'");
    if ($res) {
        $row = $res->fetch_assoc();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-4"><?php echo $id ? 'Edit' : 'Add New'; ?> Category</h4>
    </div>
    <button class="btn btn-light border rounded-pill px-4 shadow-sm"
        onclick="loadContent('components/category/category.php')">
        <i class="ri-arrow-left-line me-1"></i> Back
    </button>
</div>

<div class="card border-0 shadow-sm p-4" style="border-radius: 24px;">
    <form id="addCategoryForm"
        data-is-edit="<?php echo $id ? 'true' : 'false'; ?>"
        data-selected-type="<?php echo $row['type_id']; ?>">

        <input type="hidden" name="category_id" value="<?php echo $id; ?>">
        <input type="hidden" name="action" value="save_category">

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold small">1. Select Main Type</label>
                <select name="type_id" id="typeSelect" class="form-control select2-search" required>
                    <option value="">Type to search...</option>
                    <?php
                    $types = $conn->query("SELECT * FROM expense_category_types WHERE status = 'Active'");
                    while ($t = $types->fetch_assoc()) {
                        $sel = ($t['type_id'] == $row['type_id']) ? 'selected' : '';
                        echo "<option value='{$t['type_id']}' $sel>{$t['type_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold small">2. Category Name</label>
                <input type="text" name="category_name" class="form-control py-2"
                    value="<?php echo $row['category_name']; ?>"
                    placeholder="e.g. Diesel, Office Rent, Bills" required style="border-radius:12px;">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold small">Status</label>
                <select name="status" class="form-select py-2" style="border-radius:12px;">
                    <option value="Active" <?php echo $row['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo $row['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="col-12 mt-4 text-end">
                <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow" style="background:#6366f1; border:none;">
                    <i class="ri-save-3-line me-1"></i> Save Category
                </button>
            </div>
        </div>
    </form>
</div>