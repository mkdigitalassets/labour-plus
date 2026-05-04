<?php

// Do level up jayein config tak pohanchne ke liye
include('../config.php');

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Status update query
    $sql = "UPDATE manager_expenses SET status = '$status' WHERE expense_id = '$id'";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo mysqli_error($conn);
    }
}
?>
