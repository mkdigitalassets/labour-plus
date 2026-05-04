<?php
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    $action = $_POST['action'];

    /*
    =====================================
    1. FETCH CATEGORIES
    =====================================
    */
    if ($action == 'fetch_categories') {

        $type_id = mysqli_real_escape_string($conn, $_POST['type_id']);
        $selected_id = isset($_POST['selected_id'])
            ? mysqli_real_escape_string($conn, $_POST['selected_id'])
            : '';

        $res = $conn->query("
            SELECT *
            FROM expense_categories
            WHERE type_id = '$type_id'
            AND status = 'Active'
        ");

        echo '<option value="">Select Category...</option>';

        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {

                $selected = ($row['category_id'] == $selected_id)
                    ? 'selected'
                    : '';

                echo '<option value="' . $row['category_id'] . '" ' . $selected . '>
                        ' . $row['category_name'] . '
                      </option>';
            }
        }

        exit;
    }

    /*
    =====================================
    2. SAVE SUB CATEGORY
    =====================================
    */
    if ($action == 'save_sub_category') {

        $sub_id = mysqli_real_escape_string($conn, $_POST['sub_id']);
        $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
        $sub_name = mysqli_real_escape_string($conn, $_POST['sub_name']);
        $status = "Active";

        if (!empty($sub_id)) {
            // UPDATE
            $sql = "
                UPDATE expense_sub_categories
                SET
                    category_id = '$category_id',
                    sub_name = '$sub_name'
                WHERE sub_id = '$sub_id'
            ";
        } else {
            // INSERT
            $sql = "
                INSERT INTO expense_sub_categories
                (
                    category_id,
                    sub_name,
                    status
                )
                VALUES
                (
                    '$category_id',
                    '$sub_name',
                    '$status'
                )
            ";
        }

        if ($conn->query($sql)) {
            echo "success";
        } else {
            echo "Error: " . $conn->error;
        }

        exit;
    }

    /*
    =====================================
    3. DELETE SUB CATEGORY
    =====================================
    */
    if ($action == 'delete_sub_category') {

        $sub_id = mysqli_real_escape_string($conn, $_POST['sub_id']);

        $sql = "
            DELETE FROM expense_sub_categories
            WHERE sub_id = '$sub_id'
        ";

        if ($conn->query($sql)) {
            echo "success";
        } else {
            echo "Error: " . $conn->error;
        }

        exit;
    }
}
