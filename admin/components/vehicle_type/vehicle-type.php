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

<div class="mb-4">
    <h3 class="fw-bold m-0 text-dark">Vehicle Types</h3>
    <p class="text-muted small m-0 pt-2">Manage vehicle categories and configurations</p>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-end justify-content-end gap-4">
            <!-- Fuel Type Filter -->
            <select id="vt_fuelFilter" class="form-select border-0 shadow-sm rounded-pill" style="width: 180px; height: 45px;">
                <option value="">All Fuel Types</option>
                <option value="Diesel">Diesel</option>
                <option value="Petrol">Petrol</option>
            </select>

            <!-- Status Filter -->
            <select id="vt_statusFilter" class="form-select border-0 shadow-sm rounded-pill" style="width: 150px; height: 45px;">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>

            <!-- Search Bar -->
            <div class="position-relative">
                <i class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="z-index: 10;"></i>
                <input type="text" id="vt_search" class="form-control border-0 shadow-sm ps-5 rounded-pill"
                    placeholder="Search vehicle type..." style="width: 320px; height: 45px;">
            </div>
        </div>
    </div>
</div>

<button class="btn btn-primary rounded-circle shadow-lg position-fixed"
    onclick="loadContent('components/vehicle_type/add-vehicle-type.php')"
    onmouseover="this.style.transform='scale(1.1) rotate(90deg)';"
    onmouseout="this.style.transform='scale(1) rotate(0deg)';"
    style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 9999; border: none; transition: 0.3s; background: #6366f1;">
    <i class="ri-add-line fs-3"></i>
</button>

<div class="table-modern border-0 shadow-sm bg-white" style="border-radius: 20px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4"># ID</th>
                    <th>Vehicle Name</th>
                    <th>Fuel Type</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody id="vehicleTypeTable">
                <?php
                $result = $conn->query("SELECT * FROM vehicle_types ORDER BY v_type_id DESC");
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $statusClass = ($row['status'] == 'active') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                ?>
                        <tr>
                            <td class="ps-4 text-muted">#<?php echo str_pad($row['v_type_id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td class="fw-semibold"><?php echo $row['description']; ?></td>
                            <td><span class="badge bg-light text-dark border rounded-pill px-3"><?php echo $row['fuel_type']; ?></span></td>
                            <td><span class="badge <?php echo $statusClass; ?> rounded-pill px-3 text-capitalize"><?php echo $row['status']; ?></span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm text-primary" onclick="loadContent('components/vehicle_type/add-vehicle-type.php?id=<?php echo $row['v_type_id']; ?>')">
                                    <i class="ri-edit-box-line ri-lg"></i>
                                </button>
                                <button class="btn btn-sm text-danger" onclick="deleteVType(<?php echo $row['v_type_id']; ?>)">
                                    <i class="ri-delete-bin-line ri-lg"></i>
                                </button>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center py-4'>No records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>