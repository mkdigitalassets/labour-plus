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

<style>
    /* Webkit Scrollbar Styling */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #6366f1;
        /* Primary color */
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #4f46e5;
    }

    /* Spacing and Layout Fixes */
    #employeeTable th,
    #employeeTable td {
        padding: 1.25rem 1.5rem !important;
        /* Rows ko khula karne ke liye padding */
        white-space: nowrap;
        /* Data ko ek dusre ke upar charhne se rokne ke liye */
    }

    .extra-small {
        font-size: 11px !important;
        letter-spacing: 0.3px;
    }

    .badge {
        padding: 6px 12px !important;
        font-weight: 600 !important;
    }
</style>

<div class="mb-4">
    <h4 class="fw-bold m-0 text-dark">Employees Management</h4>
    <p class="text-muted small m-0 pt-1">View and manage all registered employees.</p>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center gap-3">

            <!-- Role Filter -->
            <select id="stf_roleFilter" class="form-select border-0 shadow-sm rounded-pill" style="width: 150px; height: 45px;">
                <option value="">All Roles</option>
                <option value="Manager">Manager</option>
                <option value="Driver">Driver</option>
                <option value="Operator">Operator</option>
            </select>

            <!-- District Filter -->
            <select id="stf_districtFilter" class="form-select border-0 shadow-sm rounded-pill" style="width: 150px; height: 45px;"
                onchange="fetchTehsilsForStaffFilter(this.value)">
                <option value="">All Districts</option>
                <?php
                $dist_res = $conn->query("SELECT district_name FROM districts WHERE status = 'Active'");
                while ($d = $dist_res->fetch_assoc()) {
                    echo "<option value='" . $d['district_name'] . "'>" . $d['district_name'] . "</option>";
                }
                ?>
            </select>

            <!-- Tehsil Filter -->
            <select id="stf_tehsilFilter" class="form-select border-0 shadow-sm rounded-pill" style="width: 150px; height: 45px;">
                <option value="">All Tehsils</option>
            </select>

            <!-- Status Filter -->
            <select id="stf_statusFilter" class="form-select border-0 shadow-sm rounded-pill" style="width: 150px; height: 45px;">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>

            <!-- Search Bar -->
            <div class="position-relative">
                <i class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-5 ps-3 text-muted"></i>
                <input type="text" id="stf_employeeSearch" class="form-control border-0 shadow-sm ps-5 ms-5 rounded-pill"
                    placeholder="Search name, role, phone..." style="width: 300px; height: 45px; margin-left:10px;">
            </div>

        </div>
    </div>
</div>

<button class="btn btn-primary rounded-circle shadow-lg position-fixed"
    onclick="loadContent('components/staff/add-staff.php')"
    onmouseover="this.style.transform='scale(1.1) rotate(90deg)';"
    onmouseout="this.style.transform='scale(1) rotate(0deg)';"
    style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 9999; border: none; transition: 0.3s; background: #6366f1;">
    <i class="ri-add-line fs-3"></i>
</button>

<div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="employeeTable">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4 py-3 text-muted small fw-bold text-uppercase">Employee Details</th>
                    <th class="py-3 text-muted small fw-bold text-uppercase">Role</th>
                    <th class="py-3 text-muted small fw-bold text-uppercase">District</th>
                    <th class="py-3 text-muted small fw-bold text-uppercase">Tehsil</th>
                    <th class="py-3 text-muted small fw-bold text-uppercase">Contact & Cnic</th>
                    <th class="py-3 text-muted small fw-bold text-uppercase">Salary</th>
                    <th class="py-3 text-muted small fw-bold text-uppercase">Joining Date</th>
                    <th class="py-3 text-muted small fw-bold text-uppercase text-center">Status</th>
                    <th class="pe-4 py-3 text-muted small fw-bold text-uppercase text-end">Action</th>
                </tr>
            </thead>
            <tbody id="employeeTableBody">
                <?php
                // Query modified to fetch both District and Tehsil statuses
                $sql = "SELECT staff.*, tehsils.tehsil_name, tehsils.status AS t_status, 
                               districts.district_name, districts.status AS d_status 
                        FROM staff 
                        LEFT JOIN tehsils ON staff.tehsil_id = tehsils.tehsil_id 
                        LEFT JOIN districts ON tehsils.district_id = districts.district_id
                        ORDER BY 
                            (CASE 
                                WHEN staff.status = 'Active' 
                                AND (tehsils.status = 'Active' OR tehsils.status IS NULL) 
                                AND (districts.status = 'Active' OR districts.status IS NULL)
                                THEN 1 ELSE 0 END) DESC, 
                            staff.staff_id ASC";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        $sStatus = strtolower(trim($row['status'] ?? 'active'));
                        $tStatus = strtolower(trim($row['t_status'] ?? 'active'));
                        $dStatus = strtolower(trim($row['d_status'] ?? 'active'));

                        // Hierarchy Logic: District > Tehsil > Staff
                        $isEffectivelyInactive = false;
                        $statusLabel = "Active";
                        $statusBadge = "bg-success-subtle text-success";
                        $rowStyle = "";

                        if ($dStatus == 'inactive') {
                            $isEffectivelyInactive = true;
                            $statusLabel = "District Disabled";
                        } elseif ($tStatus == 'inactive') {
                            $isEffectivelyInactive = true;
                            $statusLabel = "Tehsil Disabled";
                        } elseif ($sStatus == 'inactive') {
                            $isEffectivelyInactive = true;
                            $statusLabel = "Inactive";
                        }

                        if ($isEffectivelyInactive) {
                            $rowStyle = 'style="opacity: 0.7; background-color: #f8fafc;"';
                            $statusBadge = "bg-danger-subtle text-danger";
                        }

                        $role = $row['staff_role'];
                        $role_class = 'bg-secondary';
                        if ($role == 'Manager') $role_class = 'bg-primary';
                        else if ($role == 'Driver') $role_class = 'bg-info';
                        else if ($role == 'Operator') $role_class = 'bg-warning text-dark';
                ?>
                        <tr <?php echo $rowStyle; ?>>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3 <?php echo ($isEffectivelyInactive) ? 'bg-secondary-subtle text-secondary' : 'bg-primary-subtle text-primary'; ?> rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px; font-size: 14px;">
                                        <?php echo strtoupper(substr($row['staff_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <span class="fw-bold text-dark d-block"><?php echo $row['staff_name']; ?></span>
                                        <span class="text-muted extra-small" style="font-size: 10px;">ID: <?php echo $row['staff_id']; ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?php echo ($isEffectivelyInactive) ? 'bg-light text-muted border' : $role_class; ?> rounded-pill px-3" style="font-size: 10px;"><?php echo $role; ?></span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark fw-bold small">
                                        <i class="ri-map-pin-2-line text-primary me-1"></i><?php echo $row['district_name'] ?? 'N/A'; ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark fw-bold small">
                                        <i class="ri-map-pin-2-line text-primary me-1"></i><?php echo $row['tehsil_name'] ?? 'N/A'; ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark small"><i class="ri-phone-line me-1 text-muted"></i><?php echo $row['staff_phone']; ?></span>
                                    <span class="text-muted extra-small" style="font-size: 10px;"><i class="ri-id-card-line me-1"></i><?php echo $row['staff_cnic'] ?: '---'; ?></span>
                                </div>
                            </td>
                            <td><span class="fw-bold text-dark">Rs. <?php echo number_format($row['fixed_salary']); ?></span></td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark small fw-medium"><?php echo date("d M, Y", strtotime($row['joining_date'])); ?></span>
                                    <span class="text-muted extra-small" style="font-size: 10px;">Registered</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge <?php echo $statusBadge; ?> rounded-pill px-3" style="font-size: 10px;">
                                    <?php echo $statusLabel; ?>
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-sm btn-light rounded-circle border shadow-sm" onclick="loadContent('components/staff/add-staff.php?id=<?php echo $row['staff_id']; ?>')">
                                    <i class="ri-pencil-line text-primary"></i>
                                </button>
                                <button class="btn btn-sm btn-light rounded-circle border shadow-sm ms-2" onclick="deleteStaff(<?php echo $row['staff_id']; ?>)">
                                    <i class="ri-delete-bin-line text-danger"></i>
                                </button>
                            </td>
                        </tr>
                <?php }
                } else {
                    echo "<tr><td colspan='8' class='text-center p-5 text-muted'>No employees found.</td></tr>";
                } ?>
            </tbody>
        </table>
    </div>
</div>