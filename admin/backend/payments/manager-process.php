<?php
include('../config.php');

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $p_id = mysqli_real_escape_string($conn, $_POST['payment_id'] ?? '');
    $m_id = mysqli_real_escape_string($conn, $_POST['manager_id'] ?? '');
    $date = mysqli_real_escape_string($conn, $_POST['payment_date'] ?? '');
    $amt  = mysqli_real_escape_string($conn, $_POST['amount'] ?? '');
    $meth = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? '');
    $purp = mysqli_real_escape_string($conn, $_POST['purpose'] ?? '');

    if ($action == 'save_manager_payment') {
        $sql = "INSERT INTO manager_payments (manager_id, payment_date, amount, payment_method, purpose) 
                VALUES ('$m_id', '$date', '$amt', '$meth', '$purp')";
        echo ($conn->query($sql)) ? "success" : $conn->error;
    } 
    
    if ($action == 'update_manager_payment') {
        $sql = "UPDATE manager_payments SET manager_id='$m_id', payment_date='$date', amount='$amt', 
                payment_method='$meth', purpose='$purp' WHERE payment_id='$p_id'";
        echo ($conn->query($sql)) ? "success" : $conn->error;
    }

    if ($action == 'delete_manager_payment') {
        $sql = "DELETE FROM manager_payments WHERE payment_id = '$p_id'";
        echo ($conn->query($sql)) ? "success" : $conn->error;
    }
    exit;
}