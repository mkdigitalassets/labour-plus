<?php
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    // Action: Save/Update Vehicle Type
    if ($_POST['action'] == 'save_vtype') {
        // IDs aur names match karein form ke sath
        $v_id        = mysqli_real_escape_string($conn, $_POST['v_type_id'] ?? '');
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $fuel_type   = mysqli_real_escape_string($conn, $_POST['fuel_type']);
        $status      = mysqli_real_escape_string($conn, $_POST['status']);

        if (!empty($v_id)) {
            // Update Logic
            $sql = "UPDATE vehicle_types SET description='$description', fuel_type='$fuel_type', status='$status' WHERE v_type_id='$v_id'";
            $res = "updated";
        } else {
            // Insert Logic
            $sql = "INSERT INTO vehicle_types (description, fuel_type, status) VALUES ('$description', '$fuel_type', '$status')";
            $res = "success";
        }

        if ($conn->query($sql)) {
            echo $res;
        } else {
            echo "DB Error: " . $conn->error;
        }
        exit;
    }

    // Action: Delete
    if ($_POST['action'] == 'delete_vtype') {
        $id = mysqli_real_escape_string($conn, $_POST['v_type_id']);
        if ($conn->query("DELETE FROM vehicle_types WHERE v_type_id = '$id'")) { echo "deleted"; }
        exit;
    }
}
?>