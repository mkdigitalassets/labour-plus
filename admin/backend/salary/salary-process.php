<?php
include('../config.php');

/**
 * 1. FETCH TEHSILS FOR FORM (Using District ID)
 * Jab aap Add ya Edit form mein District select karte hain.
 */
if (isset($_POST['action']) && $_POST['action'] == 'fetch_tehsils') {
    $district_id = mysqli_real_escape_string($conn, $_POST['district_id']);
    $sql = "SELECT tehsil_id, tehsil_name FROM tehsils WHERE district_id = '$district_id' AND status = 'Active'";
    $res = $conn->query($sql);
    echo '<option value="">-- Select Tehsil --</option>';
    while ($row = $res->fetch_assoc()) {
        // Form ke liye value mein ID bhejna zaroori hai
        echo "<option value='" . $row['tehsil_id'] . "'>" . $row['tehsil_name'] . "</option>";
    }
    exit;
}

/**
 * 2. FETCH TEHSILS FOR FILTER (Using District Name)
 * Ye sirf Salary List ke sidebar filter ke liye hai.
 */
if (isset($_POST['action']) && $_POST['action'] == 'fetch_tehsils_for_filter') {
    $district_name = mysqli_real_escape_string($conn, $_POST['district_name']);
    $sql = "SELECT t.tehsil_name 
            FROM tehsils t 
            INNER JOIN districts d ON t.district_id = d.district_id 
            WHERE d.district_name = '$district_name' AND t.status = 'Active'";
    $res = $conn->query($sql);
    echo '<option value="">All Tehsils</option>';
    while ($row = $res->fetch_assoc()) {
        // Filter ke liye value mein Name ja raha hai taake JS text search kar sakay
        echo "<option value='" . $row['tehsil_name'] . "'>" . $row['tehsil_name'] . "</option>";
    }
    exit;
}

/**
 * 3. FETCH STAFF (Using Tehsil ID & Role)
 */
if (isset($_POST['action']) && $_POST['action'] == 'fetch_staff_filtered') {
    $tehsil_id = mysqli_real_escape_string($conn, $_POST['tehsil_id']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $sql = "SELECT staff_id, staff_name FROM staff WHERE tehsil_id = '$tehsil_id' AND staff_role = '$role' AND status = 'Active'";
    $res = $conn->query($sql);
    echo '<option value="">-- Select Name --</option>';
    while ($row = $res->fetch_assoc()) {
        echo "<option value='" . $row['staff_id'] . "'>" . $row['staff_name'] . "</option>";
    }
    exit;
}

/**
 * 4. GET SALARY DETAILS (Fixed Salary & Previous Balance)
 */
if (isset($_POST['action']) && $_POST['action'] == 'get_salary_amount') {
    $staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);

    // Fixed Salary from Staff table
    $query = "SELECT fixed_salary FROM staff WHERE staff_id = '$staff_id'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);

    // Remaining Balance calculation
    $rem_query = "SELECT SUM(remaining_balance) as total_rem FROM salaries WHERE staff_id = '$staff_id'";
    $rem_res = mysqli_query($conn, $rem_query);
    $rem_data = mysqli_fetch_assoc($rem_res);
    $data['previous_remaining'] = $rem_data['total_rem'] ? $rem_data['total_rem'] : 0;

    echo json_encode($data);
    exit;
}

/**
 * 5. SAVE OR UPDATE SALARY
 */
if (isset($_POST['action']) && ($_POST['action'] == 'save_salary' || $_POST['action'] == 'update_salary')) {
    $action = $_POST['action'];
    $salary_id = !empty($_POST['salary_id']) ? mysqli_real_escape_string($conn, $_POST['salary_id']) : '';

    $staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);
    $role     = mysqli_real_escape_string($conn, $_POST['staff_role']);
    $month    = mysqli_real_escape_string($conn, $_POST['salary_month']);
    $fixed    = mysqli_real_escape_string($conn, $_POST['fixed_salary']);
    $paid     = mysqli_real_escape_string($conn, $_POST['paid_amount']);
    $method   = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $status   = mysqli_real_escape_string($conn, $_POST['payment_status']);

    $bonus     = !empty($_POST['bonus_amount']) ? mysqli_real_escape_string($conn, $_POST['bonus_amount']) : 0;
    $deduction = !empty($_POST['deduction_amount']) ? mysqli_real_escape_string($conn, $_POST['deduction_amount']) : 0;

    $net_salary = ($fixed + $bonus) - $deduction;
    $remaining  = $net_salary - $paid;

    if ($action == 'update_salary') {
        $sql = "UPDATE salaries SET 
                staff_id='$staff_id', staff_role='$role', salary_month='$month', fixed_salary='$fixed', 
                bonus_amount='$bonus', deduction_amount='$deduction', net_salary='$net_salary', 
                paid_amount='$paid', remaining_balance='$remaining', payment_method='$method', 
                payment_status='$status' 
                WHERE salary_id='$salary_id'";
    } else {
        $sql = "INSERT INTO salaries 
                (staff_id, staff_role, salary_month, fixed_salary, bonus_amount, deduction_amount, net_salary, paid_amount, remaining_balance, payment_method, payment_status) 
                VALUES 
                ('$staff_id', '$role', '$month', '$fixed', '$bonus', '$deduction', '$net_salary', '$paid', '$remaining', '$method', '$status')";
    }

    echo ($conn->query($sql)) ? "success" : $conn->error;
    exit;
}

/**
 * 6. DELETE SALARY
 */
if (isset($_POST['action']) && $_POST['action'] == 'delete_salary') {
    $id = mysqli_real_escape_string($conn, $_POST['salary_id']);
    $sql = "DELETE FROM salaries WHERE salary_id = '$id'";
    echo ($conn->query($sql)) ? "success" : $conn->error;
    exit;
}

/**
 * 7. CHECK DUPLICATE SALARY
 */
if (isset($_POST['action']) && $_POST['action'] == 'check_duplicate_salary') {
    $staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);
    $month = mysqli_real_escape_string($conn, $_POST['month']);
    $sql = "SELECT salary_id FROM salaries WHERE staff_id = '$staff_id' AND DATE_FORMAT(salary_month, '%Y-%m') = '$month'";
    $res = $conn->query($sql);
    echo ($res->num_rows > 0) ? "exists" : "not_exists";
    exit;
}
