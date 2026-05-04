<?php
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    // Action 1: Delete
    if ($_POST['action'] == 'delete_category_type') {
        $id = mysqli_real_escape_string($conn, $_POST['type_id']);

        if ($conn->query("DELETE FROM expense_category_types WHERE type_id = '$id'")) {
            echo "deleted";
        } else {
            echo "Error: " . $conn->error;
        }
        exit;
    }

    // Action 2: Save or Update
    if ($_POST['action'] == 'save_category_type') {
        $type_name = mysqli_real_escape_string($conn, $_POST['type_name']);
        $status    = mysqli_real_escape_string($conn, $_POST['status']);
        $type_id   = (isset($_POST['type_id']) && !empty($_POST['type_id'])) ? mysqli_real_escape_string($conn, $_POST['type_id']) : '';

        // Duplication Check
        $check_query = "SELECT type_id FROM expense_category_types WHERE type_name = '$type_name'";
        if (!empty($type_id)) {
            $check_query .= " AND type_id != '$type_id'";
        }

        $check_res = $conn->query($check_query);
        if ($check_res && $check_res->num_rows > 0) {
            echo "exists";
            exit;
        }

        if (!empty($type_id)) {
            // Update Query
            $sql = "UPDATE expense_category_types SET 
                    type_name = '$type_name', 
                    status = '$status' 
                    WHERE type_id = '$type_id'";
            $res_text = "updated";
        } else {
            // Insert Query
            $sql = "INSERT INTO expense_category_types (type_name, status) 
                    VALUES ('$type_name', '$status')";
            $res_text = "success";
        }

        if ($conn->query($sql)) {
            echo $res_text;
        } else {
            echo "Database Error: " . $conn->error;
        }
        exit;
    }
}
?>