<?php
session_start();
// Path matching your login-process structure
include('../admin/backend/config.php'); 

// Session and Security Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['district_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$manager_name = $_SESSION['username'];
$dist_id = $_SESSION['district_id'];
$teh_id = $_SESSION['tehsil_id'];

// Fetch Location Names professionally
$loc_data = ['district_name' => 'Unknown', 'tehsil_name' => 'Unknown'];
$loc_query = "SELECT d.district_name, t.tehsil_name 
              FROM districts d 
              JOIN tehsils t ON t.district_id = d.district_id
              WHERE d.district_id = '$dist_id' AND t.tehsil_id = '$teh_id'";

$loc_res = $conn->query($loc_query);
if ($loc_res && $loc_res->num_rows > 0) {
    $loc_data = $loc_res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard | Expense Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .form-section { background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); border: 1px solid #edf2f7; }
        .section-title { border-bottom: 2px solid #f1f4f8; padding-bottom: 12px; margin-bottom: 25px; color: #2d3748; font-weight: 700; display: flex; align-items: center; }
        .section-title i { margin-right: 10px; color: #4a90e2; }
        .form-control, .form-select { border-radius: 12px; padding: 12px 15px; border: 1px solid #e2e8f0; background-color: #fdfdfd; transition: all 0.3s ease; }
        .form-control:focus, .form-select:focus { box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15); border-color: #4299e1; outline: none; }
        .location-badge { background: #ebf4ff; color: #2b6cb0; padding: 10px 15px; border-radius: 12px; display: inline-block; font-weight: 600; }
        .btn-primary { border-radius: 12px; padding: 12px 30px; font-weight: 600; background: #3182ce; border: none; transition: transform 0.2s; }
        .btn-primary:hover { transform: translateY(-2px); background: #2b6cb0; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="ri-dashboard-3-line me-2"></i> Labour Plus</a>
        <div class="ms-auto text-white d-flex align-items-center">
            <span class="me-3"><i class="ri-user-star-line me-1 text-info"></i> <?php echo $manager_name; ?></span>
            <a href="../auth/logout.php" class="btn btn-danger btn-sm rounded-pill px-3">Logout</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="form-section">
        <form id="managerExpenseForm" enctype="multipart/form-data">
            <!-- Hidden context data -->
            <input type="hidden" name="action" value="save_manager_expense">
            <input type="hidden" name="district_id" value="<?php echo $dist_id; ?>">
            <input type="hidden" name="tehsil_id" value="<?php echo $teh_id; ?>">

            <!-- 1. Header Info -->
            <div class="row mb-5">
                <div class="col-md-6">
                    <label class="small fw-bold text-uppercase text-muted mb-2 d-block">Operating Region</label>
                    <div class="location-badge">
                        <i class="ri-map-pin-user-fill me-2"></i> 
                        <?php echo $loc_data['district_name']; ?> <i class="ri-arrow-right-s-line mx-1"></i> <?php echo $loc_data['tehsil_name']; ?>
                    </div>
                </div>
            </div>

            <!-- 2. Category Hierarchy Section -->
            <h6 class="section-title"><i class="ri-stack-line"></i> Expense Categorization</h6>
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">1. Category Type</label>
                    <select name="type_id" id="type_id" class="form-select" required>
                        <option value="">-- Select Type --</option>
                        <?php
                        // Corrected table name as per your previous code
                        $types = $conn->query("SELECT * FROM expense_category_types WHERE status = 'Active'");
                        while($t = $types->fetch_assoc()){
                            echo "<option value='{$t['type_id']}'>{$t['type_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">2. Category</label>
                    <select name="category_id" id="category_id" class="form-select" required>
                        <option value="">Choose Type First</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">3. Sub-Category</label>
                    <select name="sub_id" id="sub_id" class="form-select">
                        <option value="">Choose Category First</option>
                    </select>
                </div>

                <!-- Registration Container (Dynamic for Machinery) -->
                <div class="col-md-12" id="reg_no_container"></div>
            </div>

            <!-- 3. Financials -->
            <h6 class="section-title mt-5"><i class="ri-wallet-3-line"></i> Transaction Details</h6>
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Amount (PKR)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">Rs.</span>
                        <input type="number" name="amount" class="form-control border-start-0" placeholder="0.00" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Expense Date</label>
                    <input type="date" name="expense_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Payment Method</label>
                    <select name="payment_method" id="pay_method" class="form-select">
                        <option value="Cash">Cash</option>
                        <option value="Mobile Acc">Mobile Account</option>
                        <option value="Bank Account">Bank Transfer</option>
                    </select>
                </div>
                
                <!-- Extra Payment Fields (Hidden by default) -->
                <div class="col-md-12" id="payment_details_extra" style="display:none;">
                    <div class="card card-body bg-light border-0 rounded-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="pay_owner_name" class="form-control" placeholder="Account Title">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="pay_acc_no" class="form-control" placeholder="Account / Phone No">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="bank_name" class="form-control" placeholder="Bank / Platform Name">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="pay_contact" class="form-control" placeholder="Contact Person">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted text-uppercase">Bill/Receipt</label>
                    <input type="file" name="bill_attachment" class="form-control" accept="image/*,.pdf">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted text-uppercase">Transfer Proof</label>
                    <input type="file" name="transaction_attachment" class="form-control" accept="image/*,.pdf">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted text-uppercase">Description / Remarks</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Enter details about this expense..."></textarea>
                </div>
            </div>

            <div class="text-end mt-5">
                <button type="submit" class="btn btn-primary shadow-lg" id="submitBtn">
                    <i class="ri-checkbox-circle-line me-2"></i> Submit for Approval
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Sahi path constant mein rakh letay hain taake mistake na ho
    const backendPath = '../admin/backend/expenses/manager-expense-process.php';

    // 1. Fetch Categories based on Type
    $('#type_id').on('change', function() {
        let type_id = $(this).val();
        if(!type_id) return;

        $('#category_id').html('<option>Loading...</option>');
        
        $.ajax({
            url: backendPath,
            type: 'POST',
            data: {action: 'fetch_categories', type_id: type_id},
            success: function(data) {
                console.log("Categories Loaded");
                $('#category_id').html(data);
                $('#sub_id').html('<option value="">Choose Category First</option>');
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                alert("File not found! Path check karein: " + backendPath);
                $('#category_id').html('<option>Error loading data</option>');
            }
        });
    });

    // 2. Fetch Sub-Categories based on Category
    $('#category_id').on('change', function() {
        let cat_id = $(this).val();
        if(!cat_id) return;

        $('#sub_id').html('<option>Loading...</option>');
        $.post(backendPath, {action: 'fetch_subcategories', category_id: cat_id}, function(data) {
            $('#sub_id').html(data);
        });
    });

    // 3. Fetch Registration Numbers (Machinery check)
    $('#sub_id').on('change', function() {
        let sub_id = $(this).val();
        if(!sub_id) return;

        $.post(backendPath, {action: 'fetch_reg_numbers', sub_id: sub_id}, function(data) {
            if(data.trim() !== "") {
                $('#reg_no_container').html(data).hide().slideDown();
            } else {
                $('#reg_no_container').slideUp().html('');
            }
        });
    });

    // 4. Toggle Extra Payment Fields
    $('#pay_method').on('change', function() {
        if($(this).val() !== 'Cash') {
            $('#payment_details_extra').slideDown();
        } else {
            $('#payment_details_extra').slideUp();
        }
    });

    // 5. AJAX Form Submission
    $('#managerExpenseForm').on('submit', function(e) {
        e.preventDefault();
        
        let btn = $('#submitBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Submitting...');
        
        let formData = new FormData(this);
        $.ajax({
            url: backendPath,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                if(res.trim() === 'success') {
                    alert('Success! Expense record has been sent for approval.');
                    location.reload();
                } else {
                    alert('Response: ' + res);
                    btn.prop('disabled', false).html('<i class="ri-checkbox-circle-line me-2"></i> Submit for Approval');
                }
            },
            error: function() {
                alert("Critical Error: Could not connect to server.");
                btn.prop('disabled', false).html('<i class="ri-checkbox-circle-line me-2"></i> Submit for Approval');
            }
        });
    });
});
</script>

</body>
</html>