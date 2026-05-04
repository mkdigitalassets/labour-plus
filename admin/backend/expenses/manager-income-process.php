<?php
include('../config.php');

// 1. MANAGER FETCH KARNE KA LOGIC (Jab Tehsil select ho)
if (isset($_POST['action']) && $_POST['action'] == 'fetch_managers') {
    $tid = mysqli_real_escape_string($conn, $_POST['tehsil_id']);

    // Yahan check karein ke table name aur column names sahi hain
    $sql = "SELECT staff_id, staff_name FROM staff WHERE tehsil_id = '$tid' AND staff_role = 'Manager'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        echo '<option value="" selected disabled>Select Manager...</option>';
        while ($row = $result->fetch_assoc()) {
            // Yahan hum manager ka ID aur Name dropdown mein bhej rahe hain
            echo '<option value="' . $row['staff_id'] . '">' . $row['staff_name'] . '</option>';
        }
    } else {
        echo '<option value="">No Manager found for this Tehsil</option>';
    }
    exit; // Ye zaroori hai taake sirf dropdown ka data hi jaye
}

// 2. DATA SAVE KARNE KA LOGIC (Jab form submit ho)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['action'])) {

    // Basic Validation
    if (empty($_POST['tehsil_id']) || empty($_POST['manager_id']) || empty($_POST['amount'])) {
        echo "Bhai, Tehsil, Manager aur Amount lazmi select karein!";
        exit;
    }

    $tehsil_id        = mysqli_real_escape_string($conn, $_POST['tehsil_id']);
    $manager_id       = mysqli_real_escape_string($conn, $_POST['manager_id']);
    $amount           = mysqli_real_escape_string($conn, $_POST['amount']);
    $transaction_date = mysqli_real_escape_string($conn, $_POST['transaction_date']);
    $pay_mode         = mysqli_real_escape_string($conn, $_POST['pay_mode']);
    $remarks          = mysqli_real_escape_string($conn, $_POST['remarks']);

    $bank_name  = "NULL";
    $account_no = "NULL";

    if ($pay_mode == 'Bank' || $pay_mode == 'cheque') {
        if (!empty($_POST['bank_name'])) {
            $val1 = mysqli_real_escape_string($conn, $_POST['bank_name']);
            $bank_name = "'$val1'";
        }
        if (!empty($_POST['account_no'])) {
            $val2 = mysqli_real_escape_string($conn, $_POST['account_no']);
            $account_no = "'$val2'";
        }
    }

    $sql = "INSERT INTO manager_income (tehsil_id, manager_id, amount, transaction_date, pay_mode, bank_name, account_no, remarks) 
            VALUES ('$tehsil_id', '$manager_id', '$amount', '$transaction_date', '$pay_mode', $bank_name, $account_no, '$remarks')";

    if ($conn->query($sql)) {
        echo "success";
    } else {
        echo "Database Error: " . $conn->error;
    }
}

// --- Delete Income Entry ---
if (isset($_POST['action']) && $_POST['action'] == 'delete_income') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);

    $sql = "DELETE FROM manager_income WHERE manager_income_id = '$id'";

    if ($conn->query($sql)) {
        echo "success";
    } else {
        echo "Error: " . $conn->error;
    }
    exit;
}
