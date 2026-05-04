<?php
include('../config.php');

$action = $_POST['action'] ?? '';

// --- Action: Get Tehsils ---
if ($action == 'get_tehsils') {
    $d_id = (int)$_POST['district_id'];
    $res = $conn->query("SELECT tehsil_id, tehsil_name FROM tehsils WHERE district_id = $d_id AND status = 'Active' ORDER BY tehsil_name ASC");
    $options = "";
    while($row = $res->fetch_assoc()) {
        $options .= "<option value='{$row['tehsil_id']}'>{$row['tehsil_name']}</option>";
    }
    echo $options;
    exit;
}

// --- Action: Save Income ---
if ($action == 'save_income') {
    $date = $_POST['income_date'];
    $dist = $_POST['district_id'];
    $teh  = $_POST['tehsil_id'];
    $amt  = $_POST['amount'];
    $meth = $_POST['payment_method'];
    $cont = $_POST['contact_no'];
    
    $recv = $_POST['receiver_name'] ?? '';
    $acc  = $_POST['account_details'] ?? '';
    $hold = $_POST['holder_name'] ?? '';
    $cnic = $_POST['cnic'] ?? '';

    // Image Upload Handling
    $image_name = "";
    if (!empty($_FILES['proof_img']['name'])) {
        // VS Code structure k mutabiq: backend/income/ se bahar nikal kar uploads/accounts/ me jana
        $upload_path = "../../uploads/accounts/"; 
        if (!is_dir($upload_path)) mkdir($upload_path, 0777, true);
        
        $ext = pathinfo($_FILES['proof_img']['name'], PATHINFO_EXTENSION);
        $image_name = "INC_" . time() . "." . $ext;
        move_uploaded_file($_FILES['proof_img']['tmp_name'], $upload_path . $image_name);
    }

    $sql = "INSERT INTO company_income (income_date, district_id, tehsil_id, amount, payment_method, receiver_name, account_details, holder_name, contact_no, cnic, proof_img) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siidsssssss", $date, $dist, $teh, $amt, $meth, $recv, $acc, $hold, $cont, $cnic, $image_name);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Database Error: " . $conn->error;
    }
    exit;
}

// --- Action: Fetch Income List ---
if ($action == 'fetch_income_list') {
    $sql = "SELECT i.*, d.district_name, t.tehsil_name 
            FROM company_income i 
            LEFT JOIN districts d ON i.district_id = d.district_id 
            LEFT JOIN tehsils t ON i.tehsil_id = t.tehsil_id 
            ORDER BY i.income_date DESC";
    
    $res = $conn->query($sql);
    $html = "";

    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            // Path corrected for front-end display
            $img_url = "uploads/accounts/" . $row['proof_img'];
            
            // Function updated to match IncomeModule
            $proof_btn = !empty($row['proof_img']) 
                ? "<button class='btn btn-sm btn-outline-info rounded-pill px-3' onclick='IncomeModule.showProof(\"$img_url\", \"{$row['payment_method']}\")'><i class='fas fa-image'></i> View</button>" 
                : "<span class='text-muted small'>No Proof</span>";

            $payer = ($row['payment_method'] == 'Cash') ? $row['receiver_name'] : $row['holder_name'];

            $html .= "<tr>
                <td class='ps-3 text-muted'>" . date('d-m-Y', strtotime($row['income_date'])) . "</td>
                <td>
                    <div class='fw-bold text-dark'>{$row['district_name']}</div>
                    <div class='small text-muted'>{$row['tehsil_name']}</div>
                </td>
                <td class='fw-bold text-success'>Rs. " . number_format($row['amount'], 2) . "</td>
                <td>
                    <span class='badge bg-light text-dark border shadow-sm mb-1'>{$row['payment_method']}</span>
                    <div class='small fw-medium'>$payer</div>
                    <div class='x-small text-muted'>{$row['contact_no']}</div>
                </td>
                <td>{$proof_btn}</td>
                <td class='text-center'>
                    <button class='btn btn-sm btn-light border rounded-circle text-danger' onclick='IncomeModule.delete({$row['income_id']})'>
                        <i class='ri-delete-bin-line ri-lg'></i>
                    </button>
                </td>
            </tr>";
        }
    } else {
        $html = "<tr><td colspan='6' class='text-center p-5 text-muted'>No income records found.</td></tr>";
    }
    echo $html;
    exit;
}

// --- Action: Delete Income ---
if ($action == 'delete_income') {
    $id = (int)$_POST['id'];
    
    // Delete the image from folder
    $img_res = $conn->query("SELECT proof_img FROM company_income WHERE income_id = $id");
    if($img_res && $img_data = $img_res->fetch_assoc()){
        if(!empty($img_data['proof_img'])){
            unlink("../../uploads/accounts/" . $img_data['proof_img']);
        }
    }

    if ($conn->query("DELETE FROM company_income WHERE income_id = $id")) {
        echo "success";
    }
    exit;
}
?>