<?php
if (!isset($conn)) {
    // Path configuration fix
    if (file_exists('backend/config.php')) {
        include('backend/config.php'); 
    } else {
        include('../../backend/config.php'); 
    }
}

// Ensure database connection consistency (mysqli vs PDO check)
// Agar aapka $db (PDO) hai aur $conn (mysqli), to dono ko handle kiya hai
?>

<div class="card border-0 shadow-sm p-4" style="border-radius: 15px;">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0 text-primary">
            <i class="ri-calendar-check-line me-2"></i>New Machinery Attendance
        </h5>
        <button type="button" onclick="loadContent('components/attendance/attendance-list.php')" class="btn btn-dark btn-sm shadow-sm">
            <i class="ri-list-check me-1"></i> View All Records
        </button>
    </div>

    <!-- Filters Section -->
    <div class="row g-2 mb-4 bg-light p-3 border" style="border-radius: 10px;">
        <div class="col-md-2">
            <label class="small fw-bold">Date</label>
            <input type="date" id="att_date" class="form-control shadow-none" value="<?= date('Y-m-d') ?>">
        </div>
        
        <div class="col-md-2">
            <label class="small fw-bold">District</label>
            <select id="f_dist" name="district_id" class="form-control shadow-none">
                <option value="">-- All Districts --</option>
                <?php
                // District fetch logic
                $dist_query = "SELECT district_id, district_name FROM districts WHERE status='active' ORDER BY district_name ASC";
                $districts = $conn->query($dist_query);
                while($row = $districts->fetch_assoc()) {
                    echo '<option value="'.$row['district_id'].'">'.$row['district_name'].'</option>';
                }
                ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="small fw-bold">Tehseel</label>
            <select id="f_teh" class="form-control shadow-none">
                <option value="">-- All Tehsils --</option>
                <?php
                // Initial load par saari tehsils dikhayen taake dropdown khali na rahe
                $teh_query = "SELECT tehsil_id, tehsil_name FROM tehsils WHERE status='active' ORDER BY tehsil_name ASC";
                $tehsils = $conn->query($teh_query);
                while($t = $tehsils->fetch_assoc()) {
                    echo "<option value='{$t['tehsil_id']}'>{$t['tehsil_name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="small fw-bold">Reg Number</label>
            <select id="f_reg" class="form-control shadow-none">
                <option value="">-- All Vehicles --</option>
                <?php
                $vehs = $conn->query("SELECT vehicle_id, reg_no FROM vehicles WHERE status='active' ORDER BY reg_no ASC");
                while($v = $vehs->fetch_assoc()) {
                    echo "<option value='{$v['vehicle_id']}'>{$v['reg_no']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="small fw-bold">Fuel Type</label>
            <select id="f_fuel" class="form-control shadow-none">
                <option value="">-- All Fuel --</option>
                <option value="Diesel">Diesel</option>
                <option value="Petrol">Petrol</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="small fw-bold">Company</label>
            <select id="f_comp" class="form-control shadow-none">
                <option value="">-- All Companies --</option>
                <?php
                $comps = $conn->query("SELECT company_id, company_name FROM companies WHERE status='Active' ORDER BY company_name ASC");
                while($c = $comps->fetch_assoc()) {
                    echo "<option value='{$c['company_id']}'>{$c['company_name']}</option>";
                }
                ?>
            </select>
        </div>
        <!-- Search & Reset Buttons -->
        <div class="col-md-4 d-flex align-items-end gap-2">
            <button type="button" onclick="loadAttendanceSheet()" class="btn btn-primary w-100 shadow-sm">
                <i class="ri-search-line me-1"></i> Search
            </button>
            <button type="button" onclick="resetAttendanceFilters()" class="btn btn-outline-secondary w-100 shadow-sm">
                <i class="ri-refresh-line me-1"></i> Reset
            </button>
        </div>
    </div>

    <!-- Attendance Form -->
    <form id="attendanceForm">
        <div class="table-responsive">
            <table class="table table-hover align-middle border">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="40" class="text-center">
                            <input type="checkbox" id="selectAll" class="form-check-input" checked>
                        </th>
                        <th class="text-start">Machinery Details</th>
                        <th>Company & Location</th>
                        <th width="180" class="text-center">Attendance Status</th>
                    </tr>
                </thead>
                <tbody id="attendanceSheet">
                    <!-- Loaded via AJAX -->
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm me-2"></div> Initializing Machinery List...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted small">
                <i class="ri-information-line me-1"></i> Only active machinery is listed above.
            </div>
            <button type="submit" id="saveAttBtn" class="btn btn-success px-5 shadow-sm">
                <i class="ri-save-line me-1"></i> Save Attendance
            </button>
        </div>
    </form>
</div>

<script>
// Select All Checkboxes Logic
$(document).on('change', '#selectAll', function() {
    $('.att-check').prop('checked', this.checked);
});
</script>