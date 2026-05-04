<?php 
if (!isset($conn)) {
    // Path selection optimized
    if (file_exists('../backend/config.php')) {
        include('../backend/config.php'); 
    } elseif (file_exists('../../backend/config.php')) {
        include('../../backend/config.php'); 
    } else {
        // Default fallback if needed
        include('backend/config.php');
    }
}
?>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-0">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="fw-bold m-0 text-dark">
                <i class="fas fa-plus-circle text-success me-2"></i> Add Company Income
            </h5>
            <button onclick="loadContent('components/income/income-list.php')" class="btn btn-white rounded-pill px-4 shadow fw-bold animate-btn">
                     <i class="fas fa-list me-1"></i> View List
                </button>
        </div>
    </div>
    <div class="card-body p-4">
        <!-- Form action removed because we use AJAX in income.js -->
        <form id="incomeForm" enctype="multipart/form-data">
            <div class="row g-4">
                <!-- Basic Info Row -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Date</label>
                    <input type="date" name="income_date" id="income_date" value="<?= date('Y-m-d') ?>" class="form-control rounded-pill border-light bg-light shadow-none" required>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">District</label>
                    <!-- Updated: Called via IncomeModule namespace -->
                    <select name="district_id" id="income_district" class="form-select select2-init" onchange="IncomeModule.fetchTehsils(this.value)" required>
                        <option value="">Select District</option>
                        <?php 
                        $res = $conn->query("SELECT * FROM districts WHERE status='Active' ORDER BY district_name");
                        if($res){
                            while($d = $res->fetch_assoc()) echo "<option value='{$d['district_id']}'>{$d['district_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Tehsil</label>
                    <select name="tehsil_id" id="income_tehsil" class="form-select select2-init" required>
                        <option value="">Select Tehsil</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Amount (PKR)</label>
                    <input type="number" name="amount" class="form-control rounded-pill border-light bg-light shadow-none" placeholder="0.00" step="0.01" min="1" required>
                </div>

                <!-- Payment Method Selection -->
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Payment Received Via</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 rounded-start-pill pe-0"><i class="fas fa-money-check-alt text-muted"></i></span>
                        <!-- Updated: Called via IncomeModule namespace -->
                        <select name="payment_method" id="payment_method" class="form-select rounded-end-pill border-light bg-light shadow-none" onchange="IncomeModule.toggleUI(this.value)" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank Account">Bank Account</option>
                            <option value="Mobile Account">Mobile Account</option>
                        </select>
                    </div>
                </div>

                <!-- Dynamic Details Section -->
                <div class="col-md-8">
                    <div id="dynamic_fields" class="row g-2 p-3 rounded-4 bg-light-success border border-white min-h-fields">
                        <div class="col-md-4">
                            <input type="text" name="receiver_name" class="form-control form-control-sm rounded-pill" placeholder="Receiver Name" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="contact_no" class="form-control form-control-sm rounded-pill" placeholder="Contact Num" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="cnic" class="form-control form-control-sm rounded-pill" placeholder="CNIC">
                        </div>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="col-md-12">
                    <label class="form-label small fw-bold text-muted">Upload Proof (SS/Receipt)</label>
                    <div class="upload-container bg-light rounded-4 p-3 text-center border-dashed">
                        <input type="file" name="proof_img" id="proof_img" class="form-control shadow-none border-0 bg-transparent" accept="image/*">
                        <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle me-1"></i> PNG, JPG or JPEG allowed (Max 2MB)</small>
                    </div>
                </div>

                <div class="col-md-12 text-end mt-4">
                    <button type="submit" class="btn btn-success px-5 py-2 rounded-pill shadow btn-save fw-bold">
                        <span class="spinner-border spinner-border-sm d-none me-2"></span> 
                        <i class="fas fa-save me-1"></i> Save Income Entry
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .bg-light-success { background-color: #f8fff9; min-height: 85px; display: flex; align-items: center; }
    .border-dashed { border: 2px dashed #d1d9e0; transition: all 0.3s; }
    .border-dashed:hover { border-color: #2ecc71; background-color: #f0fdf4 !important; }
    .form-control:focus, .form-select:focus { border-color: #2ecc71; box-shadow: 0 0 0 0.25rem rgba(46, 204, 113, 0.1); }
    .min-h-fields { min-height: 85px; }
    /* Select2 Rounded Style Fix */
    .select2-container--default .select2-selection--single {
        border-radius: 50px !important;
        background-color: #f8f9fa !important;
        border: 1px solid #f8f9fa !important;
        height: 38px !important;
        line-height: 38px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 35px !important;
        padding-left: 15px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>