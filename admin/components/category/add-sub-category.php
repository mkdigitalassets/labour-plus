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
$row = [
    'sub_name' => '',
    'category_id' => '',
    'status' => 'Active'
];

$current_type_id = '';

if ($id) {
    // Edit mode: Existing data fetch
    $res = $conn->query("
        SELECT sc.*, c.type_id 
        FROM expense_sub_categories sc
        JOIN expense_categories c 
        ON sc.category_id = c.category_id
        WHERE sc.sub_id = '$id'
    ");

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $current_type_id = $row['type_id'];
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-4">
            <?php echo $id ? 'Edit' : 'Add New'; ?> Sub-Category
        </h4>
    </div>
    <button class="btn btn-light border rounded-pill px-4 shadow-sm"
        onclick="loadContent('components/category/sub-category-list.php')">
        <i class="ri-arrow-left-line me-1"></i> Back
    </button>
</div>

<div class="card border-0 shadow-sm p-4" style="border-radius: 24px;">

    <form id="finalSubCategoryForm" data-cat-id="<?php echo $row['category_id']; ?>">

        <input type="hidden" name="sub_id" value="<?php echo $id; ?>">
        <input type="hidden" name="action" value="save_sub_category">

        <div class="row g-4">

            <!-- Category Type -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    1. Category Type
                </label>

                <select
                    id="typeSelect"
                    name="type_id"
                    class="form-select select2-inline"
                    required
                    onchange="fetchCategoriesInline(this.value)">
                    <option value="">Select Type...</option>

                    <?php
                    $types = $conn->query("
                        SELECT * 
                        FROM expense_category_types
                        WHERE status = 'Active'
                    ");

                    while ($t = $types->fetch_assoc()) {
                        $selected = ($t['type_id'] == $current_type_id) ? 'selected' : '';
                        echo "<option value='{$t['type_id']}' $selected>
                                {$t['type_name']}
                              </option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Category -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    2. Select Category
                </label>

                <select
                    name="category_id"
                    id="categorySelect"
                    class="form-select select2-inline"
                    required>
                    <option value="">Select type first...</option>
                </select>
            </div>

            <!-- Sub Category -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    3. Sub-Category Name
                </label>

                <input
                    type="text"
                    name="sub_name"
                    class="form-control"
                    value="<?php echo $row['sub_name']; ?>"
                    placeholder="e.g. Parts, Fuel etc"
                    required>
            </div>

            <!-- Submit -->
            <div class="col-12 text-end mt-4">
                <button
                    type="submit"
                    id="submitBtn"
                    class="btn btn-primary px-5 rounded-pill shadow"
                    style="background:#6366f1; border:none;">
                    <i class="ri-save-3-line me-1"></i>
                    Save Sub-Category
                </button>
            </div>

        </div>
    </form>
</div>