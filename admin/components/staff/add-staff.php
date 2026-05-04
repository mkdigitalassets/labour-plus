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

// Ab redirection sirf list page par hogi
$back_page = 'staff-list.php';

$staff_id = isset($_GET['id']) ? $_GET['id'] : '';

// Variables initialization
$name = "";
$phone = "";
$cnic = "";
$salary = "";
$date = date('Y-m-d');
$status = "Active";
$get_role = ""; // Ab ye empty hoga starting mein
$selected_tehsil = "";
$current_dist_id = "";

if (!empty($staff_id)) {
    $sql = "SELECT * FROM staff WHERE staff_id = '$staff_id'";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $name = $row['staff_name'];
        $phone = $row['staff_phone'];
        $cnic = $row['staff_cnic'];
        $salary = $row['fixed_salary'];
        $date = $row['joining_date'];
        $status = $row['status'];
        $get_role = $row['staff_role'];
        $selected_tehsil = $row['tehsil_id'];

        if (!empty($selected_tehsil)) {
            $dist_info = $conn->query("SELECT district_id FROM tehsils WHERE tehsil_id = '$selected_tehsil'")->fetch_assoc();
            $current_dist_id = $dist_info['district_id'] ?? '';
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0 text-dark">
        <i class="ri-user-add-line me-2 text-primary"></i>
        <?php echo (empty($staff_id)) ? "Register New Employee" : "Edit Employee Details"; ?>
    </h4>
    <button class="btn btn-light border rounded-pill px-4 shadow-sm"
        onclick="loadContent('components/staff/<?php echo $back_page; ?>')"
        style="transition: all 0.3s ease;">
        <i class="ri-arrow-left-line me-1"></i> Back to List
    </button>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 20px;">
    <div class="card-body p-4">
        <form id="staffForm">
            <input type="hidden" name="staff_id" value="<?php echo $staff_id; ?>">
            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">STAFF ROLE</label>
                    <div id="roleWrapper" class="position-relative">
                        <i id="roleIcon" class="ri-shield-user-line position-absolute top-50 start-0 translate-middle-y ms-3 fs-5" style="z-index: 10; color: #6366f1;"></i>
                        <select name="staff_role" id="staff_role_select" class="form-select border-0 ps-5 fw-bold"
                            style="height: 50px; border-radius: 12px; background-color: #eef2ff; color: #6366f1; cursor: pointer;" required>
                            <option value="" disabled <?php echo empty($get_role) ? 'selected' : ''; ?>>Choose Role</option>
                            <option value="Manager" <?php echo ($get_role == 'Manager') ? 'selected' : ''; ?>>Manager</option>
                            <option value="Driver" <?php echo ($get_role == 'Driver') ? 'selected' : ''; ?>>Driver</option>
                            <option value="Operator" <?php echo ($get_role == 'Operator') ? 'selected' : ''; ?>>Operator</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">SELECT DISTRICT</label>
                    <select id="staff_district" class="form-select border-0 bg-light" style="height: 50px; border-radius: 12px;">
                        <option value="">Select District</option>
                        <?php
                        $d_res = $conn->query("SELECT * FROM districts WHERE status='Active' ORDER BY district_name ASC");
                        while ($d_row = $d_res->fetch_assoc()) {
                            $sel = ($d_row['district_id'] == $current_dist_id) ? 'selected' : '';
                            echo "<option value='" . $d_row['district_id'] . "' $sel>" . $d_row['district_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">ASSIGN TEHSIL</label>
                    <select name="tehsil_id" id="tehsil_id" class="form-select border-0 bg-light" style="height: 50px; border-radius: 12px;">
                        <option value="">Select District First</option>
                        <?php
                        if (!empty($current_dist_id)) {
                            $t_res = $conn->query("SELECT * FROM tehsils WHERE district_id = '$current_dist_id' AND status='Active' ORDER BY tehsil_name ASC");
                            while ($t_row = $t_res->fetch_assoc()) {
                                $sel_t = ($t_row['tehsil_id'] == $selected_tehsil) ? 'selected' : '';
                                echo "<option value='" . $t_row['tehsil_id'] . "' $sel_t>" . $t_row['tehsil_name'] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">FULL NAME</label>
                    <input type="text" name="staff_name" value="<?php echo $name; ?>" class="form-control border-0 bg-light" required placeholder="Enter full name" style="height: 50px; border-radius: 12px;">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">PHONE NUMBER</label>
                    <input type="text" name="staff_phone" value="<?php echo $phone; ?>" class="form-control border-0 bg-light" placeholder="03xx-xxxxxxx" style="height: 50px; border-radius: 12px;">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">CNIC (13 Digits)</label>
                    <input type="number" name="staff_cnic" value="<?php echo $cnic; ?>" class="form-control border-0 bg-light" placeholder="42xxxxxxxxxxx" style="height: 50px; border-radius: 12px;" oninput="if (this.value.length > 13) this.value = this.value.slice(0, 13);">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">FIXED SALARY</label>
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-light" style="border-radius: 12px 0 0 12px;">Rs.</span>
                        <input type="number" name="fixed_salary" value="<?php echo $salary; ?>" class="form-control border-0 bg-light" required placeholder="0.00" style="height: 50px; border-radius: 0 12px 12px 0;">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">JOINING DATE</label>
                    <input type="date" name="joining_date" value="<?php echo $date; ?>" class="form-control border-0 bg-light" required style="height: 50px; border-radius: 12px;">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">ACCOUNT STATUS</label>
                    <select name="status" class="form-select border-0 bg-light" style="height: 50px; border-radius: 12px;">
                        <option value="Active" <?php echo ($status == 'Active') ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo ($status == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="col-12 text-end mt-4">
                    <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-lg" style="height: 50px; background: #6366f1; border: none;">
                        <i class="ri-save-line me-1"></i> <?php echo empty($staff_id) ? "Confirm Registration" : "Save Changes"; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Dynamic Icon Change for Roles
    $(document).ready(function() {
        $('#staff_role_select').on('change', function() {
            var role = $(this).val();
            var icon = $('#roleIcon');

            // Reset and Update Icon
            if (role === 'Manager') {
                icon.attr('class', 'ri-shield-user-line position-absolute top-50 start-0 translate-middle-y ms-3 fs-5');
            } else if (role === 'Driver') {
                icon.attr('class', 'ri-steering-2-line position-absolute top-50 start-0 translate-middle-y ms-3 fs-5');
            } else if (role === 'Operator') {
                icon.attr('class', 'ri-customer-service-2-line position-absolute top-50 start-0 translate-middle-y ms-3 fs-5');
            }
        }).trigger('change'); // Page load par icon set karne ke liye
    });
</script>