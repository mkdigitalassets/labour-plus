<?php
include('../../backend/config.php');

// --- AUTO TABLE CREATION LOGIC ---
$table_check = "CREATE TABLE IF NOT EXISTS pumps (
    pump_id INT AUTO_INCREMENT PRIMARY KEY,
    pump_name VARCHAR(255) NOT NULL,
    owner_id INT,
    contact_no VARCHAR(20),
    status VARCHAR(50) DEFAULT 'active',
    district_id INT,
    tehsil_id INT,
    address TEXT,
    petrol_capacity FLOAT DEFAULT 0,
    diesel_capacity FLOAT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conn->query($table_check);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 1. --- DELETE PUMP LOGIC (Added) ---
    if (isset($_POST['action']) && $_POST['action'] == 'delete_pump') {
        $pump_id = mysqli_real_escape_string($conn, $_POST['pump_id']);

        $sql = "DELETE FROM pumps WHERE pump_id = '$pump_id'";

        if ($conn->query($sql)) {
            echo "deleted";
        } else {
            echo "Database Error: " . $conn->error;
        }
        exit(); // Delete hone ke baad script yahan khatam ho jaye
    }

    // 2. --- INSERT / UPDATE LOGIC ---
    $pump_id = isset($_POST['pump_id']) ? mysqli_real_escape_string($conn, $_POST['pump_id']) : "";

    // Data Sanitization
    $name     = mysqli_real_escape_string($conn, $_POST['pump_name']);
    $owner    = !empty($_POST['owner_id']) ? (int)$_POST['owner_id'] : "NULL";
    $contact  = mysqli_real_escape_string($conn, $_POST['contact_no']);
    $status   = mysqli_real_escape_string($conn, $_POST['status']);
    $district = !empty($_POST['district_id']) ? (int)$_POST['district_id'] : "NULL";
    $tehsil   = !empty($_POST['tehsil_id']) ? (int)$_POST['tehsil_id'] : "NULL";
    $address  = mysqli_real_escape_string($conn, $_POST['address']);
    $p_cap    = (float)($_POST['petrol_capacity'] ?? 0);
    $d_cap    = (float)($_POST['diesel_capacity'] ?? 0);

    if (!empty($pump_id)) {
        // CASE: UPDATE EXISTING PUMP
        $sql = "UPDATE pumps SET 
                    pump_name='$name', 
                    owner_id=$owner, 
                    contact_no='$contact', 
                    status='$status', 
                    district_id=$district, 
                    tehsil_id=$tehsil, 
                    address='$address', 
                    petrol_capacity=$p_cap, 
                    diesel_capacity=$d_cap 
                WHERE pump_id=$pump_id";
        $response_text = "updated";
    } else {
        // CASE: INSERT NEW PUMP
        $sql = "INSERT INTO pumps (
                    pump_name, owner_id, contact_no, 
                    status, district_id, tehsil_id, address, 
                    petrol_capacity, diesel_capacity
                ) VALUES (
                    '$name', $owner, '$contact', 
                    '$status', $district, $tehsil, '$address', 
                    $p_cap, $d_cap
                )";
        $response_text = "success";
    }

    if ($conn->query($sql)) {
        echo $response_text;
    } else {
        echo "Database Error: " . $conn->error;
    }
} else {
    echo "Invalid Request Method";
}
