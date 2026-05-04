<?php
session_start();
// Strict Error Reporting for Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config.php');

// 1. Security Check: Kya user logged in hai?
if (!isset($_SESSION['user_id'])) {
    echo "Error: Unauthorized access. Please login again.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $current_user_id = $_SESSION['user_id'];
    $current_role = strtolower($_SESSION['user_role'] ?? '');

    if (empty($action)) {
        echo "Error: No action specified.";
        exit;
    }

    // ==========================================
    // A. FETCH LOGIC (Dropdowns)
    // ==========================================

    // 1. Fetch Tehsils
    if ($action == 'fetch_tehsils') {
        $district_id = (int)$_POST['district_id'];
        $sql = "SELECT * FROM tehsils WHERE district_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $district_id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo '<option value="">-- Choose Tehsil --</option>';
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['tehsil_id'] . '">' . $row['tehsil_name'] . '</option>';
        }
        exit;
    }

    // 2. Fetch Categories
    if ($action == 'fetch_categories') {
        $type_id = (int)$_POST['type_id'];
        $sql = "SELECT * FROM expense_categories WHERE type_id = ? AND status = 'Active'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $type_id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo '<option value="">-- Choose Category --</option>';
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['category_id'] . '">' . $row['category_name'] . '</option>';
        }
        exit;
    }

    // 3. Fetch Sub-Categories
    if ($action == 'fetch_subcategories') {
        $category_id = (int)$_POST['category_id'];
        $sql = "SELECT * FROM expense_sub_categories WHERE category_id = ? AND status = 'Active'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo '<option value="">-- Choose Sub Category --</option>';
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['sub_id'] . '">' . $row['sub_name'] . '</option>';
        }
        exit;
    }

    // 4. Fetch Registration Numbers (Machinery/Vehicles)
    if ($action == 'fetch_reg_numbers') {
        $sub_id = (int)$_POST['sub_id'];
        $sql = "SELECT registration_no FROM machinery_registration WHERE sub_id = ? AND status = 'Active'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $sub_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            echo '<label class="form-label small fw-bold text-muted text-uppercase">Machinery Reg No.</label>';
            echo '<select name="item_name" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;" required>';
            echo '<option value="">-- Select Registration --</option>';
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . $row['registration_no'] . '">' . $row['registration_no'] . '</option>';
            }
            echo '</select>';
        } else {
            echo '<label class="form-label small fw-bold text-muted text-uppercase">Item Name / Details</label>';
            echo '<input type="text" name="item_name" class="form-control border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;" placeholder="Enter details...">';
        }
        exit;
    }

    // ==========================================
    // B. SAVE / UPDATE LOGIC
    // ==========================================
    if ($action == 'save_manager_expense') {
        // 1. Session aur Basic Data
        $expense_id  = (!empty($_POST['expense_id'])) ? (int)$_POST['expense_id'] : null;
        $current_role = $_SESSION['user_role'] ?? '';
        
        // Sabse important step: Check karein session mein user_id hai ya nahi
        if (!isset($_SESSION['user_id'])) {
            echo "Error: Session expired. Please login again.";
            exit;
        }
        $user_id_from_session = (int)$_SESSION['user_id'];

        // 2. Attribution Logic (Kaun entry kar raha hai)
        if ($current_role !== 'admin') {
            // Manager ki details session se
            $manager_id  = $user_id_from_session;
            $district_id = isset($_SESSION['district_id']) ? (int)$_SESSION['district_id'] : 0;
            $tehsil_id   = isset($_SESSION['tehsil_id']) ? (int)$_SESSION['tehsil_id'] : 0;
            $status      = 'Pending';
        } else {
            // Admin dropdown se manager select karega
            $manager_id  = (int)$_POST['manager_id'];
            $district_id = (int)$_POST['district_id'];
            $tehsil_id   = (int)$_POST['tehsil_id'];
            $status      = 'Approved';
        }

        // --- DEBUG CHECK (Agar manager_id 0 hui to ye error de dega execute se pehle) ---
        if ($manager_id <= 0) {
            echo "Technical Error: Manager ID is missing. (Value: $manager_id)";
            exit;
        }

        // 3. Form Data
        $type_id     = (int)$_POST['type_id'];
        $category_id = (int)$_POST['category_id'];
        $sub_id      = (!empty($_POST['sub_id'])) ? (int)$_POST['sub_id'] : NULL;
        $amount      = $_POST['amount'];
        $exp_date    = $_POST['expense_date'];
        $pay_method  = $_POST['payment_method'];
        $description = $_POST['description'] ?? '';
        $item_name   = $_POST['item_name'] ?? '';

        // Payment Details
        $pay_owner   = $_POST['pay_owner_name'] ?? '';
        $pay_acc     = $_POST['pay_acc_no'] ?? '';
        $pay_contact = $_POST['pay_contact'] ?? '';
        $pay_cnic    = $_POST['pay_cnic'] ?? '';
        $bank_name   = $_POST['bank_name'] ?? '';

        // 4. File Upload Logic
        $upload_dir = "../../uploads/expenses/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $bill_img = "";
        $proof_img = "";

        if (!empty($_FILES['bill_attachment']['name'])) {
            $ext = strtolower(pathinfo($_FILES['bill_attachment']['name'], PATHINFO_EXTENSION));
            $bill_img = time() . "_bill_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['bill_attachment']['tmp_name'], $upload_dir . $bill_img);
        }
        if (!empty($_FILES['transaction_attachment']['name'])) {
            $ext = strtolower(pathinfo($_FILES['transaction_attachment']['name'], PATHINFO_EXTENSION));
            $proof_img = time() . "_proof_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['transaction_attachment']['tmp_name'], $upload_dir . $proof_img);
        }

        // 5. Database Save/Update
        if ($expense_id) {
            $sql = "UPDATE manager_expenses SET 
                    manager_id=?, district_id=?, tehsil_id=?, type_id=?, category_id=?, sub_id=?, 
                    amount=?, expense_date=?, payment_method=?, pay_owner_name=?, pay_acc_no=?, 
                    pay_contact=?, pay_cnic=?, bank_name=?, description=?, item_name=?";
            
            $params = [$manager_id, $district_id, $tehsil_id, $type_id, $category_id, $sub_id, $amount, $exp_date, $pay_method, $pay_owner, $pay_acc, $pay_contact, $pay_cnic, $bank_name, $description, $item_name];
            $types = "iiiiiidsssssssss";

            if ($bill_img != "") { $sql .= ", bill_attachment=?"; $params[] = $bill_img; $types .= "s"; }
            if ($proof_img != "") { $sql .= ", transaction_attachment=?"; $params[] = $proof_img; $types .= "s"; }
            
            $sql .= " WHERE expense_id=?";
            $params[] = $expense_id; $types .= "i";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
        } else {
            $sql = "INSERT INTO manager_expenses (
                        manager_id, district_id, tehsil_id, type_id, category_id, sub_id, 
                        amount, expense_date, payment_method, pay_owner_name, pay_acc_no, 
                        pay_contact, pay_cnic, bank_name, description, item_name, 
                        bill_attachment, transaction_attachment, status, added_by_role
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiiiidsssssssssssss", 
                $manager_id, $district_id, $tehsil_id, $type_id, $category_id, $sub_id, 
                $amount, $exp_date, $pay_method, $pay_owner, $pay_acc, 
                $pay_contact, $pay_cnic, $bank_name, $description, $item_name, 
                $bill_img, $proof_img, $status, $current_role
            );
        }

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Database Error: " . $stmt->error;
        }
        exit;
    }
    // ==========================================
    // C. ADMIN ACTIONS (Approval / Deletion)
    // ==========================================

    if ($action == 'approve_expense' && $current_role == 'admin') {
        $expense_id = (int)$_POST['expense_id'];
        $sql = "UPDATE manager_expenses SET status = 'Approved' WHERE expense_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $expense_id);
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Error: " . $stmt->error;
        }
        exit;
    }

    if ($action == 'delete_expense') {
        $expense_id = (int)$_POST['expense_id'];
        if ($current_role == 'admin') {
            $sql = "DELETE FROM manager_expenses WHERE expense_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $expense_id);
        } else {
            $sql = "DELETE FROM manager_expenses WHERE expense_id = ? AND status = 'Pending' AND manager_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $expense_id, $current_user_id);
        }

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Error: " . $stmt->error;
        }
        exit;
    }
}
?>