<?php
session_start(); // Login session ke liye ye sab se upar hona zaroori hai
include('../config.php');

// ==========================================
// 1. LOGIN USER LOGIC (Naya Section)
// ==========================================
if (isset($_POST['action']) && $_POST['action'] == 'login_user') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check karein ke user active hai aur credentials sahi hain
    $sql = "SELECT * FROM auth WHERE username = '$username' AND password = '$password' AND status = 'Active' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Session mein data save karein
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['district_id'] = $user['district_id'];
        $_SESSION['tehsil_id'] = $user['tehsil_id'];

        echo "success";
    } else {
        echo "Invalid Username/Password or Account Inactive!";
    }
    exit;
}

// ==========================================
// 2. DISTRICTS FETCH (Aapka Purana Code)
// ==========================================
if (isset($_POST['action']) && $_POST['action'] == 'get_districts_from_staff') {
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $sql = "SELECT DISTINCT t.district_id, d.district_name 
            FROM staff s 
            JOIN tehsils t ON s.tehsil_id = t.tehsil_id 
            JOIN districts d ON t.district_id = d.district_id
            WHERE s.staff_role = '$role'";
    $res = $conn->query($sql);
    echo '<option value="">Select District</option>';
    while ($row = $res->fetch_assoc()) {
        echo "<option value='{$row['district_id']}'>{$row['district_name']}</option>";
    }
    exit;
}

// ==========================================
// 3. TEHSILS FETCH (Aapka Purana Code)
// ==========================================
if (isset($_POST['action']) && $_POST['action'] == 'get_tehsils_from_staff') {
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $dist = mysqli_real_escape_string($conn, $_POST['district_id']);
    $sql = "SELECT DISTINCT s.tehsil_id, t.tehsil_name 
            FROM staff s 
            JOIN tehsils t ON s.tehsil_id = t.tehsil_id 
            WHERE t.district_id = '$dist' AND s.staff_role = '$role'";
    $res = $conn->query($sql);
    echo '<option value="">Select Tehsil</option>';
    while ($row = $res->fetch_assoc()) {
        echo "<option value='{$row['tehsil_id']}'>{$row['tehsil_name']}</option>";
    }
    exit;
}

// ==========================================
// 4. STAFF NAMES FETCH (Aapka Purana Code)
// ==========================================
if (isset($_POST['action']) && $_POST['action'] == 'get_staff_names_only') {
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $teh  = mysqli_real_escape_string($conn, $_POST['tehsil_id'] ?? '');
    $query = "SELECT staff_name FROM staff WHERE staff_role = '$role'";
    if (strtolower($role) !== 'admin' && !empty($teh)) {
        $query .= " AND tehsil_id = '$teh'";
    }
    $res = $conn->query($query);
    echo '<option value="">Select Name</option>';
    while ($row = $res->fetch_assoc()) {
        echo "<option value='{$row['staff_name']}'>{$row['staff_name']}</option>";
    }
    exit;
}

// ==========================================
// 5. SAVE OR UPDATE USER (Aapka Purana Code)
// ==========================================
if (isset($_POST['action']) && $_POST['action'] == 'save_user') {
    $uid = mysqli_real_escape_string($conn, $_POST['user_id']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $dist_id = ($role != 'admin' && !empty($_POST['district_id'])) ? mysqli_real_escape_string($conn, $_POST['district_id']) : 'NULL';
    $teh_id = ($role != 'admin' && !empty($_POST['tehsil_id'])) ? mysqli_real_escape_string($conn, $_POST['tehsil_id']) : 'NULL';

    if (!empty($_POST['password'])) {
        $pass = mysqli_real_escape_string($conn, $_POST['password']);
        $pass_query = ", password = '$pass'";
    } else {
        $pass_query = "";
    }

    if (!empty($uid)) {
        $sql = "UPDATE auth SET username = '$username', role = '$role', status = '$status', district_id = $dist_id, tehsil_id = $teh_id $pass_query WHERE id = '$uid'";
    } else {
        $pass = mysqli_real_escape_string($conn, $_POST['password']);
        $sql = "INSERT INTO auth (username, password, role, status, district_id, tehsil_id) VALUES ('$username', '$pass', '$role', '$status', $dist_id, $teh_id)";
    }

    if ($conn->query($sql)) {
        echo "success";
    } else {
        echo "Database Error: " . $conn->error;
    }
    exit;
}

// ==========================================
// 6. DELETE USER (Aapka Purana Code)
// ==========================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_user') {
    $id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $sql = "DELETE FROM auth WHERE id = '$id'";
    if ($conn->query($sql)) {
        echo "deleted";
    } else {
        echo "Error deleting user: " . $conn->error;
    }
    exit;
}
