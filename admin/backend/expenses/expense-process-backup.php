<?php
session_start(); // Session data use karne ke liye zaroori hai
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // Check karein ke user admin hai ya manager
    $current_user_id   = $_SESSION['user_id'];
    $current_user_role = strtolower($_SESSION['user_role']);

    if (empty($action)) {
        echo "Error: No action specified.";
        exit;
    }

    // --- 1 to 5: Fetch Functions (Same rahenge, bas session based security add ki ja sakti hai) ---
    // (Aapka purana fetch_tehsils, fetch_categories etc. yahan aye ga)

    // 6. SAVE / UPDATE EXPENSE (MODIFIED)
    if ($action == 'save_manager_expense') {
        $expense_id  = (!empty($_POST['expense_id'])) ? (int)$_POST['expense_id'] : null;
        
        // Agar Manager login hai to session se uthaen, agar Admin hai to form se
        if ($current_user_role !== 'admin') {
            $manager_id  = (int)$current_user_id; 
            $district_id = (int)$_SESSION['district_id'];
            $tehsil_id   = (int)$_SESSION['tehsil_id'];
            $entry_status = 'Pending'; // Manager ki entry approval mangti hai
        } else {
            $manager_id  = (int)$_POST['manager_id']; // Admin kisi bhi manager ke liye add kar sakta hai
            $district_id = (int)$_POST['district_id'];
            $tehsil_id   = (int)$_POST['tehsil_id'];
            $entry_status = 'Approved'; // Admin ki entry direct approve hogi
        }

        $type_id     = (int)$_POST['type_id'];
        $category_id = (int)$_POST['category_id'];
        $sub_id      = (!empty($_POST['sub_id'])) ? (int)$_POST['sub_id'] : "NULL";

        $amount      = mysqli_real_escape_string($conn, $_POST['amount']);
        $exp_date    = mysqli_real_escape_string($conn, $_POST['expense_date']);
        $pay_method  = mysqli_real_escape_string($conn, $_POST['payment_method']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $item_name   = mysqli_real_escape_string($conn, $_POST['item_name'] ?? '');

        // Payment Details
        $pay_owner   = mysqli_real_escape_string($conn, $_POST['pay_owner_name'] ?? '');
        $pay_acc     = mysqli_real_escape_string($conn, $_POST['pay_acc_no'] ?? '');
        $pay_contact = mysqli_real_escape_string($conn, $_POST['pay_contact'] ?? '');
        $pay_cnic    = mysqli_real_escape_string($conn, $_POST['pay_cnic'] ?? '');
        $bank_name   = mysqli_real_escape_string($conn, $_POST['bank_name'] ?? '');

        // Image Handling
        $upload_dir = "../../uploads/expenses/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $bill_img = "";
        if (!empty($_FILES['bill_attachment']['name'])) {
            $bill_img = time() . "_bill_" . $_FILES['bill_attachment']['name'];
            move_uploaded_file($_FILES['bill_attachment']['tmp_name'], $upload_dir . $bill_img);
        }

        $proof_img = "";
        if (!empty($_FILES['transaction_attachment']['name'])) {
            $proof_img = time() . "_proof_" . $_FILES['transaction_attachment']['name'];
            move_uploaded_file($_FILES['transaction_attachment']['tmp_name'], $upload_dir . $proof_img);
        }

        if ($expense_id) {
            // UPDATE Logic
            $sql = "UPDATE manager_expenses SET 
                    manager_id=$manager_id, district_id=$district_id, tehsil_id=$tehsil_id, 
                    type_id=$type_id, category_id=$category_id, sub_id=$sub_id, 
                    amount='$amount', expense_date='$exp_date', payment_method='$pay_method', 
                    pay_owner_name='$pay_owner', pay_acc_no='$pay_acc', pay_contact='$pay_contact', 
                    pay_cnic='$pay_cnic', bank_name='$bank_name', description='$description', item_name='$item_name'";
            
            if ($bill_img != "") $sql .= ", bill_attachment='$bill_img'";
            if ($proof_img != "") $sql .= ", transaction_attachment='$proof_img'";
            
            $sql .= " WHERE expense_id=$expense_id";
        } else {
            // INSERT (Yahan 'added_by_role' aur 'status' important hain)
            $sql = "INSERT INTO manager_expenses (
                        manager_id, district_id, tehsil_id, type_id, category_id, sub_id, 
                        amount, expense_date, payment_method, pay_owner_name, pay_acc_no, 
                        pay_contact, pay_cnic, bank_name, description, item_name, 
                        bill_attachment, transaction_attachment, status, added_by_role
                    ) VALUES (
                        $manager_id, $district_id, $tehsil_id, $type_id, $category_id, $sub_id, 
                        '$amount', '$exp_date', '$pay_method', '$pay_owner', '$pay_acc', 
                        '$pay_contact', '$pay_cnic', '$bank_name', '$description', '$item_name', 
                        '$bill_img', '$proof_img', '$entry_status', '$current_user_role'
                    )";
        }

        if ($conn->query($sql)) {
            echo "success";
        } else {
            echo "SQL Error: " . $conn->error;
        }
        exit;
    }
}
?>