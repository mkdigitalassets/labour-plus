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
// Edit mode check (Agar ID pass hui hai to data fetch karein)
$user = null;
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $res = $conn->query("SELECT * FROM auth WHERE id = '$id'");
    $user = $res->fetch_assoc();
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-light rounded-circle shadow-sm" onclick="loadContent('components/auth/auth-list.php')" style="width: 45px; height: 45px;">
            <i class="ri-arrow-left-line"></i>
        </button>
        <div>
            <h3 class="fw-bold m-0 text-dark"><?php echo $user ? 'Edit User Account' : 'Create New User'; ?></h3>
            <p class="text-muted small m-0">Define role, region, and login credentials</p>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4">
    <form id="userForm">
        <input type="hidden" name="user_id" value="<?php echo $user['id'] ?? ''; ?>">
        <input type="hidden" name="action" value="save_user">

        <div class="row g-4">
            <!-- Role -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-muted small">SYSTEM ROLE</label>
                <?php
                $roles = $conn->query("SELECT DISTINCT staff_role FROM staff");

                ?>
                <select name="role" id="roleSelector" class="form-select border-0 bg-light rounded-3" style="height: 50px;" required onchange="toggleRegionalFields(this.value)">
                    <option value="">Select Role</option>
                    <?php
                    while ($r = $roles->fetch_assoc()) {
                        $selected = ($user && $user['role'] == $r['staff_role']) ? 'selected' : '';
                        echo "<option value='{$r['staff_role']}' $selected>{$r['staff_role']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- District -->
            <div id="regionalFields" class="row g-4 m-0 p-0">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-muted small">ASSIGN DISTRICT</label>
                    <select name="district_id" id="district_id" class="form-select border-0 bg-light rounded-3" style="height: 50px;"
                        onchange="fetchTehsils(this.value); fetchStaffByLocation();">
                        <option value="">Select District</option>
                    </select>
                </div>

                <!-- Tehsil -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-muted small">ASSIGN TEHSIL</label>
                    <select name="tehsil_id" id="tehsil_dropdown" class="form-select border-0 bg-light rounded-3" style="height: 50px;"
                        onchange="fetchStaffByLocation()">
                        <option value="">Select Tehsil</option>
                    </select>
                </div>
            </div>

            <!-- Staff Name -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-muted small">USERNAME</label>
                <select name="username" id="staff_dropdown" class="form-select border-0 bg-light rounded-3" style="height: 50px;" required>
                    <option value="">Select Name</option>
                </select>
            </div>

            <!-- Password -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-muted small">PASSWORD</label>
                <div class="input-group">
                    <input type="password" name="password" id="userPass" class="form-control border-0 bg-light rounded-3" style="height: 50px;" placeholder="Enter Password" <?php echo $user ? '' : 'required'; ?>>
                    <button class="btn btn-light border-0" type="button" onclick="togglePass()"><i class="ri-eye-line"></i></button>
                </div>
            </div>

            <!-- Status -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-muted small">ACCOUNT STATUS</label>
                <select name="status" class="form-select border-0 bg-light rounded-3" style="height: 50px;">
                    <option value="Active" <?php echo ($user && $user['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo ($user && $user['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <!-- Submit -->
            <div class="col-12 mt-4 text-end">
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow">
                    <i class="ri-save-3-line me-1"></i> <?php echo $user ? 'Update Account' : 'Save Account'; ?>
                </button>
            </div>
        </div>
    </form>
</div>