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

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="container-fluid p-4">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold m-0"><i class="ri-gas-station-fill text-primary me-2"></i>Daily Bulk Fuel Entry</h4>
            <div class="btns">
                <button type="button" onclick="resetFuelFilters()" class="btn btn-light btn-sm rounded-pill border shadow-sm px-3">
                    <i class="ri-refresh-line"></i> Reset Filters
                </button>
                <a onclick="loadContent('components/fuel/fuel-mileage.php')" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fas fa-plus-circle me-1"></i> Back to List
                </a>
            </div>
        </div>

        <div class="card-body bg-light-subtle">
            <form id="fuelFilterForm" class="row g-3 mb-4 p-3 bg-white rounded-3 shadow-sm mx-0">
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Issue Date</label>
                    <input type="date" id="fuel_date" value="<?= date('Y-m-d') ?>" class="form-control rounded-pill shadow-sm border-light">
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">District</label>
                    <select id="f_district" class="select2-init" onchange="fetchTehsils(this.value)">
                        <option value="">All Districts</option>
                        <?php
                        $res = $conn->query("SELECT * FROM districts WHERE status='Active'");
                        while ($d = $res->fetch_assoc()) echo "<option value='{$d['district_id']}'>{$d['district_name']}</option>";
                        ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Tehsil</label>
                    <select id="f_tehsil" class="select2-init">
                        <option value="">All Tehsils</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Vehicle Type</label>
                    <select id="f_v_type" class="select2-init">
                        <option value="">All Types</option>
                        <?php
                        $res = $conn->query("SELECT * FROM vehicle_types");
                        while ($vt = $res->fetch_assoc()) echo "<option value='{$vt['v_type_id']}'>{$vt['description']}</option>";
                        ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Search Vehicle (Reg No)</label>
                    <select id="f_vehicle_id" class="select2-init">
                        <option value="">Search Specific Vehicle</option>
                        <?php
                        $res = $conn->query("SELECT vehicle_id, reg_no FROM vehicles WHERE status='active'");
                        while ($v = $res->fetch_assoc()) echo "<option value='{$v['vehicle_id']}'>{$v['reg_no']}</option>";
                        ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" onclick="loadVehiclesForFuel()" class="btn btn-primary w-100 rounded-pill shadow">
                        <i class="ri-search-eye-line"></i> Filter Now
                    </button>
                </div>
            </form>

            <div id="fuelTableContainer" class="table-responsive bg-white rounded-4 p-3 shadow-sm border" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted">Show</span>
                        <select id="page_limit" class="form-select form-select-sm rounded-pill" style="width:90px;" onchange="loadVehiclesForFuel()">
                            <option value="10">10 Rows</option>
                            <option value="20">20 Rows</option>
                            <option value="50">50 Rows</option>
                            <option value="all">Show All</option>
                        </select>
                    </div>
                </div>

                <table class="table table-hover align-middle" id="bulkFuelTable">
                    <thead>
                        <tr class="text-uppercase small text-muted border-bottom" style="letter-spacing: 0.5px;">
                            <th class="ps-3">#</th>
                            <th>Vehicle Detail</th>
                            <th>Location Info</th>
                            <th class="text-center">Prev. Reading</th>
                            <th class="text-center" width="200">Fuel Qty (Liters)</th>
                        </tr>
                    </thead>
                    <tbody id="vehicleFuelBody">
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end text-muted">Page Total:</td>
                            <td class="text-center text-success small" id="page_total_fuel"></td>
                            <td class="text-center small fw-medium">Liters</td>
                        </tr>
                    </tfoot>
                </table>
                <!-- <div class="d-flex justify-content-end mb-3 px-3 w-100">
                    <div class="bg-primary text-white p-3 rounded-4 shadow-sm d-inline-block">
                        <h6 class="m-0 small opacity-75 text-uppercase fw-bold" style="letter-spacing: 1px;">Page Total Fuel</h6>
                        <div class="d-flex align-items-baseline gap-1">
                            <span class="fs-3 fw-bold" id="page_total_fuel">0.00</span>
                            <span class="small fw-medium">Liters</span>
                        </div>
                    </div>
                </div> -->

                <div class="p-3 border-top mt-3">
                    <button type="button" onclick="saveBulkFuel()" class="btn btn-success btn-lg px-5 rounded-pill shadow float-end">
                        <i class="ri-check-double-line"></i> Save All Entries
                    </button>
                    <div class="clearfix"></div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

</script>