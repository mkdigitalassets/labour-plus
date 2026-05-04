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

<!-- 1. Heading Section -->
<div class="mb-4">
    <h4 class="fw-bold m-0 text-dark">Company Management</h4>
    <p class="text-muted small m-0 pt-1">View and manage all registered companies across districts.</p>
</div>

<!-- 2. Filters Card Section -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center gap-3">

            <!-- District Filter -->
            <select id="comp_filterDistrict" class="form-select border-0 shadow-sm rounded-pill"
                style="width: 180px; height: 45px;"
                onchange="fetchTehsilsForCompanyFilter(this.value)">
                <option value="">All Districts</option>
                <?php
                $dist_res = $conn->query("SELECT * FROM districts WHERE status = 'Active'");
                while ($d = $dist_res->fetch_assoc()) {
                    // Yahan hum name filter ke liye value mein name bhej rahe hain
                    echo "<option value='{$d['district_name']}'>{$d['district_name']}</option>";
                }
                ?>
            </select>

            <!-- Tehsil Filter -->
            <select id="comp_filterTehsil" class="form-select border-0 shadow-sm rounded-pill"
                style="width: 180px; height: 45px;">
                <option value="">All Tehsils</option>
            </select>

            <!-- Status Filter -->
            <select id="comp_filterStatus" class="form-select border-0 shadow-sm rounded-pill"
                style="width: 150px; height: 45px;">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>

            <!-- Search Bar -->
            <div class="position-relative ms-auto">
                <i class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="comp_companySearch" class="form-control border-0 shadow-sm ps-5 rounded-pill"
                    placeholder="Search company name..." style="width: 280px; height: 45px;">
            </div>

        </div>
    </div>
</div>

<!-- 3. Floating Add Button -->
<button class="btn btn-primary rounded-circle shadow-lg position-fixed"
    onclick="loadContent('components/company/add-company.php')"
    onmouseover="this.style.transform='scale(1.1) rotate(90deg)';"
    onmouseout="this.style.transform='scale(1) rotate(0deg)';"
    style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 9999; border: none; transition: 0.3s; background: #6366f1;">
    <i class="ri-add-line fs-3"></i>
</button>

<div class="table-modern border-0 shadow-sm bg-white" style="border-radius: 20px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="companyTable">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4"># ID</th>
                    <th>Company Name</th>
                    <th>Districts</th>
                    <th>Tehsils</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody id="companyTableBody">
                <?php
                // Intelligent Query: District aur Tehsil dono ka status check kar rahe hain
                $sql = "SELECT c.*, d.district_name, d.status AS dist_status, t.tehsil_name, t.status AS teh_status 
                        FROM companies c
                        LEFT JOIN districts d ON c.district_id = d.district_id
                        LEFT JOIN tehsils t ON c.tehsil_id = t.tehsil_id
                        ORDER BY 
                            (CASE WHEN c.status = 'Active' AND d.status = 'Active' AND t.status = 'Active' THEN 1 ELSE 0 END) DESC, 
                            c.company_id DESC";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        $dStatus = strtolower($row['dist_status'] ?? 'active');
                        $tStatus = strtolower($row['teh_status'] ?? 'active');
                        $cStatus = strtolower($row['status']);

                        // Dependency Logic: Agar parent (District/Tehsil) inactive hai to ye automatically inactive hai
                        $isBlocked = ($dStatus == 'inactive' || $tStatus == 'inactive');

                        if ($isBlocked || $cStatus == 'inactive') {
                            $statusBadge = 'bg-danger-subtle text-danger';
                            $statusLabel = 'Inactive';
                            $rowStyle = 'style="opacity: 0.7; background: #f8fafc;"';
                        } else {
                            $statusBadge = 'bg-success-subtle text-success';
                            $statusLabel = 'Active';
                            $rowStyle = '';
                        }
                ?>
                        <tr <?php echo $rowStyle; ?>>
                            <td class="ps-4 text-muted">#<?php echo str_pad($row['company_id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td class="fw-semibold text-dark">
                                <?php echo $row['company_name']; ?>
                                <?php if ($isBlocked): ?>
                                    <small class="d-block text-danger" style="font-size: 10px;">
                                        <i class="ri-error-warning-line"></i>
                                        <?php echo ($dStatus == 'inactive') ? 'District Disabled' : 'Tehsil Disabled'; ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="small text-dark fw-medium"><i class="ri-map-pin-line text-primary me-1"></i><?php echo $row['district_name']; ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="small text-dark fw-medium"></i><?php echo $row['tehsil_name']; ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?php echo $statusBadge; ?> rounded-pill px-3">
                                    <?php echo $statusLabel; ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm text-primary p-1"
                                    onclick="loadContent('components/company/add-company.php?id=<?php echo $row['company_id']; ?>')">
                                    <i class="ri-edit-box-line ri-lg"></i>
                                </button>
                                <button class="btn btn-sm text-danger p-1 ms-1"
                                    onclick="deleteCompany(<?php echo $row['company_id']; ?>)">
                                    <i class="ri-delete-bin-line ri-lg"></i>
                                </button>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No companies found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>