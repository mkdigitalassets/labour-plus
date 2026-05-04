<?php
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    // 1. DELETE LOGIC
    if ($_POST['action'] == 'delete_district') {
        $id = mysqli_real_escape_string($conn, $_POST['district_id']);

        $sql = "DELETE FROM districts WHERE district_id = '$id'";
        if ($conn->query($sql)) {
            echo "deleted";
        } else {
            echo "Error: " . $conn->error;
        }
        exit;
    }

    // 2. SAVE / UPDATE LOGIC
    if ($_POST['action'] == 'save_district') {
        // Data sanitization
        $name   = mysqli_real_escape_string($conn, $_POST['district_name']);
        $code   = mysqli_real_escape_string($conn, $_POST['region_code']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);

        $district_id = (isset($_POST['district_id']) && !empty($_POST['district_id'])) ? mysqli_real_escape_string($conn, $_POST['district_id']) : '';

        // --- DUPLICATION CHECK START ---
        // Hum check karenge ke is naam ki district pehle se hai ya nahi
        // UPDATE ke waqt hum ye bhi check karenge ke wo ID hamari apni na ho
        $check_query = "SELECT district_id FROM districts WHERE district_name = '$name'";
        if (!empty($district_id)) {
            $check_query .= " AND district_id != '$district_id'";
        }

        $check_res = $conn->query($check_query);
        if ($check_res && $check_res->num_rows > 0) {
            echo "exists"; // Agar mil gayi to yahin se exit
            exit;
        }
        // --- DUPLICATION CHECK END ---

        if (!empty($district_id)) {
            // UPDATE QUERY
            $sql = "UPDATE districts SET 
                    district_name = '$name', 
                    region_code = '$code', 
                    status = '$status' 
                    WHERE district_id = '$district_id'";
            $res_text = "updated";
        } else {
            // INSERT QUERY
            $sql = "INSERT INTO districts (district_name, region_code, status) 
                    VALUES ('$name', '$code', '$status')";
            $res_text = "success";
        }

        if ($conn->query($sql)) {
            echo $res_text;
        } else {
            echo "Error: " . $conn->error;
        }
        exit;
    }
}
