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
        <h3 class="fw-bold m-0 text-dark">Categories</h3>
        <p class="text-muted small m-0">Detailed expense classifications</p>
    </div>
    <button class="btn btn-primary rounded-circle shadow-lg position-fixed"
        onclick="loadContent('components/category/add-category.php')"
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
                    <th class="ps-4">#</th>
                    <th>Category Name</th>
                    <th>Main Type</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                /**
                 * ADVANCED SORTING LOGIC:
                 * 1. Pehle wo dikhao jinka Parent aur Child dono Active hain (Priority 1)
                 * 2. Phir wo dikhao jo Inactive hain (Priority 2)
                 * 3. Phir ID ke mutabiq sort karo
                 */
                $sql = "SELECT c.*, t.type_name, t.status AS parent_status 
                        FROM expense_categories c 
                        INNER JOIN expense_category_types t ON c.type_id = t.type_id 
                        ORDER BY 
                            (CASE 
                                WHEN t.status = 'Active' AND c.status = 'Active' THEN 1 
                                ELSE 2 
                            END) ASC, 
                            c.category_id DESC";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        $isParentInactive = ($row['parent_status'] == 'Inactive');
                        $isChildInactive = ($row['status'] == 'Inactive');

                        // Final Display Status
                        $finalStatus = ($isParentInactive) ? 'Inactive' : $row['status'];

                        // Styling Logic
                        $rowClass = ($isParentInactive || $isChildInactive) ? 'text-muted' : '';
                        $rowOpacity = ($isParentInactive || $isChildInactive) ? 'style="opacity: 0.6; background-color: #fcfcfc;"' : '';
                        $badgeClass = ($finalStatus == 'Active') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                ?>
                        <tr <?php echo $rowOpacity; ?> class="<?php echo $rowClass; ?>">
                            <td class="ps-4">#<?php echo $row['category_id']; ?></td>
                            <td>
                                <div class="fw-semibold <?php echo ($finalStatus == 'Active') ? 'text-dark' : ''; ?>">
                                    <?php echo $row['category_name']; ?>
                                </div>
                                <?php if ($isParentInactive): ?>
                                    <span class="badge bg-danger text-white" style="font-size: 9px; padding: 2px 5px;">Category Type Unavailable</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2">
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
                                <button class="btn btn-sm text-primary btn-light rounded-3 shadow-sm"
                                    onclick="loadContent('components/category/add-category.php?id=<?php echo $row['category_id']; ?>')">
                                    <i class="ri-edit-box-line"></i>
                                </button>
                                <button class="btn btn-sm text-danger btn-light rounded-3 shadow-sm ms-1"
                                    onclick="deleteCategory(<?php echo $row['category_id']; ?>)">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center py-5 text-muted'>No categories found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>