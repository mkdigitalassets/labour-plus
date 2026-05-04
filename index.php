<?php
session_start();

// 1. Agar login nahi hai, to seedha login page
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// 2. Agar login hai, to Role dekh kar redirect karo
if ($_SESSION['user_role'] === 'admin') {
    header("Location: admin/index.php");
} elseif ($_SESSION['user_role'] === 'manager') {
    header("Location: manager/index.php");
} else {
    // Agar role hi samajh nahi aa raha to logout
    header("Location: auth/logout.php");
}
exit();
?>