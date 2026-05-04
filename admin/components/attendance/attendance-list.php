<?php
if (!isset($conn)) {
    if (file_exists('backend/config.php')) {
        include('backend/config.php');
    } else {
        include('../../backend/config.php');
    }
}
?>

<div id="attendance-list-wrapper" class="card border-0 shadow-sm p-4" style="border-radius: 15px;">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h5 class="fw-bold m-0 text-primary">
            <i class="ri-file-chart-line me-2"></i>Monthly Machinery Attendance Report
        </h5>
        <div class="d-flex gap-2">
            <button onclick="loadContent('components/attendance/add-attendance.php')" class="btn btn-success btn-sm shadow-sm">
                <i class="ri-add-line"></i> Add New
            </button>
            <button onclick="window.print()" class="btn btn-dark btn-sm shadow-sm">
                <i class="ri-printer-line"></i> Print
            </button>
        </div>
    </div>
    
    <!-- Professional Filters -->
    <div class="row g-2 mb-4 p-3 bg-light border no-print" style="border-radius: 12px;">
        <div class="col-md-2">
    <label class="small fw-bold">Start Date</label>
    <!-- report_month ko hata kar start_date karein -->
    <input type="date" id="start_date" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
</div>
<div class="col-md-2">
    <label class="small fw-bold">End Date</label>
    <input type="date" id="end_date" class="form-control form-control-sm" value="<?= date('Y-m-t') ?>">
</div>
        <div class="col-md-2">
            <label class="small fw-bold">District</label>
            <select id="r_dist" class="form-select shadow-none">
                <option value="">-- Overall --</option>
                <?php
                $dists = $conn->query("SELECT * FROM districts WHERE status='active' ORDER BY district_name ASC");
                while($d = $dists->fetch_assoc()) echo "<option value='{$d['district_id']}'>{$d['district_name']}</option>";
                ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="small fw-bold">Tehseel</label>
            <select id="r_tehsil" class="form-select shadow-none">
                <option value="">-- All Tehsils --</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="small fw-bold">Fuel Type</label>
            <select id="r_fuel" class="form-select shadow-none">
                <option value="">-- All --</option>
                <option value="Diesel">Diesel</option>
                <option value="Petrol">Petrol</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button onclick="loadMonthlyReport()" class="btn btn-primary w-100 shadow-sm">
                <i class="ri-search-line"></i> Generate
            </button>
            <button onclick="resetReport()" class="btn btn-outline-danger w-100 shadow-sm">
                <i class="ri-restart-line"></i> Reset
            </button>
        </div>
    </div>

    <!-- Monthly Report Print Area -->
    <div id="printArea">
    <!-- Top Info Bar (NTN & Mobile) -->
    <div class="d-none d-print-block">
        <div class="ntn-bar text-center py-1 border-bottom border-dark mb-2">
            NTN No: G045096-6 | Reg No: 3620376465971 | Mobile: 0309-6389030 , 0303-9027357
        </div>
        
        <div class="text-center mb-3">
            <!-- Main Title Bar -->
            <div class="main-title-box d-inline-block px-5 py-2 mb-2">
                <h1 class="fw-bold m-0" style="letter-spacing: 2px; font-size: 28px;">LABOUR PLUS</h1>
                <div class="small fw-bold border-top border-dark mt-1">General Order Suppliers</div>
            </div>
            
            <!-- Dynamic Heading -->
            <h5 class="fw-bold text-uppercase mt-2" id="locationHeader" style="text-decoration: underline;">
                ATTENDANCE REPORT FOR RENTAL VEHICLES
            </h5>
            <p class="fw-bold small mb-1" id="dateRangeDisplay" style="font-size: 12px;"></p>
        </div>
    </div>

    <!-- Table Container -->
    <div class="report-scroll-wrapper">
        <table class="table table-bordered align-middle" id="reportTable">
            <thead class="text-center" id="calendarHeader">
                <!-- JS se dynamic dates aur days ayenge -->
            </thead>
            <tbody id="reportData" class="text-center">
                <tr><td colspan="40" class="py-5">Loading Records...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Professional Blue Footer Bar -->
    <div class="print-footer-bar d-none d-print-flex">
        <div class="footer-left">Address: Behind Albadar Hospital, Khanewal Road, Lodhran</div>
        <div class="footer-right">Page 1 / 1</div>
    </div>
</div>
</div>

<script>

</script>

<style>
 
</style>