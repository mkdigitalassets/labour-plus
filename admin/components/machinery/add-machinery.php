<?php
if (!isset($conn)) {
    // Agar $conn pehle se nahi bana (yani index.php se load nahi ho raha), to include karein
    // Hum file_exists check kar ke path set karte hain
    if (file_exists('backend/config.php')) {
        include('backend/config.php'); // Jab index.php se load ho
    } else {
        include('../../backend/config.php'); // Jab dashboard.php direct chale
    }
} 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark">Register New Machinery</h4>
        <p class="text-muted small m-0 pt-1">Add machinery registration details with location and category.</p>
    </div>
    <button class="btn btn-light border rounded-pill px-4 shadow-sm" onclick="loadContent('components/machinery/machinery-list.php')">
        <i class="ri-history-line me-1"></i> View Inventory
    </button>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 20px;">
    <div class="card-body p-4 p-md-5">
        <form id="machineryForm">
            <input type="hidden" name="action" value="save_machinery">
            <input type="hidden" name="machine_id" id="machine_id">

            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Select District</label>
                    <select name="district_id" id="district_id" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;" onchange="fetchTehsils(this.value)">
                        <option value="">-- Choose District --</option>
                        <?php
                        // Filter added: WHERE status='Active'
                        $d_query = mysqli_query($conn, "SELECT * FROM districts WHERE status='Active'");
                        while ($d = mysqli_fetch_assoc($d_query)) {
                            echo "<option value='" . $d['district_id'] . "'>" . $d['district_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Select Tehsil</label>
                    <select name="tehsil_id" id="tehsil_id" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">
                        <option value="">-- Select District First --</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Category Type</label>
                    <select name="type_id" id="type_id" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;" onchange="fetchCategories(this.value)">
                        <option value="">-- Select Type --</option>
                        <?php
                        $t_query = mysqli_query($conn, "SELECT * FROM expense_category_types WHERE status='Active'");
                        while ($t = mysqli_fetch_assoc($t_query)) {
                            echo "<option value='" . $t['type_id'] . "'>" . $t['type_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Category</label>
                    <select name="category_id" id="expense_category" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;" onchange="fetchSubCategories(this.value)">
                        <option value="">-- Select Type First --</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Sub Category</label>
                    <select name="sub_id" id="sub_category" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">
                        <option value="">-- Select Category First --</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Registration Number</label>
                    <input type="text" name="registration_no" id="registration_no" placeholder="e.g. ABC-1234" required class="form-control border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">
                </div>

                <div class="col-md-4" id="status_container">
                    <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                    <select name="status" id="status" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-12 text-end pt-3">
                    <hr class="opacity-5">
                    <button type="submit" id="submitBtn" class="btn btn-primary px-5 py-2 rounded-pill shadow" style="background: #6366f1; border:none;">
                        <i class="ri-save-line me-1"></i> Save Machinery
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>