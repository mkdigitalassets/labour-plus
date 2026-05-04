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

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold m-0 text-dark">User Management</h3>
        <p class="text-muted small m-0 pt-2">Manage system access and user roles</p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <div class="position-relative">
            <i class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            <input type="text" id="userSearch" class="form-control border-0 shadow-sm ps-5 rounded-pill" placeholder="Search users..." style="width: 250px; height: 45px;">
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" style="height: 45px;" onclick="loadContent('components/auth/add-auth.php')">
            <i class="ri-user-add-line me-1"></i> Add User
        </button>
    </div>
</div>

<div class="table-modern border-0 shadow-sm bg-white" style="border-radius: 20px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4"># ID</th>
                    <th>Username</th>
                    <th>User Role</th>
                    <th>District</th>
                    <th>Tehsil</th>
                    <th>Password</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody id="userListData">
                <?php
                // JOIN query taake IDs ki jagah Names nazar aayein
                $sql = "SELECT auth.*, districts.district_name, tehsils.tehsil_name 
                        FROM auth 
                        LEFT JOIN districts ON auth.district_id = districts.district_id 
                        LEFT JOIN tehsils ON auth.tehsil_id = tehsils.tehsil_id 
                        ORDER BY auth.id ASC";

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Role-based badge colors
                        $roleBadge = 'bg-secondary';
                        if ($row['role'] == 'admin') $roleBadge = 'bg-danger';
                        else if ($row['role'] == 'manager') $roleBadge = 'bg-primary';
                        else if ($row['role'] == 'operator') $roleBadge = 'bg-info text-dark';
                        else if ($row['role'] == 'driver') $roleBadge = 'bg-warning text-dark';

                        $statusBadge = ($row['status'] == 'Active') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                ?>
                        <tr>
                            <td class="ps-4 text-muted">#<?php echo $row['id']; ?></td>
                            <td class="fw-semibold text-dark"><?php echo $row['username']; ?></td>
                            <td>
                                <span class="badge <?php echo $roleBadge; ?> rounded-pill px-3">
                                    <?php echo ucfirst($row['role']); ?>
                                </span>
                            </td>
                            <td class="text-muted small">
                                <?php echo ($row['role'] == 'admin') ? '<span class="text-muted">---</span>' : ($row['district_name'] ?? 'N/A'); ?>
                            </td>
                            <td class="text-muted small">
                                <?php echo ($row['role'] == 'admin') ? '<span class="text-muted">---</span>' : ($row['tehsil_name'] ?? 'N/A'); ?>
                            </td>
                            <td class="text-muted font-monospace" style="font-size: 0.85rem; letter-spacing: 1px;">
                                <?php echo $row['password']; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $statusBadge; ?> rounded-pill px-3">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm text-primary p-1" onclick="loadContent('components/auth/add-auth.php?id=<?php echo $row['id']; ?>')">
                                    <i class="ri-edit-box-line ri-lg"></i>
                                </button>
                                <button class="btn btn-sm text-danger p-1 ms-1" onclick="deleteUser(<?php echo $row['id']; ?>)">
                                    <i class="ri-delete-bin-line ri-lg"></i>
                                </button>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center py-4'>No users found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>