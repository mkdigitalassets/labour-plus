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

// --- SQL QUERY (Saara data fetch karne ke liye) ---
$sql = "SELECT s.*, st.staff_name, t.tehsil_name, d.district_name 
        FROM salaries s 
        INNER JOIN staff st ON s.staff_id = st.staff_id 
        INNER JOIN tehsils t ON st.tehsil_id = t.tehsil_id 
        INNER JOIN districts d ON t.district_id = d.district_id
        ORDER BY s.salary_id ASC"; // Yahan salary_id ko ASC (Ascending) kar dein

$result = $conn->query($sql);
?>

<style>
    /* Table ko mazeed khula karne ke liye */
    .table {
        table-layout: auto;
        /* min-width: 1400px; */
        /* Width thori aur barha di taake gap milay */
        border-collapse: separate;
        border-spacing: 0 8px;
        /* Rows ke darmiyan halka sa gap */
    }

    .table thead th {
        font-size: 12px;
        background: #f8fafc;
        white-space: nowrap;
        padding: 18px 15px !important;
        /* Header mein gap barha diya */
        letter-spacing: 0.5px;
    }

    .table tbody td {
        white-space: nowrap;
        padding: 16px 15px !important;
        /* Data cells mein gap barha diya */
        background-color: #fff;
    }

    /* Column widths ko makhsoos gap dena */
    .col-emp {
        min-width: 250px;
    }

    .col-loc {
        min-width: 180px;
    }

    .col-money {
        min-width: 130px;
    }

    .col-status {
        min-width: 150px;
    }

    .col-action {
        min-width: 100px;
    }

    /* Row hover effect with shadow */
    .table tbody tr {
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        background-color: #fcfdfe !important;
    }

    .avatar-sm {
        width: 42px;
        height: 42px;
    }

    .table-responsive {
        border-radius: 15px;
        overflow-x: auto;
        scrollbar-width: thin;
        /* Firefox ke liye */
        scrollbar-color: #6366f1 #f1f5f9;
        /* Firefox ke liye (thumb & track) */
    }

    /* 2. Scrollbar ki Height (Motaai) */
    .table-responsive::-webkit-scrollbar {
        height: 6px;
        /* Isko kam karne se scrollbar patla ho jayega */
    }

    /* 3. Scrollbar ka Track (Background) */
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f5f9;
        /* Halka grey background */
        border-radius: 10px;
    }

    /* 4. Scrollbar ka Thumb (Jo hissa move hota hai) */
    .table-responsive::-webkit-scrollbar-thumb {
        background: #6366f1;
        /* Indigo color (aapke theme ke mutabiq) */
        border-radius: 10px;
        border: 1px solid #f1f5f9;
        /* Track aur thumb ke darmiyan thori space dikhane ke liye */
    }

    /* 5. Hover karne par color change */
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #4f46e5;
        /* Thora dark indigo jab mouse ooper aaye */
    }
</style>

<div class="mb-4">
    <h4 class="fw-bold m-0 text-dark">Staff Salary Reports</h4>
    <p class="text-muted small m-0 pt-1">View and manage all payment history.</p>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center gap-3">

            <!-- Role Filter -->
            <select id="sal_filterRole" class="form-select border-0 shadow-sm rounded-pill" style="width: 150px; height: 45px;">
                <option value="">All Roles</option>
                <option value="Manager">Manager</option>
                <option value="Driver">Driver</option>
                <option value="Operator">Operator</option>
            </select>

            <!-- District Filter -->
            <select id="sal_filterDistrict" class="form-select border-0 shadow-sm rounded-pill" style="width: 150px; height: 45px;" onchange="fetchTehsilsForSalaryFilter(this.value)">
                <option value="">All Districts</option>
                <?php
                $dist_res = $conn->query("SELECT district_name FROM districts WHERE status = 'Active'");
                while ($d = $dist_res->fetch_assoc()) {
                    echo "<option value='" . $d['district_name'] . "'>" . $d['district_name'] . "</option>";
                }
                ?>
            </select>

            <!-- Tehsil Filter -->
            <select id="sal_filterTehsil" class="form-select border-0 shadow-sm rounded-pill" style="width: 150px; height: 45px;">
                <option value="">All Tehsils</option>
            </select>

            <!-- Status Filter -->
            <select id="sal_filterStatus" class="form-select border-0 shadow-sm rounded-pill" style="width: 150px; height: 45px;">
                <option value="">All Status</option>
                <option value="Paid">Paid</option>
                <option value="Pending">Pending</option>
                <option value="Pending">Partially Paid</option>
            </select>

            <!-- Search Bar -->
            <div class="position-relative">
                <i class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 ps-5 text-muted"></i>
                <input type="text" id="sal_salarySearch" class="form-control border-0 shadow-sm ps-5 ms-5 rounded-pill"
                    placeholder="Search name, role, phone..." style="width: 300px; height: 45px;">
            </div>

        </div>
    </div>
</div>

<button class="btn btn-primary rounded-circle shadow-lg position-fixed"
    onclick="loadContent('components/salary/add-salary.php')"
    onmouseover="this.style.transform='scale(1.1) rotate(90deg)';"
    onmouseout="this.style.transform='scale(1) rotate(0deg)';"
    style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 9999; border: none; transition: 0.3s; background: #6366f1;">
    <i class="ri-add-line fs-3"></i>
</button>

<div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="salaryTable">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4 py-3 text-muted fw-bold text-uppercase">Employee</th>
                    <th class="py-3 text-muted fw-bold text-uppercase">Role</th>
                    <th class="py-3 text-muted fw-bold text-uppercase">District/Tehsil</th>
                    <th class="py-3 text-muted fw-bold text-uppercase">Fixed Salary</th>
                    <th class="py-3 text-muted fw-bold text-uppercase">Working Days</th>
                    <th class="py-3 text-muted fw-bold text-uppercase">Bonus</th>
                    <th class="py-3 text-muted fw-bold text-uppercase">Leaves</th>
                    <th class="py-3 text-muted fw-bold text-uppercase">Deduction</th>
                    <th class="py-3 text-muted fw-bold text-uppercase">Amount</th>
                    <th class="py-3 text-muted fw-bold text-uppercase">Remaining</th>
                    <th class="py-3 text-muted fw-bold text-uppercase">Method</th>
                    <th class="py-3 text-muted fw-bold text-uppercase">Month</th>
                    <th class="py-3 text-muted fw-bold text-uppercase text-center">Status</th>
                    <th class="pe-4 py-3 text-muted fw-bold text-uppercase text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Data Variables
                        $fixed = $row['fixed_salary'] ?? 0;
                        $bonus = $row['bonus_amount'] ?? 0;
                        $deduction = $row['deduction_amount'] ?? 0;
                        $paid = $row['paid_amount'] ?? 0;
                        $w_days = $row['working_days'] ?? 0;
                        $leaves = $row['leaves'] ?? 0;

                        // Calculations
                        $net_payable = ($fixed + $bonus) - $deduction;
                        $remaining = $net_payable - $paid;

                        $p_status = trim($row['payment_status']); // trim() extra spaces bhi khatam kar dega

                        if (strtolower($p_status) == 'paid') {
                            $statusBadge = '<span class="badge bg-success-subtle text-success rounded-pill px-3">Paid</span>';
                        } elseif (strtolower($p_status) == 'partially paid') {
                            $statusBadge = '<span class="badge bg-info-subtle text-info rounded-pill px-3">Partially Paid</span>';
                        } else {
                            $statusBadge = '<span class="badge bg-warning-subtle text-warning rounded-pill px-3">Pending</span>';
                        }

                        $roleColor = ($row['staff_role'] == 'Manager') ? 'bg-primary' : (($row['staff_role'] == 'Driver') ? 'bg-info' : 'bg-secondary');
                ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3 bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                        <?php echo strtoupper(substr($row['staff_name'], 0, 1)); ?>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark" style="line-height: 1.2;"><?php echo $row['staff_name']; ?></span>
                                        <small class="text-muted" style="font-size: 10px;">ID: #<?php echo $row['salary_id']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge <?php echo $roleColor; ?> rounded-pill px-3" style="font-size: 10px;"><?php echo $row['staff_role']; ?></span></td>
                            <td>
                                <div class="small fw-bold text-dark"><?php echo $row['district_name']; ?></div>
                                <div class="text-muted small"><?php echo $row['tehsil_name']; ?></div>
                            </td>
                            <td class="fw-bold text-dark">Rs. <?php echo number_format($fixed); ?></td>
                            <td class="text-center"><?php echo $w_days; ?></td>
                            <td class="text-success fw-bold">+<?php echo number_format($bonus); ?></td>
                            <td class="text-center text-danger"><?php echo $leaves; ?></td>
                            <td class="text-danger fw-bold">-<?php echo number_format($deduction); ?></td>
                            <td>
                                <div class="fw-bold text-primary">Rs. <?php echo number_format($paid); ?></div>
                            </td>
                            <td class="fw-bold text-danger">Rs. <?php echo number_format($remaining); ?></td>
                            <td><span class="small fw-medium"><?php echo $row['payment_method']; ?></span></td>
                            <td><span class="text-muted fw-medium"><?php echo $row['salary_month']; ?></span></td>
                            <td class="text-center"><?php echo $statusBadge; ?></td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-sm btn-light rounded-circle border shadow-sm" onclick="loadContent('components/salary/add-salary.php?id=<?php echo $row['salary_id']; ?>')">
                                    <i class="ri-pencil-line text-primary"></i>
                                </button>
                                <button class="btn btn-sm btn-light rounded-circle border shadow-sm ms-1" onclick="deleteSalary(<?php echo $row['salary_id']; ?>)">
                                    <i class="ri-delete-bin-line text-danger"></i>
                                </button>
                            </td>
                        </tr>
                <?php }
                } else {
                    echo "<tr><td colspan='14' class='text-center p-5 text-muted'>No records found.</td></tr>";
                } ?>
            </tbody>
        </table>
    </div>
</div>