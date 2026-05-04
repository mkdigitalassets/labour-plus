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
// Filter values (agar aap search functionality implement karna chahen)
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold m-0 text-dark">Master Pump Directory</h3>
            <p class="text-muted small m-0 pt-3">Comprehensive list of all registered pumps with ownership and location
                details.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success rounded-pill px-3 shadow-sm" onclick="exportToExcel()">
                <i class="ri-file-excel-2-line me-1"></i> Export CSV
            </button>
            <button class="btn btn-success rounded-circle shadow-lg position-fixed"
                onclick="loadContent('components/pump/add-pump.php')"
                style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 1050; border: 4px solid #fff;">
                <i class="ri-user-add-fill fs-3"></i>
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-lg-5">
                    <div class="input-group"
                        style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <span class="input-group-text border-0 bg-transparent text-muted"><i
                                class="ri-search-eye-line"></i></span>
                        <input type="text" class="form-control border-0 bg-transparent py-2" id="masterSearch"
                            placeholder="Search by Pump, Owner, ID, or Tehsil...">
                    </div>
                </div>
                <div class="col-lg-7 d-flex gap-2">
                    <select class="form-select border-0 bg-light" style="border-radius: 10px;">
                        <option selected>All Districts</option>
                        <option>Vehari</option>
                        <option>Multan</option>
                    </select>
                    <select class="form-select border-0 bg-light" style="border-radius: 10px;">
                        <option selected>All Owners</option>
                    </select>
                    <select class="form-select border-0 bg-light" style="border-radius: 10px;">
                        <option selected>Status: All</option>
                        <option>Active</option>
                        <option>Low Stock</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 24px; overflow: hidden;">
    <div class="master-table-container card border-0 shadow-sm" style="border-radius: 24px;">
        <table class="table align-middle mb-0" id="masterPumpTable">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="ps-4 text-nowrap" style="min-width: 200px;">ID & Pump Name</th>
                    <th class="text-nowrap text-center" style="min-width: 150px;">Ownership</th>
                    <th class="text-nowrap text-center" style="min-width: 180px;">Tehsil</th>
                    <th class="text-nowrap text-center" style="min-width: 130px;">Contact</th>
                    <th class="text-nowrap text-center" style="min-width: 300px;">Capacity (Ltrs)</th>
                    <th class="text-nowrap text-center" style="min-width: 330px;">Pump Location</th>
                    <th class="text-nowrap text-center" style="min-width: 200px;">Status</th>
                    <th class="text-end pe-4 text-nowrap" style="min-width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody id="pumpTableBody">
                <?php
                // Query update ki hai owners table ke sath join laga kar
                $sql = "SELECT 
    p.pump_id, 
    p.pump_name, 
    p.contact_no, 
    p.address, 
    p.petrol_capacity, 
    p.diesel_capacity, 
    p.status, 
    p.owner_id,  /* Pump table se ID */
    o.owner_name, /* Owner table se Name */
    t.tehsil_name, 
    d.district_name
FROM pumps p
LEFT JOIN districts d ON p.district_id = d.district_id
LEFT JOIN tehsils t ON p.tehsil_id = t.tehsil_id
LEFT JOIN owners o ON p.owner_id = o.owner_id
ORDER BY p.pump_id ASC";


                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $status = strtolower($row['status'] ?? 'active');
                        $badgeClass = ($status == 'active') ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning';
                ?>
                        <tr>
                            <td>
                                <div class="d-flex flex-column ">
                                    <small class="text-dark ps-3">#<?php echo $row['pump_id']; ?><span class="fw-bold text-dark">-<?php echo $row['pump_name']; ?></span></small>
                                </div>
                            </td>
                            <td class="text-nowrap text-center">
                                <?php echo !empty($row['owner_name']) ? $row['owner_name'] : '<span class="text-danger">No Owner</span>'; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-column ms-5">
                                    <span class="badge bg-light text-primary text-center border" style="font-size: 0.7rem; width:fit-content;">
                                        <?php echo $row['tehsil_name']  ?>
                                    </span>
                                </div>
                            </td>
                            <td>

                                <div class="d-flex flex-column text-center">
                                    <span class="small text-muted"><?php echo $row['contact_no']; ?></span>
                                </div>

                            </td>
                            <td class="text-nowrap text-center">Petrol: <?php echo $row['petrol_capacity']; ?> / Diesel: <?php echo $row['diesel_capacity']; ?></td>
                            <td class="text-nowrap text-center"> <?php echo $row['address']; ?></td>
                            <td>
                                <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 ms-5">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm text-primary p-2" title="Edit"
                                        onclick="loadContent('components/pump/add-pump.php?id=<?php echo $row['pump_id']; ?>')">
                                        <i class="ri-edit-box-line fs-5"></i>
                                    </button>
                                    <button class="btn btn-sm text-danger p-2" title="Delete"
                                        onclick="deletePump(<?php echo $row['pump_id']; ?>)">
                                        <i class="ri-delete-bin-line fs-5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center py-5 text-muted'>No pumps found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>