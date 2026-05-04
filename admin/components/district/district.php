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

// Data fetch karne ki query
$query = "SELECT * FROM districts ORDER BY district_id";
$result = mysqli_query($conn, $query);

?>



<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold m-0 text-dark">District Management</h3>
        <!-- <p class="text-muted small m-0">View and manage all active districts in the system</p> -->
    </div>
    <button class="btn btn-primary rounded-circle shadow-lg position-fixed"
        onclick="loadContent('components/district/add-district.php')"
        onmouseover="this.style.transform='scale(1.1) rotate(90deg)';"
        onmouseout="this.style.transform='scale(1) rotate(0deg)';"
        style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 9999; border: none; transition: 0.3s; background: #6366f1;">
        <i class="ri-add-line fs-3"></i>
    </button>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
    <div class="card-body p-3">
        <!-- justify-content-end se content right side pr chala jaye ga -->
        <div class="row align-items-center justify-content-end">
            <!-- col-md-4 lagane se width kam ho jaye gi (aap isay 3 ya 5 bhi kr skty hain) -->
            <div class="col-md-4">
               <div class="position-relative ms-auto">
                <i class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="districtSearch" class="form-control border-0 shadow-sm ps-5 rounded-pill"
                    placeholder="Search District name..." style="width: 280px; height: 45px;">
            </div>
            </div>
        </div>
    </div>
</div>

<div class="table-modern border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="districtTable">
            <thead>
                <tr>
                    <th class="ps-4"># ID</th>
                    <th>District Name</th>
                    <th>Region Code</th>
                    <th>Total Tehsils</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query mein sorting logic add ki hai: Active pehle, Inactive baad mein
                $sql = "SELECT d.*, COUNT(t.tehsil_id) AS total_tehsils 
            FROM districts d 
            LEFT JOIN tehsils t ON d.district_id = t.district_id 
            GROUP BY d.district_id 
            ORDER BY 
                (CASE WHEN d.status = 'active' THEN 1 ELSE 0 END) DESC, 
                d.district_id DESC";

                $districts = $conn->query($sql);

                while ($row = $districts->fetch_assoc()):
                    $currentStatus = strtolower($row['status']);

                    // Inactive ke liye styling aur badges set karein
                    if ($currentStatus == 'active') {
                        $statusBadge = 'bg-success-subtle text-success';
                        $rowStyle = '';
                        $statusText = 'Active';
                    } else {
                        $statusBadge = 'bg-danger-subtle text-danger';
                        // Inactive row ko thoda dim aur background change kar diya
                        $rowStyle = 'style="opacity: 0.75; background-color: #f8fafc;"';
                        $statusText = 'Inactive';
                    }
                ?>
                    <tr <?php echo $rowStyle; ?>>
                        <td class="ps-4 text-muted">#<?php echo str_pad($row['district_id'], 3, '0', STR_PAD_LEFT); ?></td>
                        <td class="fw-semibold">
                            <span class="text-dark"><?php echo $row['district_name']; ?></span>
                            <?php if ($currentStatus == 'inactive'): ?>
                                <small class="d-block text-muted" style="font-size: 10px;">Currently Unavailable</small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-light text-dark border px-2"><?php echo $row['region_code']; ?></span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="ri-stack-line me-2 text-muted"></i>
                                <?php echo $row['total_tehsils']; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?php echo $statusBadge; ?> rounded-pill px-3">
                                <?php echo $statusText; ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm text-primary p-1"
                                onclick="loadContent('components/district/add-district.php?id=<?php echo $row['district_id']; ?>')"
                                title="Edit District">
                                <i class="ri-edit-box-line ri-lg"></i>
                            </button>
                            <button class="btn btn-sm text-danger p-1 ms-1"
                                onclick="deleteDistrict(<?php echo $row['district_id']; ?>)"
                                title="Delete District">
                                <i class="ri-delete-bin-line ri-lg"></i>
                            </button>
                        </td>
                    </tr>

                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>