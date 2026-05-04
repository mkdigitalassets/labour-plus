<?php
if (!isset($conn)) {
    // Agar $conn pehle se nahi bana (yani index.php se load nahi ho raha), to include karein
    // Hum file_exists check kar ke path set karte hain
    if (file_exists('backend/config.php')) {
        include('backend/config.php'); // Jab index.php se load ho
    } else {
        include('../../backend/config.php'); // Jab dashboard.php direct chale
    }
} ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold m-0 text-dark">Tehsil Management</h3>
        <p class="text-muted small m-0 pt-2">Manage tehsils and assign them to specific districts</p>
    </div>
    <button class="btn btn-primary rounded-circle shadow-lg position-fixed"
        onclick="loadContent('components/tehsil/add-tehsil.php')"
        onmouseover="this.style.transform='scale(1.1) rotate(90deg)';"
        onmouseout="this.style.transform='scale(1) rotate(0deg)';"
        style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 9999; border: none; transition: 0.3s; background: #6366f1;">
        <i class="ri-add-line fs-3"></i>
    </button>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
    <div class="card-body p-3">
        <!-- row aur justify-content-end se search bar right side par chali jaye gi -->
        <div class="row justify-content-end">
            <!-- col-md-4 lagane se width 50% se kam ho kar approx 33% ho jaye gi -->
            <div class="col-md-4">
                <div class="position-relative ms-auto">
                    <i class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="tehsilSearch" class="form-control border-0 shadow-sm ps-5 rounded-pill"
                        placeholder="Search Tehsil name..." style="width: 300px; height: 45px;">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="table-modern border-0 shadow-sm bg-white" style="border-radius: 20px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tehsilTable">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4"># ID</th>
                    <th>Tehsil Name</th>
                    <th>Belongs to District</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Logic: Pehle check karein ke District aur Tehsil dono Active hain (1), baqi sab niche (0)
                // Phir mazeed sorting ke liye ID use karein
                $sql = "SELECT t.*, d.district_name, d.status AS district_status 
            FROM tehsils t 
            LEFT JOIN districts d ON t.district_id = d.district_id 
            ORDER BY 
                (CASE WHEN t.status = 'active' AND d.status = 'active' THEN 1 ELSE 0 END) DESC, 
                t.tehsil_id DESC";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        $parentStatus = strtolower($row['district_status'] ?? 'active');
                        $originalStatus = strtolower($row['status']);

                        // Final status determine karna
                        if ($parentStatus == 'inactive' || $originalStatus == 'inactive') {
                            $isActuallyActive = false;
                            $statusBadge = 'bg-danger-subtle text-danger';
                            $statusLabel = ($parentStatus == 'inactive') ? 'Inactive' : 'Inactive';
                            $rowOpacity = 'style="opacity: 0.7; background: #f8fafc;"';
                        } else {
                            $isActuallyActive = true;
                            $statusBadge = 'bg-success-subtle text-success';
                            $statusLabel = 'Active';
                            $rowOpacity = '';
                        }
                ?>
                        <tr <?php echo $rowOpacity; ?>>
                            <td class="ps-4 text-muted">#<?php echo str_pad($row['tehsil_id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td class="fw-semibold text-dark">
                                <?php echo $row['tehsil_name']; ?>
                                <?php if (!$isActuallyActive && $parentStatus == 'inactive'): ?>
                                    <small class="d-block text-danger" style="font-size: 10px; font-weight: 400;">
                                        <i class="ri-error-warning-line"></i> District Un-available
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-3 rounded-pill">
                                    <i class="ri-map-pin-2-line text-primary me-1"></i>
                                    <?php echo $row['district_name'] ?? 'Not Assigned'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $statusBadge; ?> rounded-pill px-3">
                                    <?php echo $statusLabel; ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm text-primary p-1"
                                    onclick="loadContent('components/tehsil/add-tehsil.php?id=<?php echo $row['tehsil_id']; ?>')">
                                    <i class="ri-edit-box-line ri-lg"></i>
                                </button>
                                <button class="btn btn-sm text-danger p-1 ms-1"
                                    onclick="deleteTehsil(<?php echo $row['tehsil_id']; ?>)">
                                    <i class="ri-delete-bin-line ri-lg"></i>
                                </button>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No tehsils found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>