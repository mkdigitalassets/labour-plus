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
        /* font-size: 12px; */
        background: #f8fafc;
        white-space: nowrap;
        padding: 18px 35px !important;
        /* Header mein gap barha diya */
        letter-spacing: 0.5px;
    }

    .table tbody td {
        white-space: nowrap;
        padding: 16px 35px !important;
        /* Data cells mein gap barha diya */
        background-color: #fff;
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
    <h3 class="fw-bold m-0 text-dark">Vehicle Owners</h3>
    <p class="text-muted small m-0 pt-2">Manage owner profiles and payment details</p>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center gap-3">

            <!-- District Filter -->
            <select id="own_filterDistrict" class="form-select border-0 shadow-sm rounded-pill"
                style="width: 180px; height: 45px;"
                onchange="fetchTehsilsForCompanyFilter(this.value)">
                <option value="">All Districts</option>
                <?php
                $dist_res = $conn->query("SELECT * FROM districts WHERE status = 'Active'");
                while ($d = $dist_res->fetch_assoc()) {
                    // Yahan hum name filter ke liye value mein name bhej rahe hain
                    echo "<option value='{$d['district_name']}'>{$d['district_name']}</option>";
                }
                ?>
            </select>

            <!-- Tehsil Filter -->
            <select id="own_filterTehsil" class="form-select border-0 shadow-sm rounded-pill"
                style="width: 180px; height: 45px;"
                onchange="filterOwnerTable()"> <!-- Ye line add karein -->
                <option value="">All Tehsils</option>
            </select>

            <!-- Status Filter -->
            <!-- Status Filter -->
            <select id="own_filterStatus" class="form-select border-0 shadow-sm rounded-pill"
                style="width: 150px; height: 45px;"
                onchange="filterOwnerTable()"> <!-- Ye line add karein -->
                <option value="">All Status</option>
                <option value="active">Active</option> <!-- Value small letters mein kar dein -->
                <option value="inactive">Inactive</option>
            </select>

            <!-- Search Bar -->
            <div class="position-relative ms-auto">
                <i class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="own_companySearch" class="form-control border-0 shadow-sm ps-5 rounded-pill"
                    placeholder="Search Owner name..." style="width: 300px; height: 45px;">
            </div>

        </div>
    </div>
</div>

<button class="btn btn-primary rounded-circle shadow-lg position-fixed"
    onclick="loadContent('components/owner/add-owner.php')"
    onmouseover="this.style.transform='scale(1.1) rotate(90deg)';"
    onmouseout="this.style.transform='scale(1) rotate(0deg)';"
    style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 9999; border: none; transition: 0.3s; background: #6366f1;">
    <i class="ri-add-line fs-3"></i>
</button>

<div class="table-modern border-0 shadow-sm bg-white" style="border-radius: 20px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Owner Name</th>
                    <th>Cnic</th>
                    <th>Contact No</th>
                    <th>Districts</th>
                    <th>Tehsils</th>
                    <th>Account Title</th>
                    <th>Payment Method</th>
                    <th>Iban</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT o.*, d.district_name, t.tehsil_name 
                        FROM vehicle_owners o 
                        JOIN districts d ON o.district_id = d.district_id 
                        JOIN tehsils t ON o.tehsil_id = t.tehsil_id 
                        ORDER BY o.owner_id DESC";

                $res = $conn->query($sql);
                if ($res && $res->num_rows > 0) {
                    while ($r = $res->fetch_assoc()) {
                        $statusClass = ($r['status'] == 'active') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                ?>
                        <tr>
                            <td class="ps-4 text-muted">#<?= str_pad($r['owner_id'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div class="d-flex flex-column owner-name-cell">
                                    <span class="fw-bold text-dark"><?= $r['full_name'] ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column cnic-cell">
                                    <span class=" text-dark"></i> <?= $r['cnic'] ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column contact-cell">
                                    <span class=" text-dark"></i><?= $r['contact_number'] ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column district-cell">
                                    <span><?= $r['district_name'] ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column tehsil-cell">
                                    <span><?= $r['tehsil_name'] ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($r['account_number'])): ?>
                                    <div class="d-flex flex-column acc-title-cell">
                                        <span class="fw-semibold small text-dark"><?= $r['account_title'] ?: 'No Title' ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small italic">No Bank Details</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($r['account_number'])): ?>
                                    <div class="d-flex flex-column iban-cell">
                                        <span class="badge bg-light text-primary border mt-1" style="width: fit-content; font-size: 12px;">
                                            <?= $r['account_type'] ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small italic">No Bank Details</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($r['account_number'])): ?>
                                    <div class="d-flex flex-column status-cell">
                                        <span class=""><?= $r['account_number'] ?></span>

                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small italic">No Bank Details</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge <?= $statusClass ?> rounded-pill px-3 text-capitalize">
                                    <?= $r['status'] ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light rounded-circle border shadow-sm"
                                    onclick="loadContent('components/owner/add-owner.php?id=<?= $r['owner_id'] ?>')"
                                    title="Edit Owner">
                                    <i class="ri-pencil-line text-primary"></i>
                                </button>
                                <button class="btn btn-sm btn-light rounded-circle border shadow-sm ms-1 "
                                    onclick="deleteOwner(<?= $r['owner_id'] ?>)"
                                    title="Delete Owner">
                                    <i class="ri-delete-bin-line text-danger"></i>
                                </button>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-5 text-muted'>No owners found. Click the + button to add one.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>

</script>