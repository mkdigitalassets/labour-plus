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

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold m-0 text-dark">Category Type Management</h3>
        <p class="text-muted small m-0 pt-2">Manage main expense categories</p>
    </div>
    <button class="btn btn-primary rounded-circle shadow-lg position-fixed"
        onclick="loadContent('components/category-type/add-category-type.php')"
        onmouseover="this.style.transform='scale(1.1) rotate(90deg)';"
        onmouseout="this.style.transform='scale(1) rotate(0deg)';"
        style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 9999; border: none; transition: 0.3s; background: #6366f1;">
        <i class="ri-add-line fs-3"></i>
    </button>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
    <div class="card-body p-3">
        <div class="input-group" style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; width:50%;">
            <span class="input-group-text border-0 bg-transparent text-muted"><i class="ri-search-line"></i></span>
            <input type="text" class="form-control border-0 bg-transparent py-2" id="typeSearch" placeholder="Search by name...">
        </div>
    </div>
</div>

<div class="table-modern border-0 shadow-sm bg-white" style="border-radius: 20px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="typeTable">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4"># ID</th>
                    <th>Category Type Name</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                /**
                 * SORTING LOGIC:
                 * Active types pehle aayenge (Priority 1), Inactive baad mein (Priority 2).
                 */
                $sql = "SELECT * FROM expense_category_types 
                        ORDER BY (CASE WHEN status = 'Active' THEN 1 ELSE 2 END) ASC, 
                        type_id DESC";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $isActive = ($row['status'] == 'Active');
                        $badge = $isActive ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';

                        // Opacity aur Background logic
                        $rowStyle = !$isActive ? 'style="opacity: 0.6; background-color: #fcfcfc;"' : '';
                ?>
                        <tr <?php echo $rowStyle; ?>>
                            <td class="ps-4 text-muted">#<?php echo str_pad($row['type_id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td class="<?php echo $isActive ? 'fw-semibold text-dark' : 'text-muted'; ?>">
                                <?php echo $row['type_name']; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $badge; ?> rounded-pill px-3">
                                    <i class="ri-checkbox-blank-circle-fill me-1" style="font-size: 8px;"></i>
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm text-primary p-1"
                                    onclick="loadContent('components/category-type/add-category-type.php?id=<?php echo $row['type_id']; ?>')">
                                    <i class="ri-edit-box-line ri-lg"></i>
                                </button>
                                <button class="btn btn-sm text-danger p-1 ms-1"
                                    onclick="deleteCategoryType(<?php echo $row['type_id']; ?>)">
                                    <i class="ri-delete-bin-line ri-lg"></i>
                                </button>
                            </td>
                        </tr>
                <?php }
                } else {
                    echo "<tr><td colspan='4' class='text-center py-4 text-muted'>No record found.</td></tr>";
                } ?>
            </tbody>
        </table>
    </div>
</div>