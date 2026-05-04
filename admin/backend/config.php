<?php
include 'db_init.php'; 

$conn = new mysqli("localhost", "root", "", "lp_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>