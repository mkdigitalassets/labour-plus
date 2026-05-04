<?php
include('../config.php');

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- 1. GET TEHSILS FOR DROPDOWN ---
    if($action == 'fetch_tehsils') {
        $district_id = mysqli_real_escape_string($conn, $_POST['district_id'] ?? '');
        echo '<option value="">-- All Tehsils --</option>';
        
        $query = "SELECT * FROM tehsils WHERE status='active'";
        if(!empty($district_id)) {
            $query .= " AND district_id = '$district_id'";
        }
        $query .= " ORDER BY tehsil_name ASC";
        
        $res = $conn->query($query);
        if($res && $res->num_rows > 0) {
            while($row = $res->fetch_assoc()){
                echo "<option value='{$row['tehsil_id']}'>{$row['tehsil_name']}</option>";
            }
        }
        exit;
    }

    // --- 2. LOAD ATTENDANCE SHEET ---
    if ($action == 'load_attendance_sheet') {
        $date   = mysqli_real_escape_string($conn, $_POST['date']);
        $dist   = mysqli_real_escape_string($conn, $_POST['district_id'] ?? '');
        $teh    = mysqli_real_escape_string($conn, $_POST['tehsil_id'] ?? '');
        $veh_id = mysqli_real_escape_string($conn, $_POST['vehicle_id'] ?? ''); 
        $fuel   = mysqli_real_escape_string($conn, $_POST['fuel_type'] ?? '');
        $vtype  = mysqli_real_escape_string($conn, $_POST['v_type_id'] ?? '');
        $comp   = mysqli_real_escape_string($conn, $_POST['company_id'] ?? '');

        $sql = "SELECT v.vehicle_id, v.reg_no, v.fuel_type, vt.description as v_type_desc, 
                       d.district_name, t.tehsil_name, c.company_name 
                FROM vehicles v
                LEFT JOIN vehicle_types vt ON v.v_type_id = vt.v_type_id
                LEFT JOIN districts d ON v.district_id = d.district_id
                LEFT JOIN tehsils t ON v.tehsil_id = t.tehsil_id
                LEFT JOIN companies c ON v.company_id = c.company_id
                WHERE v.status = 'active'"; 

        if(!empty($dist))   $sql .= " AND v.district_id = '$dist'";
        if(!empty($teh))    $sql .= " AND v.tehsil_id = '$teh'";
        if(!empty($veh_id)) $sql .= " AND v.vehicle_id = '$veh_id'"; 
        if(!empty($fuel))   $sql .= " AND v.fuel_type = '$fuel'";
        if(!empty($vtype))  $sql .= " AND v.v_type_id = '$vtype'";
        if(!empty($comp))   $sql .= " AND v.company_id = '$comp'";

        $sql .= " ORDER BY v.reg_no ASC";

        $res = $conn->query($sql);
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $check = $conn->query("SELECT status FROM rental_attendance WHERE vehicle_id='{$row['vehicle_id']}' AND attendance_date='$date' LIMIT 1");
                $att = $check->fetch_assoc();
                $db_status = $att['status'] ?? 'Present'; 

                echo "<tr>
                    <td class='text-center'>
                        <input type='checkbox' value='{$row['vehicle_id']}' class='att-check form-check-input' checked>
                    </td>
                    <td class='text-start'>
                        <strong class='text-primary'>{$row['reg_no']}</strong><br>
                        <small class='text-muted'>".($row['v_type_desc'] ?? 'N/A')." | <span class='badge bg-light text-dark p-0' style='font-size:10px;'>{$row['fuel_type']}</span></small>
                    </td>
                    <td>
                        <span class='badge bg-light text-dark border'>".($row['company_name'] ?? 'No Company')."</span><br>
                        <small class='text-muted'>{$row['district_name']} > {$row['tehsil_name']}</small>
                    </td>
                    <td>
                        <select class='form-select form-select-sm row-status'>
                            <option value='Present' ".($db_status=='Present'?'selected':'').">Present</option>
                            <option value='Absent' ".($db_status=='Absent'?'selected':'').">Absent</option>
                            <option value='Maintenance' ".($db_status=='Maintenance'?'selected':'').">Maintenance</option>
                        </select>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='text-center py-4 text-danger text-uppercase fw-bold'>No Machinery Records Found</td></tr>";
        }
        exit;
    }

    // --- 3. SAVE BULK ATTENDANCE ---
    if ($action == 'save_bulk_attendance') {
        $att_date = mysqli_real_escape_string($conn, $_POST['att_date']);
        $data = $_POST['attendance_data'] ?? []; 

        foreach ($data as $item) {
            $vid = mysqli_real_escape_string($conn, $item['id']);
            $status = mysqli_real_escape_string($conn, $item['status']);

            $check = $conn->query("SELECT vehicle_id FROM rental_attendance WHERE vehicle_id='$vid' AND attendance_date='$att_date' LIMIT 1");
            
            if ($check && $check->num_rows > 0) {
                $conn->query("UPDATE rental_attendance SET status='$status' WHERE vehicle_id='$vid' AND attendance_date='$att_date'");
            } else {
                $conn->query("INSERT INTO rental_attendance (vehicle_id, attendance_date, status) VALUES ('$vid', '$att_date', '$status')");
            }
        }
        echo "success";
        exit;
    }

    // --- 4. GENERATE MONTHLY CALENDAR REPORT (Professional Matrix) ---
   if ($action == 'generate_monthly_calendar_report') {
    // Kisi bhi kism ki warning ko JSON kharab karne se rokne ke liye
    error_reporting(0);
    if (ob_get_length()) ob_clean(); 

    // JS se 'month' aa raha hai (Format: YYYY-MM)
    $selected_month = $_POST['month'] ?? date('Y-m');
    $dist_id = $_POST['district_id'] ?? '';
    $te_id = $_POST['tehsil_id'] ?? '';
    $fuel = $_POST['fuel_type'] ?? '';

    // Mahine ki start aur end dates calculate karein
    $start = date("Y-m-01", strtotime($selected_month));
    $end = date("Y-m-t", strtotime($selected_month));

    $begin = new DateTime($start);
    $last = new DateTime($end);
    $last->modify('+1 day');
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($begin, $interval, $last);

    // 1. Double Header (Days Numbers & Weekday Initials)
    $header = "<tr><th rowspan='2' class='sr-col'>S.No</th>";
    $header .= "<th rowspan='2' class='reg-col'>Reg.No</th>";
    $header .= "<th rowspan='2' class='type-col'>Machinery Type</th>";
    
    // Top Row: Date Numbers (01, 02...)
    foreach ($period as $dt) {
        $header .= "<th class='day-col' style='font-size:10px;'>".$dt->format('d')."</th>";
    }
    $header .= "<th rowspan='2' style='width:50px;'>Total</th></tr>";

    // Bottom Row: Weekday Initials (M, T, W...)
    $header .= "<tr>";
    foreach ($period as $dt) {
        $dayName = substr($dt->format('D'), 0, 1);
        // Sunday ko highlight karne ke liye logic
        $bg = ($dayName == 'S') ? "background:#ffecb3;" : "background:#f9f9f9;"; 
        $header .= "<th style='font-size:8px; $bg'>$dayName</th>";
    }
    $header .= "</tr>";

    // 2. Query with Full Filters
    $where = " WHERE v.status='active'";
    if(!empty($dist_id)) $where .= " AND v.district_id='$dist_id'";
    if(!empty($te_id)) $where .= " AND v.tehsil_id='$te_id'";
    if(!empty($fuel)) $where .= " AND v.fuel_type='$fuel'";

    $vehicles = $conn->query("SELECT v.vehicle_id, v.reg_no, vt.description as v_type_name 
                              FROM vehicles v 
                              LEFT JOIN vehicle_types vt ON v.v_type_id = vt.v_type_id 
                              $where ORDER BY v.reg_no ASC");

    $body = "";
    $sr = 1;

    if ($vehicles && $vehicles->num_rows > 0) {
        while ($v = $vehicles->fetch_assoc()) {
            $vid = $v['vehicle_id'];
            $totalPresent = 0;
            $body .= "<tr><td>".$sr++."</td>";
            $body .= "<td class='text-start fw-bold ps-2'>".$v['reg_no']."</td>";
            $body .= "<td class='text-start small ps-1'>".$v['v_type_name']."</td>";

            foreach ($period as $dt) {
                $curr = $dt->format('Y-m-d');
                $att = $conn->query("SELECT status FROM rental_attendance WHERE vehicle_id='$vid' AND attendance_date='$curr' LIMIT 1");
                
                $status = ($att && $att->num_rows > 0) ? $att->fetch_assoc()['status'] : '-';
                $char = '-';
                $bg = "";

                if($status == 'Present') { 
                    $char = 'P'; 
                    $totalPresent++; 
                }
                elseif($status == 'Maintenance') { 
                    $char = 'M'; 
                }
                elseif($status == 'Absent' || (strtotime($curr) <= time() && $status == '-')) { 
                    $char = 'A'; 
                    $bg = "background-color: #ffebee; color: #c62828;"; // Red highlight for Absent
                }

                $body .= "<td style='$bg font-weight:bold;'>$char</td>";
            }
            $body .= "<td class='fw-bold'>$totalPresent</td></tr>";
        }
    } else {
        $body = "<tr><td colspan='40' class='py-4'>No records found for the selected filters.</td></tr>";
    }

    // 3. Dynamic Heading Logic
    $location = "OVERALL MACHINERY REPORT";
    if (!empty($dist_id)) {
        $d_res = $conn->query("SELECT district_name FROM districts WHERE district_id='$dist_id'");
        if($d_res && $d_res->num_rows > 0){
            $d_row = $d_res->fetch_assoc();
            $location = "ATTENDANCE REPORT FOR RENTAL VEHICLES ( " . strtoupper($d_row['district_name']) . " )";
        }
    }

    // Final JSON Response
    echo json_encode(['header' => $header, 'body' => $body, 'location' => $location]);
    exit;
}
}
?>