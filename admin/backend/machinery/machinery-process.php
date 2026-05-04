<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../config.php');

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- 1. DELETE MACHINERY ---
    if ($action == 'delete_machinery') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);

        if (!empty($id)) {
            // Table: machinery_registration, Column: machine_id
            $sql = "DELETE FROM machinery_registration WHERE machine_id = '$id'";

            if ($conn->query($sql)) {
                echo "success";
            } else {
                echo "DB Error: " . $conn->error;
            }
        } else {
            echo "Invalid ID provided";
        }
        exit;
    }

    // --- 2. SAVE OR UPDATE MACHINERY ---
    if ($action == 'save_machinery') {
        $machine_id = mysqli_real_escape_string($conn, $_POST['machine_id'] ?? '');
        $district_id = mysqli_real_escape_string($conn, $_POST['district_id']);
        $tehsil_id = mysqli_real_escape_string($conn, $_POST['tehsil_id']);
        $type_id = mysqli_real_escape_string($conn, $_POST['type_id']);
        $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
        $sub_id = mysqli_real_escape_string($conn, $_POST['sub_id']);
        $registration_no = mysqli_real_escape_string($conn, $_POST['registration_no']);
        $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Active');

        if (!empty($machine_id)) {
            // Update Logic
            $sql = "UPDATE machinery_registration SET 
                    district_id='$district_id', tehsil_id='$tehsil_id', type_id='$type_id', 
                    category_id='$category_id', sub_id='$sub_id', registration_no='$registration_no', 
                    status='$status' WHERE machine_id='$machine_id'";
        } else {
            // Insert Logic
            $sql = "INSERT INTO machinery_registration (district_id, tehsil_id, type_id, category_id, sub_id, registration_no, status) 
                    VALUES ('$district_id', '$tehsil_id', '$type_id', '$category_id', '$sub_id', '$registration_no', '$status')";
        }

        if ($conn->query($sql)) {
            echo "success";
        } else {
            echo "Error: " . $conn->error;
        }
        exit;
    }

    // --- 3. FETCH TEHSILS (Dependency) ---
    if ($action == 'fetch_tehsils') {
        $district_id = mysqli_real_escape_string($conn, $_POST['district_id']);
        $query = "SELECT * FROM tehsils WHERE district_id = '$district_id' AND status = 'Active' ORDER BY tehsil_name ASC";
        $result = mysqli_query($conn, $query);

        echo '<option value="">-- Choose Tehsil --</option>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<option value='" . $row['tehsil_id'] . "'>" . $row['tehsil_name'] . "</option>";
        }
        exit;
    }

    // --- 4. FETCH CATEGORIES (Dependency) ---
    if ($action == 'fetch_categories') {
        $type_id = mysqli_real_escape_string($conn, $_POST['type_id']);
        $sql = "SELECT category_id, category_name FROM expense_categories WHERE type_id = '$type_id' AND status = 'Active'";
        $result = $conn->query($sql);

        echo '<option value="">-- Choose Category --</option>';
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['category_id'] . '">' . $row['category_name'] . '</option>';
        }
        exit;
    }

    // --- 5. FETCH SUB-CATEGORIES (Dependency) ---
    if ($action == 'fetch_subcategories') {
        $cat_id = mysqli_real_escape_string($conn, $_POST['category_id']);
        $sql = "SELECT sub_id, sub_name FROM expense_sub_categories WHERE category_id = '$cat_id' AND status = 'Active'";
        $result = $conn->query($sql);

        echo '<option value="">-- Choose Sub Category --</option>';
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['sub_id'] . '">' . $row['sub_name'] . '</option>';
        }
        exit;
    }

    // --- 6. FETCH MANAGERS ---
    if ($action == 'fetch_managers') {
        $tehsil_id = mysqli_real_escape_string($conn, $_POST['tehsil_id']);
        $sql = "SELECT staff_id, staff_name FROM staff WHERE tehsil_id = '$tehsil_id' AND designation = 'Manager' AND status = 'Active'";
        $result = $conn->query($sql);

        echo '<option value="">-- Choose Manager --</option>';
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['staff_id'] . '">' . $row['staff_name'] . '</option>';
        }
        exit;
    }

    // --- 7. FETCH REGISTRATION NUMBERS (For Dynamic Inputs) ---
    if ($action == 'fetch_reg_numbers') {
        $sub_id = mysqli_real_escape_string($conn, $_POST['sub_id']);
        $sql = "SELECT registration_no FROM machinery_registration WHERE sub_id = '$sub_id' AND status = 'Active'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo '<label class="form-label small fw-bold text-muted text-uppercase">Machinery Reg No.</label>';
            echo '<select name="item_name" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;" required>';
            echo '<option value="">-- Select Vehicle --</option>';
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . $row['registration_no'] . '">' . $row['registration_no'] . '</option>';
            }
            echo '</select>';
        } else {
            echo '<label class="form-label small fw-bold text-muted text-uppercase">Item Name / Details</label>';
            echo '<input type="text" name="item_name" class="form-control border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;" placeholder="Enter details manually...">';
        }
        exit;
    }
}
