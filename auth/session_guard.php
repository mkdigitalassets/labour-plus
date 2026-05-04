<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check karein user login hai ya nahi
if (!isset($_SESSION['user_id'])) {
    // Ye line sabse aham hai. /labour/ se shuru karein taake components ka masla na ho
    header("Location: /labour/auth/login.php"); 
    exit();
}
?>