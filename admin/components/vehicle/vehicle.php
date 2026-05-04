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
    <h3 class="fw-bold text-dark">Vehicle Inventory</h3>

</div>

<button class="btn btn-primary rounded-circle shadow-lg position-fixed"
    onclick="loadContent('components/vehicle/add-vehicle.php')"
    onmouseover="this.style.transform='scale(1.1) rotate(90deg)';"
    onmouseout="this.style.transform='scale(1) rotate(0deg)';"
    style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 9999; border: none; transition: 0.3s; background: #6366f1;">
    <i class="ri-add-line fs-3"></i>
</button>

<div class="table-modern border-0 shadow-sm">
    <div class=" table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="">
                    <th>Reg No</th>
                    <th>Vehicle Info</th>
                    <th>Owner & Company</th>
                    <th>Location</th>
                    <th>Rent Details</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Aapki tables ke schema ke mutabik JOIN query
                $sql = "SELECT v.*, 
                               vt.description as type_name, 
                               vo.full_name as owner_name, 
                               c.company_name,
                               d.district_name,
                               t.tehsil_name
                        FROM vehicles v
                        LEFT JOIN vehicle_types vt ON v.v_type_id = vt.v_type_id
                        LEFT JOIN vehicle_owners vo ON v.owner_id = vo.owner_id
                        LEFT JOIN companies c ON v.company_id = c.company_id
                        LEFT JOIN districts d ON v.district_id = d.district_id
                        LEFT JOIN tehsils t ON v.tehsil_id = t.tehsil_id
                        ORDER BY v.vehicle_id DESC";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $status_badge = ($row['status'] == 'active') ? 'bg-success' : 'bg-danger';

                        // Rental Status colors
                        $rental_color = 'bg-light text-dark';
                        if ($row['rental_status'] == 'Rental') $rental_color = 'bg-info text-dark';
                        if ($row['rental_status'] == 'Exempted') $rental_color = 'bg-warning text-dark';
                ?>
                        <tr>
                            <td>
                                <span class="fw-bold text-dark"><?= $row['reg_no'] ?></span><br>
                                <span class="badge <?= $rental_color ?> x-small"><?= $row['rental_status'] ?></span>
                            </td>
                            <td>
                                <div class="small fw-bold"><?= $row['type_name'] ?></div>
                                <div class="text-muted x-small">Meter: <?= $row['meter_type'] ?></div>
                            </td>
                            <td>
                                <div class="small fw-bold text-primary"><?= $row['owner_name'] ?></div>
                                <div class="small text-muted italic"><?= $row['company_name'] ?></div>
                            </td>
                            <td>
                                <div class="small"><?= $row['district_name'] ?></div>
                                <div class="x-small text-muted"><?= $row['tehsil_name'] ?></div>
                            </td>
                            <td>
                                <div class="small">Comp: <span class="fw-bold"><?= number_format($row['company_rent'], 0) ?></span></div>
                                <div class="small text-success">LP: <span class="fw-bold"><?= number_format($row['lp_rent'], 0) ?></span></div>
                            </td>
                            <td><span class="badge <?= $status_badge ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <button onclick="loadContent('components/vehicle/add-vehicle.php?id=<?= $row['vehicle_id'] ?>')"
                                        class="btn btn-sm btn-light border shadow-sm rounded-circle"
                                        title="Edit Vehicle"
                                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-edit-box-line ri-lg text-primary"></i>
                                    </button>

                                    <button onclick="deleteVehicle(<?= $row['vehicle_id'] ?>)"
                                        class="btn btn-sm btn-light border shadow-sm rounded-circle"
                                        title="Delete Vehicle"
                                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-delete-bin-line ri-lg text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center py-4 text-muted'>Koi vehicle record nahi mila.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>