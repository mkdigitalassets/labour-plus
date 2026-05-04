<?php
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    // --- 1. FORM DATA FETCH: Get Tehsils by District ID (For Add/Edit Form) ---
    if ($_POST['action'] == 'get_tehsils') {
        $district_id = mysqli_real_escape_string($conn, $_POST['district_id']);
        
        $sql = "SELECT tehsil_id, tehsil_name FROM tehsils 
                WHERE district_id = '$district_id' AND status = 'Active' 
                ORDER BY tehsil_name ASC";
        
        $result = $conn->query($sql);
        $options = '<option value="">Select Tehsil</option>';
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Form ke liye value 'tehsil_id' (Number) honi chahiye
                $options .= '<option value="' . $row['tehsil_id'] . '">' . $row['tehsil_name'] . '</option>';
            }
        } else {
            $options = '<option value="">No Tehsil Found</option>';
        }
        echo $options;
        exit;
    }

    // --- 2. TABLE FILTER: Fetch Tehsils by District Name (For List Filters) ---
    if ($_POST['action'] == 'fetch_tehsils_by_name') {
        $dist_name = mysqli_real_escape_string($conn, $_POST['district_name']);
        
        $sql = "SELECT t.tehsil_name FROM tehsils t 
                JOIN districts d ON t.district_id = d.district_id 
                WHERE d.district_name = '$dist_name' AND t.status = 'Active' 
                ORDER BY t.tehsil_name ASC";
        
        $result = $conn->query($sql);
        $options = '';
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Filter ke liye value 'tehsil_name' (Text) honi chahiye
                $options .= '<option value="' . $row['tehsil_name'] . '">' . $row['tehsil_name'] . '</option>';
            }
        }
        echo $options;
        exit;
    }

    // --- 3. SAVE / UPDATE COMPANY ---
    if ($_POST['action'] == 'save_company') {
        $c_id         = mysqli_real_escape_string($conn, $_POST['company_id']);
        $district_id  = mysqli_real_escape_string($conn, $_POST['district_id']);
        $tehsil_id    = mysqli_real_escape_string($conn, $_POST['tehsil_id']);
        $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
        $status       = mysqli_real_escape_string($conn, $_POST['status']);

        $check = $conn->query("SELECT * FROM companies WHERE company_name='$company_name' AND tehsil_id='$tehsil_id' AND company_id != '$c_id'");

        if ($check->num_rows > 0) {
            echo "exists";
        } else {
            if (!empty($c_id)) {
                $sql = "UPDATE companies SET district_id='$district_id', tehsil_id='$tehsil_id', company_name='$company_name', status='$status' WHERE company_id='$c_id'";
                $res = "updated";
            } else {
                $sql = "INSERT INTO companies (district_id, tehsil_id, company_name, status) VALUES ('$district_id', '$tehsil_id', '$company_name', '$status')";
                $res = "success";
            }

            if ($conn->query($sql)) {
                echo $res;
            } else {
                echo "Database Error: " . $conn->error;
            }
        }
        exit;
    }

    // --- 4. DELETE COMPANY ---
    if ($_POST['action'] == 'delete_company') {
        $c_id = mysqli_real_escape_string($conn, $_POST['company_id']);
        if (!empty($c_id)) {
            $sql = "DELETE FROM companies WHERE company_id = '$c_id'";
            if ($conn->query($sql)) {
                echo "deleted";
            } else {
                echo "Database Error: " . $conn->error;
            }
        } else {
            echo "Error: Invalid ID";
        }
        exit;
    }
}
?>