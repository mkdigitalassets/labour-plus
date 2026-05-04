<?php
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    // Action: Delete
    if ($_POST['action'] == 'delete_category') {
        $id = mysqli_real_escape_string($conn, $_POST['category_id']);
        if ($conn->query("DELETE FROM expense_categories WHERE category_id = '$id'")) {
            echo "success";
        } else {
            echo "Error: " . $conn->error;
        }
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'save_category') {
    $category_id   = mysqli_real_escape_string($conn, $_POST['category_id']);
    $type_id       = mysqli_real_escape_string($conn, $_POST['type_id']);
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $status        = mysqli_real_escape_string($conn, $_POST['status']);

    if (!empty($category_id)) {
        // Edit Mode
        $sql = "UPDATE expense_categories SET type_id='$type_id', category_name='$category_name', status='$status' WHERE category_id='$category_id'";
    } else {
        // Add Mode
        $sql = "INSERT INTO expense_categories (type_id, category_name, status) VALUES ('$type_id', '$category_name', '$status')";
    }

    if ($conn->query($sql)) { echo "success"; } else { echo $conn->error; }
    exit;
}
}
?>