<?php
include('../config.php');

// 1. FETCH TEHSILS LOGIC (By ID - For Forms)
if (isset($_POST['action']) && $_POST['action'] == 'fetch_tehsils') {
    $dist_id = mysqli_real_escape_string($conn, $_POST['district_id']);
    $sql = "SELECT tehsil_id, tehsil_name FROM tehsils WHERE district_id = '$dist_id' AND status = 'Active' ORDER BY tehsil_name ASC";
    $result = $conn->query($sql);
    $options = '<option value="">Select Tehsil</option>';
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $options .= '<option value="' . $row['tehsil_id'] . '">' . $row['tehsil_name'] . '</option>';
        }
    } else {
        $options = '<option value="">No Tehsils Found</option>';
    }
    echo $options;
    exit;
}

// 2. FETCH TEHSILS BY NAME (For List Filters)
if (isset($_POST['action']) && $_POST['action'] == 'fetch_tehsils_by_name') {
    $dist_name = mysqli_real_escape_string($conn, $_POST['district_name']);
    $sql = "SELECT t.tehsil_name FROM tehsils t 
            JOIN districts d ON t.district_id = d.district_id 
            WHERE d.district_name = '$dist_name' AND t.status = 'Active' 
            ORDER BY t.tehsil_name ASC";
    $result = $conn->query($sql);
    $options = '';
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $options .= '<option value="' . $row['tehsil_name'] . '">' . $row['tehsil_name'] . '</option>';
        }
    }
    echo $options;
    exit;
}

// 3. DELETE LOGIC
if (isset($_POST['action']) && $_POST['action'] == 'delete_staff') {
    $id = mysqli_real_escape_string($conn, $_POST['staff_id']);
    $sql = "DELETE FROM staff WHERE staff_id = '$id'";
    if ($conn->query($sql)) {
        echo "deleted";
    } else {
        echo "Error: " . $conn->error;
    }
    exit;
}

// 4. INSERT / UPDATE LOGIC
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role      = isset($_POST['staff_role']) ? mysqli_real_escape_string($conn, $_POST['staff_role']) : '';
    $name      = isset($_POST['staff_name']) ? mysqli_real_escape_string($conn, $_POST['staff_name']) : '';
    $phone     = isset($_POST['staff_phone']) ? mysqli_real_escape_string($conn, $_POST['staff_phone']) : '';
    $cnic      = isset($_POST['staff_cnic']) ? mysqli_real_escape_string($conn, $_POST['staff_cnic']) : '';
    $salary    = isset($_POST['fixed_salary']) ? mysqli_real_escape_string($conn, $_POST['fixed_salary']) : '';
    $date      = isset($_POST['joining_date']) ? mysqli_real_escape_string($conn, $_POST['joining_date']) : '';
    $status    = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : 'Active';
    $staff_id  = isset($_POST['staff_id']) ? mysqli_real_escape_string($conn, $_POST['staff_id']) : '';

    // CNIC Duplicate Check
    $check_sql = "SELECT staff_id FROM staff WHERE staff_cnic = '$cnic'";
    if (!empty($staff_id)) {
        $check_sql .= " AND staff_id != '$staff_id'";
    }
    $check_res = $conn->query($check_sql);
    if ($check_res && $check_res->num_rows > 0) {
        echo "duplicate";
        exit;
    }

    $tehsil = "NULL";
    if (isset($_POST['tehsil_id']) && !empty($_POST['tehsil_id'])) {
        $val = mysqli_real_escape_string($conn, $_POST['tehsil_id']);
        $tehsil = "'$val'";
    }

    if (!empty($staff_id)) {
        $sql = "UPDATE staff SET staff_role='$role', staff_name='$name', staff_phone='$phone', staff_cnic='$cnic', fixed_salary='$salary', joining_date='$date', status='$status', tehsil_id=$tehsil WHERE staff_id='$staff_id'";
        $msg = "updated";
    } else {
        $sql = "INSERT INTO staff (staff_role, staff_name, staff_phone, staff_cnic, fixed_salary, joining_date, status, tehsil_id) VALUES ('$role', '$name', '$phone', '$cnic', '$salary', '$date', '$status', $tehsil)";
        $msg = "success";
    }

    if ($conn->query($sql)) {
        echo $msg;
    } else {
        echo "Error: " . $conn->error;
    }
}
