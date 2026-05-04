<?php
// Database connection include karein
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

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold m-0 text-dark">Allotment History</h3>
        <p class="text-muted small m-0">View and track all funds released to managers</p>
    </div>
    <div class="col-md-6 d-flex justify-content-md-end gap-2 mt-3 mt-md-0">
        <button class="btn btn-light border shadow-sm px-4 py-2 rounded-pill d-flex align-items-center gap-2" onclick="exportToPDF()">
            <i class="ri-file-download-line text-danger"></i>
            <span class="text-dark">Export PDF</span>
        </button>

        <button class="btn btn-primary shadow-sm px-4 py-2 rounded-pill d-flex align-items-center gap-2"
            onclick="loadContent('components/expenses/manager-income.php')"
            style="background: #6366f1; border: none;">
            <i class="ri-add-line text-white"></i>
            <span class="text-white">New Allotment</span>
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
    <div class="card-body p-3">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="input-group" style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
                    <span class="input-group-text border-0 bg-transparent text-muted"><i class="ri-search-line"></i></span>
                    <input type="text" class="form-control border-0 bg-transparent py-2" placeholder="Search manager or ref #...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select border-0 bg-light py-2" style="border-radius: 12px;">
                    <option selected>All Managers</option>
                    <option>Ali Ahmed</option>
                    <option>Usman Khan</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control border-0 bg-light py-2" style="border-radius: 12px;">
            </div>
            <div class="col-md-2">
                <button class="btn btn-dark w-100 py-2" style="border-radius: 12px;">Filter</button>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 24px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4 py-3 text-secondary small fw-bold">DATE</th>
                    <th class="py-3 text-secondary small fw-bold">MANAGER</th>
                    <th class="py-3 text-secondary small fw-bold ps-4">TEHSIL</th>
                    <th class="py-3 text-secondary small fw-bold">PAYMENT MODE</th>
                    <th class="py-3 text-secondary small fw-bold">BANK DETAILS</th>
                    <th class="py-3 text-secondary small fw-bold">AMOUNT</th>
                    <th class="text-end pe-4 py-3 text-secondary small fw-bold">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Data fetch karne ki query
                $sql = "SELECT mi.*, s.staff_name, t.tehsil_name 
                            FROM manager_income mi
                            LEFT JOIN staff s ON mi.manager_id = s.staff_id
                            LEFT JOIN tehsils t ON mi.tehsil_id = t.tehsil_id 
                            ORDER BY mi.transaction_date ASC";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Payment mode ka color set karna
                        $modeColor = ($row['pay_mode'] == 'Cash') ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary';
                ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">
                                    <?php echo date('d M, Y', strtotime($row['transaction_date'])); ?>
                                </div>
                                <div class="text-muted small">
                                    <?php
                                    // Agar 'created_at' column database mein mojood hai (jo default time leta hai)
                                    if (isset($row['created_at'])) {
                                        echo date('h:i A', strtotime($row['created_at']));
                                    } else {
                                        echo "---"; // Ya koi bhi placeholder
                                    }
                                    ?>
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold text-dark">
                                    <?php echo !empty($row['staff_name']) ? $row['staff_name'] : 'Unknown Manager'; ?>

                                </div>

                            <td>
                                <span class="badge bg-light text-primary border px-3 rounded-pill" style="font-size: 11px;">
                                    <i class="ri-map-pin-2-line me-1"></i>
                                    <?php echo !empty($row['tehsil_name']) ? $row['tehsil_name'] : 'N/A'; ?>
                                </span>
                            </td>
                            </td>

                            <td>
                                <?php
                                $mode = $row['pay_mode']; // Database se mode uthaya

                                // Logic: Agar Cash hai to success (green), warna primary (blue)
                                if ($mode == 'Cash') {
                                    $badgeClass = "bg-success-subtle text-success";
                                    $icon = "ri-cash-line";
                                } else if ($mode == 'Bank') {
                                    $badgeClass = "bg-primary-subtle text-primary";
                                    $icon = "ri-bank-line";
                                } else {
                                    $badgeClass = "bg-info-subtle text-info";
                                    $icon = "ri-file-list-2-line"; // Cheque ke liye icon
                                }
                                ?>
                                <span class="badge <?php echo $badgeClass; ?> px-3 rounded-pill">
                                    <i class="<?php echo $icon; ?> me-1"></i> <?php echo $mode; ?>
                                </span>
                            </td>

                            <td>
                                <?php if ($row['pay_mode'] == 'Bank' || $row['pay_mode'] == 'cheque'): ?>
                                    <div class="text-dark small fw-bold"><?php echo $row['bank_name']; ?></div>
                                    <div class="text-muted" style="font-size: 10px;"><?php echo $row['account_no']; ?></div>
                                <?php else: ?>
                                    <span class="text-muted small">---</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fw-bold text-dark">
                                    Rs. <?php echo number_format($row['amount']); ?>
                                </span>
                            </td>

                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light rounded-circle shadow-sm"
                                    onclick="printReceipt(<?php echo $row['manager_income_id']; ?>)">
                                    <i class="ri-printer-line"></i>
                                </button>

                                <button class="btn btn-sm btn-light rounded-circle shadow-sm ms-1 text-danger delete-btn"
                                    data-id="<?php echo $row['manager_income_id']; ?>">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>

                <?php
                    }
                } else {
                    echo '<tr><td colspan="5" class="text-center py-5 text-muted">No history found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>