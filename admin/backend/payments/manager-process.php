<?php
include('../config.php');

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Basic Fields - Security ke liye real_escape_string use kiya hai
    $p_id = mysqli_real_escape_string($conn, $_POST['payment_id'] ?? '');
    $m_id = mysqli_real_escape_string($conn, $_POST['manager_id'] ?? '');
    $date = mysqli_real_escape_string($conn, $_POST['payment_date'] ?? '');
    $amt  = mysqli_real_escape_string($conn, $_POST['amount'] ?? '');
    $meth = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? '');
    $purp = mysqli_real_escape_string($conn, $_POST['purpose'] ?? '');
    
    // Bank Details (New Fields)
    $bank_n = mysqli_real_escape_string($conn, $_POST['bank_name'] ?? '');
    $acc_i  = mysqli_real_escape_string($conn, $_POST['account_info'] ?? '');

    // Image Handling Logic
    $image_sql = "";
    $target_dir = "../../uploads/payments/";

    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_ext = pathinfo($_FILES["payment_proof"]["name"], PATHINFO_EXTENSION);
        $file_name = "PAY_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $target_file)) {
            $image_sql = ", payment_proof = '$file_name'";
            
            // Update mode mein purani image delete karein
            if ($action == 'update_manager_payment' && !empty($p_id)) {
                $old_img_query = mysqli_query($conn, "SELECT payment_proof FROM manager_payments WHERE payment_id = '$p_id'");
                $old_img_row = mysqli_fetch_assoc($old_img_query);
                $old_img = $old_img_row['payment_proof'] ?? '';
                if (!empty($old_img) && file_exists($target_dir . $old_img)) {
                    unlink($target_dir . $old_img);
                }
            }
        }
    }

    // --- Actions ---

    // 1. SAVE Action
    if ($action == 'save_manager_payment') {
        $img_val = isset($file_name) ? $file_name : '';
        $sql = "INSERT INTO manager_payments 
                (manager_id, payment_date, amount, payment_method, bank_name, account_info, purpose, payment_proof) 
                VALUES 
                ('$m_id', '$date', '$amt', '$meth', '$bank_n', '$acc_i', '$purp', '$img_val')";
        
        if ($conn->query($sql)) {
            echo "success";
        } else {
            echo "Error: " . $conn->error;
        }
    } 
    
    // 2. UPDATE Action
    else if ($action == 'update_manager_payment') {
        if (empty($p_id)) { echo "Payment ID missing"; exit; }

        $sql = "UPDATE manager_payments SET 
                manager_id = '$m_id', 
                payment_date = '$date', 
                amount = '$amt', 
                payment_method = '$meth', 
                bank_name = '$bank_n', 
                account_info = '$acc_i', 
                purpose = '$purp' 
                $image_sql 
                WHERE payment_id = '$p_id'";
        
        if ($conn->query($sql)) {
            echo "success";
        } else {
            echo "Error: " . $conn->error;
        }
    }

    // 3. DELETE Action
    else if ($action == 'delete_manager_payment') {
        if (empty($p_id)) { echo "ID missing"; exit; }

        // Folder se image remove karein
        $img_query = mysqli_query($conn, "SELECT payment_proof FROM manager_payments WHERE payment_id = '$p_id'");
        $img_data = mysqli_fetch_assoc($img_query);
        if (!empty($img_data['payment_proof'])) {
            $path = $target_dir . $img_data['payment_proof'];
            if (file_exists($path)) { unlink($path); }
        }

        $sql = "DELETE FROM manager_payments WHERE payment_id = '$p_id'";
        echo ($conn->query($sql)) ? "success" : $conn->error;
    }
    
    exit;
}
?>