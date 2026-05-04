<?php
include('../config.php');

$action = $_POST['action'] ?? '';

if ($action == 'fetch_vehicles_fuel' || $action == 'fetch_fuel_mileage') {
    // Determine which page is calling
    $isMileagePage = ($action == 'fetch_fuel_mileage');
    
    $s_date = !empty($_POST['start_date']) ? $_POST['start_date'] : $_POST['date'];
    $e_date = !empty($_POST['end_date']) ? $_POST['end_date'] : $_POST['date'];
    
    $limit = ($_POST['limit'] == 'all') ? 999999 : (int)$_POST['limit'];
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $offset = ($page - 1) * $limit;

    $where = "WHERE v.status = 'active' ";
    if(!empty($_POST['district']))   $where .= " AND v.district_id = '{$_POST['district']}'";
    if(!empty($_POST['tehsil']))     $where .= " AND v.tehsil_id = '{$_POST['tehsil']}'";
    if(!empty($_POST['v_type']))     $where .= " AND v.v_type_id = '{$_POST['v_type']}'";
    if(!empty($_POST['vehicle_id'])) $where .= " AND v.vehicle_id = '{$_POST['vehicle_id']}'";

    $total_res = $conn->query("SELECT COUNT(*) as total FROM vehicles v $where");
    $total_records = $total_res->fetch_assoc()['total'];
    $total_pages = ceil($total_records / $limit);

    $sql = "SELECT v.vehicle_id, v.reg_no, v.meter_type, vt.description as type_name, 
                   d.district_name, t.tehsil_name, f.qty, f.meter_reading
            FROM vehicles v
            LEFT JOIN vehicle_types vt ON v.v_type_id = vt.v_type_id
            LEFT JOIN districts d ON v.district_id = d.district_id
            LEFT JOIN tehsils t ON v.tehsil_id = t.tehsil_id
            LEFT JOIN fuel_entries f ON v.vehicle_id = f.vehicle_id 
                 AND f.fuel_date BETWEEN '$s_date' AND '$e_date'
            $where ORDER BY v.reg_no ASC LIMIT $offset, $limit";

    $result = $conn->query($sql);
    $html = "";
    $i = $offset + 1;

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $qty = $row['qty'] ?? '0.00';
            $meter = $row['meter_reading'] ?? '0.00';
            $unit_label = !empty($row['meter_type']) ? $row['meter_type'] : 'KM';

            $html .= "<tr>
                        <td class='ps-3 text-muted'>{$i}</td>
                        <td>
                            <div class='fw-bold text-dark'>{$row['reg_no']}</div>
                            <div class='small text-muted'>{$row['type_name']}</div>
                            <input type='hidden' name='v_ids[]' value='{$row['vehicle_id']}'>
                        </td>
                        <td>
                            <div class='small fw-medium text-uppercase text-primary'>{$row['district_name']}</div>
                            <div class='x-small text-muted'>{$row['tehsil_name']}</div>
                        </td>";

            // Agar Mileage page hai to Meter column dikhao
            if ($isMileagePage) {
                $unit_color = ($row['meter_type'] == 'KM') ? 'bg-info' : 'bg-warning text-dark';
                $html .= "<td>
                            <input type='text' name='meter[]' value='{$meter}' class='form-control form-control-sm text-center meter-input bg-light border-0 mb-1' placeholder='0.00'>
                            <div class='text-center'><span class='badge $unit_color px-2 rounded-pill' style='font-size:10px;'>$unit_label</span></div>
                          </td>";
            } else {
                // Add Fuel page ke liye sirf previous reading label dikha sakte hain (optional)
                $html .= "<td class='text-center text-muted small'>Last: {$meter} {$unit_label}</td>";
            }

            $html .= "<td>
                        <input type='text' name='qty[]' value='{$qty}' class='form-control text-center fw-bold qty-input border-primary shadow-sm' style='background: #f0f7ff;'>
                      </td>
                    </tr>";
            $i++;
        }
    } else {
        $colspan = $isMileagePage ? 5 : 4;
        $html = "<tr><td colspan='$colspan' class='text-center p-5 text-muted'>No vehicles found.</td></tr>";
    }

    $pagination = '<nav><ul class="pagination pagination-sm m-0">';
    $func = $isMileagePage ? 'loadData' : 'loadVehiclesForFuel';
    for($p=1; $p<=$total_pages; $p++) {
        $active = ($p == $page) ? 'active' : '';
        $pagination .= "<li class='page-item $active'><a class='page-link' href='javascript:void(0)' onclick='{$func}($p)'>$p</a></li>";
    }
    $pagination .= '</ul></nav>';

    echo json_encode(['html' => $html, 'pagination' => $pagination]);
}

// Save Logic (Universal)
if ($action == 'save_bulk_fuel' || $action == 'save_fuel_mileage') {
    $date = $_POST['fuel_date'];
    $v_ids = $_POST['v_ids'] ?? [];
    $qtys = $_POST['qty'] ?? [];
    $meters = $_POST['meter'] ?? []; // Mileage page se aayega, Add fuel se empty hoga

    $conn->begin_transaction();
    try {
        foreach ($v_ids as $key => $v_id) {
            $qty = !empty($qtys[$key]) ? $qtys[$key] : 0;
            
            if ($action == 'save_fuel_mileage') {
                $meter = !empty($meters[$key]) ? $meters[$key] : 0;
                $stmt = $conn->prepare("INSERT INTO fuel_entries (vehicle_id, fuel_date, qty, meter_reading) VALUES (?, ?, ?, ?) 
                                      ON DUPLICATE KEY UPDATE qty = VALUES(qty), meter_reading = VALUES(meter_reading)");
                $stmt->bind_param("isdd", $v_id, $date, $qty, $meter);
            } else {
                // Add Fuel page: Don't overwrite meter_reading if it exists
                $stmt = $conn->prepare("INSERT INTO fuel_entries (vehicle_id, fuel_date, qty) VALUES (?, ?, ?) 
                                      ON DUPLICATE KEY UPDATE qty = VALUES(qty)");
                $stmt->bind_param("isd", $v_id, $date, $qty);
            }
            $stmt->execute();
        }
        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>