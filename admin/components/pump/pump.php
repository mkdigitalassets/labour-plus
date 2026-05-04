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
    <div class="col">
        <h3 class="fw-bold m-0 text-dark">Pump Inventory</h3>
        <p class="text-muted small mb-0">List of all registered fuel stations</p>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary px-4 py-2 shadow-sm" onclick="loadContent('components/pump/add_pump.php')"
            style="border-radius: 12px;">
            <i class="ri-add-line"></i> Add Pump
        </button>
    </div>
</div>

<div class="table-responsive shadow-sm rounded-4 bg-white overflow-hidden">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th class="ps-4">Pump Details</th>
                <th>Owner Info</th>
                <th>Bank / CNIC</th>
                <th>Tehsil</th>
                <th class="text-end pe-4">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Join with tehsils using tehsil_id
            $query = "SELECT p.*, t.tehsil_name FROM pumps p 
                      INNER JOIN tehsils t ON p.tehsil_id = t.tehsil_id 
                      ORDER BY p.pump_id DESC";
            $res = $conn->query($query);
            while ($row = $res->fetch_assoc()):
            ?>
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold text-dark mb-0"><?php echo $row['pump_name']; ?></div>
                        <small class="text-muted">ID: #<?php echo $row['pump_id']; ?></small>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-2">
                                <i class="ri-user-line text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-semibold small"><?php echo $row['owner_name']; ?></div>
                                <div class="text-muted small"><?php echo $row['owner_phone']; ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="small text-secondary"><strong>CNIC:</strong> <?php echo $row['owner_cnic']; ?></div>
                        <div class="small text-secondary"><strong>ACC:</strong> <?php echo $row['owner_account']; ?></div>
                    </td>
                    <td><span class="badge bg-primary-subtle text-primary px-3 py-2"><?php echo $row['tehsil_name']; ?></span></td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-primary border-0"
                            onclick="loadContent('components/pump/add_pump.php?id=<?php echo $row['pump_id']; ?>&name=<?php echo urlencode($row['pump_name']); ?>&owner=<?php echo urlencode($row['owner_name']); ?>&phone=<?php echo $row['owner_phone']; ?>&acc=<?php echo $row['owner_account']; ?>&cnic=<?php echo $row['owner_cnic']; ?>&t_id=<?php echo $row['tehsil_id']; ?>')">
                            <i class="ri-edit-line fs-5"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger border-0" onclick="deletePump(<?php echo $row['pump_id']; ?>)">
                            <i class="ri-delete-bin-line fs-5"></i>
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>

</script>