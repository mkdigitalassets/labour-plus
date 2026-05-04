<?php
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    // Action: Get Tehsils
    if ($_POST['action'] == 'get_tehsils') {
        $d_id = mysqli_real_escape_string($conn, $_POST['district_id']);
        $res = $conn->query("SELECT tehsil_id, tehsil_name FROM tehsils WHERE district_id = '$d_id' AND status = 'active'");
        
        echo '<option value="" disabled selected>Choose Tehsil...</option>';
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                echo "<option value='{$row['tehsil_id']}'>{$row['tehsil_name']}</option>";
            }
        } else {
            echo '<option value="">No Active Tehsil Found</option>';
        }
        exit;
    }

    // Action: Save/Update Owner
    if ($_POST['action'] == 'save_owner') {
        $owner_id      = mysqli_real_escape_string($conn, $_POST['owner_id']);
        $district_id   = mysqli_real_escape_string($conn, $_POST['district_id']);
        $tehsil_id     = mysqli_real_escape_string($conn, $_POST['tehsil_id']);
        $full_name     = mysqli_real_escape_string($conn, $_POST['full_name']);
        $cnic          = mysqli_real_escape_string($conn, $_POST['cnic']);
        $contact       = mysqli_real_escape_string($conn, $_POST['contact_number']);
        
        // Banking Details (New Columns)
        $account_no    = mysqli_real_escape_string($conn, $_POST['account_number']);
        $account_title = mysqli_real_escape_string($conn, $_POST['account_title']);
        $account_type  = mysqli_real_escape_string($conn, $_POST['account_type']);
        
        $status        = mysqli_real_escape_string($conn, $_POST['status']);

        if (!empty($owner_id)) {
            // Update Query
            $sql = "UPDATE vehicle_owners SET 
                    district_id='$district_id', 
                    tehsil_id='$tehsil_id', 
                    full_name='$full_name', 
                    cnic='$cnic', 
                    contact_number='$contact', 
                    account_number='$account_no', 
                    account_title='$account_title', 
                    account_type='$account_type', 
                    status='$status' 
                    WHERE owner_id='$owner_id'";
            $res_text = "updated";
        } else {
            // Insert Query
            $sql = "INSERT INTO vehicle_owners (district_id, tehsil_id, full_name, cnic, contact_number, account_number, account_title, account_type, status) 
                    VALUES ('$district_id', '$tehsil_id', '$full_name', '$cnic', '$contact', '$account_no', '$account_title', '$account_type', '$status')";
            $res_text = "success";
        }

        if ($conn->query($sql)) { 
            echo $res_text; 
        } else { 
            echo "Error: " . $conn->error; 
        }
        exit;
    }

    // Action: Delete
    if ($_POST['action'] == 'delete_owner') {
        $id = mysqli_real_escape_string($conn, $_POST['owner_id']);
        if ($conn->query("DELETE FROM vehicle_owners WHERE owner_id = '$id'")) {
            echo "deleted";
        } else {
            echo "Error: " . $conn->error;
        }
        exit;
    }

    // --- ADDED FOR TABLE FILTERS: Get Tehsils by District Name ---
    if ($_POST['action'] == 'get_tehsils_by_name') {
        $d_name = mysqli_real_escape_string($conn, $_POST['d_name']);
        
        // District Name ke zariye Active Tehsils fetch karna
        $query = "SELECT t.tehsil_name FROM tehsils t 
                  JOIN districts d ON t.district_id = d.district_id 
                  WHERE d.district_name = '$d_name' AND t.status = 'Active'";
        
        $res = $conn->query($query);
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $t_name = trim($row['tehsil_name']);
                echo "<option value='$t_name'>$t_name</option>";
            }
        }
        exit;
    }
}
?>