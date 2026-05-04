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

<div class="container-fluid p-4">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold m-0 text-primary"><i class="fas fa-gas-station me-2"></i>Fuel & Mileage Entry</h4>
                <p class="text-muted small mb-0">Manage vehicle consumption and meter readings</p>
            </div>
            <div class="d-flex gap-2">
                <a onclick="loadContent('components/fuel/add-fuel.php')" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fas fa-plus-circle me-1"></i> Add Fuel Page
                </a>
                <button type="button" onclick="location.reload()" class="btn btn-light btn-sm rounded-pill border shadow-sm px-3">
                    <i class="fas fa-sync"></i> Reset
                </button>
            </div>
        </div>

        <div class="card-body bg-light-subtle">
            <form id="fuelFilterForm" class="row g-3 mb-4 p-3 bg-white rounded-3 shadow-sm mx-0">
                <div class="col-md-2">
                    <label class="small fw-bold">Start Date</label>
                    <input type="date" id="start_date" value="<?= date('Y-m-d') ?>" class="form-control rounded-pill shadow-sm border-light">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">End Date</label>
                    <input type="date" id="end_date" value="<?= date('Y-m-d') ?>" class="form-control rounded-pill shadow-sm border-light">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">District</label>
                    <select id="f_district" class="select2-init form-control">
                        <option value="">All Districts</option>
                        <?php
                        $res = $conn->query("SELECT * FROM districts WHERE status='Active'");
                        while ($d = $res->fetch_assoc()) echo "<option value='{$d['district_id']}'>{$d['district_name']}</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">Tehsil</label>
                    <select id="f_tehsil" class="select2-init form-control">
                        <option value="">All Tehsils</option>
                        <?php
                        $res = $conn->query("SELECT * FROM tehsils WHERE status='Active'");
                        while ($t = $res->fetch_assoc()) echo "<option value='{$t['tehsil_id']}'>{$t['tehsil_name']}</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">Search Vehicle</label>
                    <select id="f_vehicle" class="select2-init form-control">
                        <option value="">Specific Vehicle...</option>
                        <?php
                        $res = $conn->query("SELECT vehicle_id, reg_no FROM vehicles WHERE status='Active'");
                        while ($v = $res->fetch_assoc()) echo "<option value='{$v['vehicle_id']}'>{$v['reg_no']}</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="small fw-bold">Rows</label>
                    <select id="page_limit" class="form-select rounded-pill shadow-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="all">All</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" onclick="loadData(1)" class="btn btn-primary w-100 rounded-pill shadow">
                        <i class="ri-search-eye-line"></i>
                    </button>
                </div>
            </form>

            <div id="tableContainer" class="table-responsive bg-white rounded-4 p-3 shadow-sm border" style="display:none;">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr class="text-uppercase small text-muted border-bottom">
                            <th width="50">#</th>
                            <th>Vehicle Detail</th>
                            <th class="text-center">Location Info</th>
                            <th class="text-center" width="180">Current Meter</th>
                            <th class="text-center" width="150">Fuel Qty (Ltr)</th>
                        </tr>
                    </thead>
                    <tbody id="fuelBody">
                    </tbody>
                    <tfoot class="bg-light fw-bold border-top">
                        <tr>
                            <td colspan="3" class="text-end text-muted">Page Total:</td>
                            <td class="text-center text-primary" id="page_total_meter">0.00</td>
                            <td class="text-center text-success" id="page_total_fuel">0.00</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="d-flex justify-content-between align-items-center mt-4 p-2">
                    <div id="paginationControls"></div>
                    <button type="button" onclick="saveBulkData()" class="btn btn-success btn-lg px-5 rounded-pill shadow-sm">
                        <i class="fas fa-save me-2"></i> Update Records
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

</script>