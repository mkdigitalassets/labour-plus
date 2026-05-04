<?php
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    // Fetch Tehsils
    if ($_POST['action'] == 'get_tehsils') {
        $district_id = mysqli_real_escape_string($conn, $_POST['district_id']);
        $sql = "SELECT * FROM tehsils WHERE district_id = '$district_id' AND status = 'active' ORDER BY tehsil_name ASC";
        $res = $conn->query($sql);
        echo '<option value="">Select Tehsil</option>';
        while($t = $res->fetch_assoc()) {
            echo "<option value='{$t['tehsil_id']}'>{$t['tehsil_name']}</option>";
        }
        exit;
    }

    // Fetch Companies
    if ($_POST['action'] == 'get_companies') {
        $tehsil_id = mysqli_real_escape_string($conn, $_POST['tehsil_id']);
        $sql = "SELECT * FROM companies WHERE tehsil_id = '$tehsil_id' AND status = 'Active' ORDER BY company_name ASC";
        $res = $conn->query($sql);
        echo '<option value="">Select Company</option>';
        while($c = $res->fetch_assoc()) {
            echo "<option value='{$c['company_id']}'>{$c['company_name']}</option>";
        }
        exit;
    }

    // Save/Update Vehicle
if ($_POST['action'] == 'save_vehicle') {
    // Sanitize all inputs
    $v_id          = mysqli_real_escape_string($conn, $_POST['vehicle_id']);
    $district_id   = mysqli_real_escape_string($conn, $_POST['district_id']);
    $tehsil_id     = mysqli_real_escape_string($conn, $_POST['tehsil_id']);
    $owner_id      = mysqli_real_escape_string($conn, $_POST['owner_id']);
    $company_id    = mysqli_real_escape_string($conn, $_POST['company_id']);
    $v_type_id     = mysqli_real_escape_string($conn, $_POST['v_type_id']);
    $reg_no        = mysqli_real_escape_string($conn, $_POST['reg_no']);
    $meter_type    = mysqli_real_escape_string($conn, $_POST['meter_type']);
    $rental_status = mysqli_real_escape_string($conn, $_POST['rental_status']);
    $company_rent  = !empty($_POST['company_rent']) ? $_POST['company_rent'] : 0;
    $lp_rent       = !empty($_POST['lp_rent']) ? $_POST['lp_rent'] : 0;
    $status        = mysqli_real_escape_string($conn, $_POST['status']);

    if (!empty($v_id)) {
        // UPDATE Logic
        $sql = "UPDATE vehicles SET 
                district_id='$district_id', 
                tehsil_id='$tehsil_id', 
                owner_id='$owner_id', 
                company_id='$company_id', 
                v_type_id='$v_type_id', 
                reg_no='$reg_no', 
                meter_type='$meter_type', 
                rental_status='$rental_status', 
                company_rent='$company_rent', 
                lp_rent='$lp_rent', 
                status='$status' 
                WHERE vehicle_id='$v_id'";
        $out = "updated";
    } else {
        // INSERT Logic
        $sql = "INSERT INTO vehicles (district_id, tehsil_id, owner_id, company_id, v_type_id, reg_no, meter_type, rental_status, company_rent, lp_rent, status) 
                VALUES ('$district_id', '$tehsil_id', '$owner_id', '$company_id', '$v_type_id', '$reg_no', '$meter_type', '$rental_status', '$company_rent', '$lp_rent', '$status')";
        $out = "success";
    }

    if ($conn->query($sql)) {
        echo $out;
    } else {
        // This will tell us exactly what is wrong if it fails again
        echo "Database Error: " . $conn->error;
    }
    exit;
}
}
?>