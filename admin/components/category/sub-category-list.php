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
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold m-0 text-dark">Sub-Categories</h3>
        <p class="text-muted small m-0 pt-2">Manage third-level classifications</p>
    </div>
    <button class="btn btn-primary rounded-circle shadow-lg position-fixed"
        onclick="loadContent('components/category/add-sub-category.php')"
        onmouseover="this.style.transform='scale(1.1) rotate(90deg)';"
        onmouseout="this.style.transform='scale(1) rotate(0deg)';"
        style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 9999; border: none; transition: 0.3s; background: #6366f1;">
        <i class="ri-add-line fs-3"></i>
    </button>
</div>

<div class="table-modern border-0 shadow-sm bg-white" style="border-radius: 20px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">#ID</th>
                    <th>Sub-Category Name</th>
                    <th>Category</th>
                    <th>Category Type</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Triple Table JOIN logic with Status Checks
                // t = Main Type, c = Category, sc = Sub-Category
                $sql = "SELECT sc.*, c.category_name, c.status AS cat_status, 
                               t.type_name, t.status AS type_status 
                        FROM expense_sub_categories sc 
                        JOIN expense_categories c ON sc.category_id = c.category_id 
                        JOIN expense_category_types t ON c.type_id = t.type_id 
                        ORDER BY 
                            (CASE 
                                WHEN t.status = 'Active' AND c.status = 'Active' AND sc.status = 'Active' THEN 1 
                                ELSE 2 
                            END) ASC, 
                            sc.sub_id DESC";

                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        // --- MULTI-LEVEL STATUS LOGIC ---
                        $isTypeInactive = ($row['type_status'] == 'Inactive');
                        $isCatInactive  = ($row['cat_status'] == 'Inactive');
                        $isSubInactive  = ($row['status'] == 'Inactive');

                        // Agar Type ya Category band hai, to Sub-Category lazmi Inactive hogi
                        $finalStatus = ($isTypeInactive || $isCatInactive) ? 'Inactive' : $row['status'];

                        // Visual Styling
                        $isBlurred = ($isTypeInactive || $isCatInactive || $isSubInactive);
                        $rowStyle = $isBlurred ? 'style="opacity: 0.6; background-color: #fcfcfc;"' : '';
                        $badgeClass = ($finalStatus == 'Active') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                ?>
                        <tr <?php echo $rowStyle; ?>>
                            <td class="ps-4 text-muted">#<?php echo $row['sub_id']; ?></td>
                            <td>
                                <span class="fw-bold <?php echo !$isBlurred ? 'text-dark' : ''; ?>">
                                    <?php echo $row['sub_name']; ?>
                                </span>
                                <?php if ($isTypeInactive): ?>
                                    <br><span class="badge bg-danger text-white" style="font-size: 9px;">Category Type Unavailable</span>
                                <?php elseif ($isCatInactive): ?>
                                    <br><span class="badge bg-warning text-dark" style="font-size: 9px;">Category Unavailable</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary px-2 border border-secondary-subtle">
                                    <?php echo $row['category_name']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info px-2 border border-info-subtle">
                                    <?php echo $row['type_name']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3">
                                    <i class="ri-checkbox-blank-circle-fill me-1" style="font-size: 8px;"></i>
                                    <?php echo $finalStatus; ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light text-primary border shadow-sm"
                                    onclick="loadContent('components/category/add-sub-category.php?id=<?php echo $row['sub_id']; ?>')">
                                    <i class="ri-edit-box-line"></i>
                                </button>
                                <button class="btn btn-sm btn-light text-danger border shadow-sm ms-1"
                                    onclick="deleteSubCategory('<?php echo $row['sub_id']; ?>')">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-5 text-muted'>No sub-categories found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>