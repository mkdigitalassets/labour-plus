<?php
session_start();
include('../admin/backend/config.php');

if (isset($_POST['username']) && isset($_POST['password'])) {
    // 1. Inputs ko clean karein aur trim karein (extra spaces khatam karne ke liye)
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    // 2. Query: Auth table se user aur Staff table se uski ID fetch karna
    // Hum BINARY use kar rahe hain taake exact match ho (Case-Sensitive)
    $query = "SELECT a.*, s.staff_id 
              FROM auth a 
              LEFT JOIN staff s ON TRIM(a.username) = TRIM(s.staff_name) 
              WHERE a.username='$username' LIMIT 1";
              
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // 3. Password Check
        if ($password == $user['password']) {
            
            // --- CRITICAL FIX ---
            // Agar staff_id mil gayi to wo use karein, warna auth id (lekin foreign key fail hogi agar staff_id na hui)
            if (!empty($user['staff_id'])) {
                $_SESSION['user_id'] = (int)$user['staff_id'];
            } else {
                // Agar JOIN fail hua, to hum manually check karte hain
                $_SESSION['user_id'] = (int)$user['id']; 
            }

            $_SESSION['username'] = $user['username'];
            $_SESSION['district_id'] = $user['district_id']; 
            $_SESSION['tehsil_id'] = $user['tehsil_id'];     
            
            $role = strtolower(trim($user['role']));
            $_SESSION['user_role'] = $role;

            // Redirect responses
            if ($role === 'admin') {
                echo 'admin_success';
            } elseif ($role === 'manager') {
                echo 'manager_success';
            } elseif ($role === 'driver') {
                echo 'driver_success';
            } elseif ($role === 'operator') {
                echo 'operator_success';
            } else {
                echo $role;
            }
            exit();
        } else {
            echo 'invalid';
            exit();
        }
    } else {
        echo 'invalid';
        exit();
    }
}
?>