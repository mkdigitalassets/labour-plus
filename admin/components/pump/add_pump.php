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

$id = $_GET['id'] ?? '';
$p_name = $_GET['name'] ?? '';
$o_name = $_GET['owner'] ?? '';
$o_phone = $_GET['phone'] ?? '';
$o_acc = $_GET['acc'] ?? '';
$o_cnic = $_GET['cnic'] ?? '';
$t_id = $_GET['t_id'] ?? '';
?>

<div class="mb-4">
    <button class="btn btn-link text-decoration-none text-secondary p-0 mb-2"
        onclick="loadContent('components/pump/pump.php')">
        <i class="ri-arrow-left-line"></i> Back to Directory
    </button>
    <h3 class="fw-bold m-0 text-dark"><?php echo $id ? 'Edit' : 'Register'; ?> Fuel Pump</h3>
    <p class="text-muted small">Manage gas stations and owner credentials</p>
</div>

<div class="row">
    <div class="col-xl-10">
        <div class="card border-0 shadow-sm p-4" style="border-radius: 24px;">
            <form id="pumpForm" novalidate>
                <input type="hidden" id="p_id" value="<?php echo $id; ?>">

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small">Pump Name</label>
                        <input type="text" id="p_name" class="form-control py-2" value="<?php echo $p_name; ?>" required
                            style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small">Assign to Tehsil</label>
                        <select id="p_tehsil" class="form-select py-2 select2-init" required
                            style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                            <option value="">Choose Tehsil...</option>
                            <?php
                            $tehsils = $conn->query("SELECT tehsil_id, tehsil_name FROM tehsils ORDER BY tehsil_name ASC");
                            while ($t = $tehsils->fetch_assoc()) {
                                $sel = ($t['tehsil_id'] == $t_id) ? 'selected' : '';
                                echo "<option value='" . $t['tehsil_id'] . "' $sel>" . $t['tehsil_name'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <hr class="my-3 text-light">

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Owner Name</label>
                        <input type="text" id="o_name" class="form-control py-2" value="<?php echo $o_name; ?>" required
                            style="border-radius: 12px; background: #f8fafc;">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Owner Phone</label>
                        <input type="text" id="o_phone" class="form-control py-2" value="<?php echo $o_phone; ?>" required
                            style="border-radius: 12px; background: #f8fafc;">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Owner CNIC</label>
                        <input type="text" id="o_cnic" class="form-control py-2" value="<?php echo $o_cnic; ?>"
                            placeholder="31301-0000000-0" style="border-radius: 12px; background: #f8fafc;">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold text-secondary small">Bank Account Number</label>
                        <input type="text" id="o_acc" class="form-control py-2" value="<?php echo $o_acc; ?>"
                            placeholder="Enter full account number or IBAN" style="border-radius: 12px; background: #f8fafc;">
                    </div>

                    <div class="col-12 mt-5">
                        <button type="button" class="btn btn-primary px-5 py-2 fw-bold shadow-sm" onclick="savePump()"
                            style="border-radius: 12px;">
                            <i class="ri-save-line me-1"></i> Save Pump Info
                        </button>
                        <button type="button" class="btn btn-light px-4 py-2 ms-2" onclick="loadContent('components/pump/pump.php')"
                            style="border-radius: 12px;">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

</script>