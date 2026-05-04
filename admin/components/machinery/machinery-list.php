<style>
    /* Soft Colors for Status */
    .bg-success-light {
        background-color: rgba(25, 135, 84, 0.1);
    }

    .bg-danger-light {
        background-color: rgba(220, 53, 69, 0.1);
    }

    /* Avatar Icon in Table */
    .avatar-sm {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-soft-primary {
        background-color: #eef2ff;
    }

    .avatar-title {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    /* Table Row Smoothness */
    .table-row-smooth {
        transition: 0.2s;
        border-bottom: 1px solid #f1f5f9;
    }

    .table-row-smooth:hover {
        background-color: #fafbfc !important;
    }

    /* Smaller Text */
    .smaller {
        font-size: 11px;
    }

    /* Remove default focus outline on pill filters */
    .form-select:focus,
    .form-control:focus {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08) !important;
        border-color: transparent !important;
    }
</style>

<div class="container-fluid py-3">
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

    <div class="row mb-4 align-items-center">
        <div class="col-lg-4">
            <h3 class="fw-bold text-dark mb-0">Machinery Inventory</h3>
            <p class="text-muted small mb-0">Manage and track all registered assets.</p>
        </div>

        <div class="col-lg-8">
            <div class="d-flex justify-content-lg-end align-items-center gap-2 flex-wrap">
                <select class="form-select border-0 shadow-sm px-3 filter-trigger" id="filterDistrict"
                    style="width: 160px; height: 48px; border-radius: 30px; font-size: 14px;">
                    <option value="">All Districts</option>
                    <?php
                    $dist_sql = "SELECT * FROM districts ORDER BY district_name ASC";
                    $dist_res = $conn->query($dist_sql);
                    while ($d = $dist_res->fetch_assoc()) {
                        echo "<option value='" . $d['district_id'] . "'>" . $d['district_name'] . "</option>";
                    }
                    ?>
                </select>

                <select class="form-select border-0 shadow-sm px-3 filter-trigger" id="filterStatus"
                    style="width: 140px; height: 48px; border-radius: 30px; font-size: 14px;">
                    <option value="">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>

                <div class="position-relative">
                    <input type="text" id="machinerySearch" class="form-control border-0 shadow-sm px-4"
                        style="width: 260px; height: 48px; border-radius: 30px; font-size: 14px;"
                        placeholder="Search reg no, type...">
                    <i class="ri-search-line position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                </div>

                <button class="btn btn-primary rounded-circle shadow-lg position-fixed"
                    onclick="loadContent('components/machinery/add-machinery.php')"
                    onmouseover="this.style.transform='scale(1.1) rotate(90deg)';"
                    onmouseout="this.style.transform='scale(1) rotate(0deg)';"
                    style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 9999; border: none; transition: 0.3s; background: #6366f1;">
                    <i class="ri-add-line fs-3"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="machineryTable">
                    <thead style="background-color: #f8f9fa; border-bottom: 2px solid #f1f5f9;">
                        <tr>
                            <th class="ps-4 py-3 text-muted small fw-bold text-uppercase">Machinery Info</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Type</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">District</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Tehsil</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase text-center">Status</th>
                            <th class="pe-4 py-3 text-muted small fw-bold text-uppercase text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="machineryListData">
                        <?php
                        include('../../backend/config.php');

                        $district = $_POST['district'] ?? '';
                        $status = $_POST['status'] ?? '';
                        $search = $_POST['search'] ?? '';

                        // Query building
                        $sql = "SELECT m.*, d.district_name, t.tehsil_name, tp.type_name 
        FROM machinery_registration m
        LEFT JOIN districts d ON m.district_id = d.district_id
        LEFT JOIN tehsils t ON m.tehsil_id = t.tehsil_id
        LEFT JOIN expense_category_types tp ON m.type_id = tp.type_id
        WHERE 1=1";

                        if (!empty($district)) {
                            $sql .= " AND m.district_id = '" . $conn->real_escape_string($district) . "'";
                        }

                        if (!empty($status)) {
                            $sql .= " AND m.status = '" . $conn->real_escape_string($status) . "'";
                        }

                        if (!empty($search)) {
                            $s = $conn->real_escape_string($search);
                            $sql .= " AND (m.registration_no LIKE '%$s%' OR tp.type_name LIKE '%$s%' OR d.district_name LIKE '%$s%')";
                        }

                        $sql .= " ORDER BY m.machine_id DESC";
                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $statusClass = ($row['status'] == 'Active') ? 'text-success bg-success-light' : 'text-danger bg-danger-light';
                        ?>
                                <tr class="table-row-smooth">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <span class="avatar-title bg-soft-primary text-primary fw-bold">
                                                    <?php echo substr($row['registration_no'], 0, 1); ?>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark text-uppercase"><?php echo $row['registration_no']; ?></h6>
                                                <small class="text-muted" style="font-size: 10px;">ID: #<?php echo $row['machine_id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1 fw-normal" style="border-radius: 4px;">
                                            <?php echo $row['type_name']; ?>
                                        </span>
                                    </td>
                                    <td><span class="text-dark fw-bold small text-uppercase"><?php echo $row['district_name']; ?></span></td>
                                    <td><span class="text-dark fw-bold small text-uppercase"><?php echo $row['tehsil_name']; ?></span></td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $statusClass; ?> px-3 py-2" style="font-size: 10px; border-radius: 50px;">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-light text-primary border-0 me-1" onclick='editMachine(<?php echo json_encode($row); ?>)' style="border-radius: 8px;">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light text-danger border-0" onclick="deleteMachine(<?php echo $row['machine_id']; ?>)" style="border-radius: 8px;">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-5 text-muted'>No machinery found matching your criteria.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>