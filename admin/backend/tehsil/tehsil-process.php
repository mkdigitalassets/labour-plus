<?php
// Path hamesha check karein ke config.php kahan hai
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    // Action 1: Delete
    if ($_POST['action'] == 'delete_tehsil') {
        $id = mysqli_real_escape_string($conn, $_POST['tehsil_id']);

        if ($conn->query("DELETE FROM tehsils WHERE tehsil_id = '$id'")) {
            echo "deleted";
        } else {
            echo "Error: " . $conn->error;
        }
        exit;
    }

    // Action 2: Save or Update
    if ($_POST['action'] == 'save_tehsil') {
        // Data fetch and sanitize
        $tehsil_name = mysqli_real_escape_string($conn, $_POST['tehsil_name']);
        $district_id = mysqli_real_escape_string($conn, $_POST['district_id']);
        $status      = mysqli_real_escape_string($conn, $_POST['status']);
        $tehsil_id   = (isset($_POST['tehsil_id']) && !empty($_POST['tehsil_id'])) ? mysqli_real_escape_string($conn, $_POST['tehsil_id']) : '';

        // Validation: Agar district_id nahi aai to error show karein
        if (empty($district_id)) {
            echo "Error: Please select a valid district.";
            exit;
        }

        // --- DUPLICATION CHECK START ---
        // Hum check karenge ke is SPECIFIC DISTRICT mein ye tehsil pehle se to nahi?
        $check_query = "SELECT tehsil_id FROM tehsils WHERE tehsil_name = '$tehsil_name' AND district_id = '$district_id'";

        // Agar UPDATE ho raha hai, to apni ID chorr kar baqi check karein
        if (!empty($tehsil_id)) {
            $check_query .= " AND tehsil_id != '$tehsil_id'";
        }

        $check_res = $conn->query($check_query);
        if ($check_res && $check_res->num_rows > 0) {
            echo "exists"; // Agar us district mein ye naam pehle se hai
            exit;
        }
        // --- DUPLICATION CHECK END ---

        if (!empty($tehsil_id)) {
            // Update Query
            $sql = "UPDATE tehsils SET 
                    tehsil_name = '$tehsil_name', 
                    district_id = '$district_id', 
                    status = '$status' 
                    WHERE tehsil_id = '$tehsil_id'";
            $res_text = "updated";
        } else {
            // Insert Query
            $sql = "INSERT INTO tehsils (tehsil_name, district_id, status) 
                    VALUES ('$tehsil_name', '$district_id', '$status')";
            $res_text = "success";
        }

        // Query execution
        if ($conn->query($sql)) {
            echo $res_text;
        } else {
            echo "Database Error: " . $conn->error;
        }
        exit;
    }
}
