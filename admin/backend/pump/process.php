<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Data Sanitization
    $p_name = mysqli_real_escape_string($conn, $_POST['pump_name']);
    $o_name = mysqli_real_escape_string($conn, $_POST['owner_name']);
    $o_phone = mysqli_real_escape_string($conn, $_POST['owner_phone']);
    $o_acc = mysqli_real_escape_string($conn, $_POST['owner_account']);
    $o_cnic = mysqli_real_escape_string($conn, $_POST['owner_cnic']);
    $t_id = (int)$_POST['tehsil_id'];
    $status = $_POST['status'] ?? 'active';

    $sql = "";

    if ($action == 'add') {
        $sql = "INSERT INTO pumps (pump_name, owner_name, owner_phone, owner_account, owner_cnic, tehsil_id, status) 
                VALUES ('$p_name', '$o_name', '$o_phone', '$o_acc', '$o_cnic', '$t_id', '$status')";
    } 
    elseif ($action == 'update') {
        $id = (int)$_POST['pump_id'];
        $sql = "UPDATE pumps SET pump_name='$p_name', owner_name='$o_name', owner_phone='$o_phone', 
                owner_account='$o_acc', owner_cnic='$o_cnic', tehsil_id='$t_id', status='$status' 
                WHERE pump_id=$id";
    }
    elseif ($action == 'delete') {
        $id = (int)$_POST['pump_id'];
        $sql = "DELETE FROM pumps WHERE pump_id=$id";
    }

    if (!empty($sql)) {
        if ($conn->query($sql)) {
            echo "success";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>