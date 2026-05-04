<div class="row mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold m-0 text-dark">Manager Fund Allotment</h3>
        <p class="text-muted small pt-2">Record income/cash handed over to managers</p>
    </div>
    <div class="col-md-6 d-flex justify-content-end align-items-center">
        <button class="btn btn-light border shadow-sm px-4 py-2 rounded-pill d-flex align-items-center gap-2"
            onclick="loadContent('components/expenses/income-history.php')">
            <i class="ri-history-line text-primary"></i>
            <span class="text-dark">View History</span>
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm p-4" style="border-radius: 24px;">
            <form id="managerIncomeForm">
                <div class="row g-4">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small">Select Tehsil</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light" style="border-radius: 12px 0 0 12px;">
                                <i class="ri-map-pin-2-line"></i>
                            </span>
                            <select name="tehsil_id" id="tehsilSelect" class="form-select border-0 bg-light py-2" style="border-radius: 0 12px 12px 0;" onchange="filterManagers(this.value)">
                                <option value="" selected disabled>Select Tehsil...</option>
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
                                
                                $t_sql = "SELECT * FROM tehsils ORDER BY tehsil_name ASC";
                                $t_res = $conn->query($t_sql);
                                while ($t_row = $t_res->fetch_assoc()) {
                                    echo "<option value='" . $t_row['tehsil_id'] . "'>" . $t_row['tehsil_name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>


                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small">Responsible Manager</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light" style="border-radius: 12px 0 0 12px;">
                                <i class="ri-user-settings-line"></i>
                            </span>
                            <select name="manager_id" id="managerSelect" class="form-select border-0 bg-light py-2" style="border-radius: 0 12px 12px 0;">
                                <option value="" selected disabled>Select Tehsil First...</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small">Transaction Date</label>
                        <input type="date" name="transaction_date" class="form-control border-0 bg-light py-2" style="border-radius: 12px;" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small">Amount to Allot (PKR)</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light" style="border-radius: 12px 0 0 12px;">
                                <i class="ri-money-dollar-circle-line"></i>
                            </span>
                            <input type="number" name="amount" class="form-control border-0 bg-light py-2" placeholder="e.g. 50000" style="border-radius: 0 12px 12px 0;">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-secondary small">Payment Mode</label>
                        <div class="d-flex gap-3">
                            <div class="form-check custom-radio">
                                <input class="form-check-input" type="radio" name="pay_mode" id="cash" value="Cash" checked onclick="toggleBankFields(false)">
                                <label class="form-check-label" for="cash">Cash</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input" type="radio" name="pay_mode" id="bank" value="Bank" onclick="toggleBankFields(true)">
                                <label class="form-check-label" for="bank">Bank</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input" type="radio" name="pay_mode" id="cheque" value="cheque" onclick="toggleBankFields(false)">
                                <label class="form-check-label" for="bank">Cheque</label>
                            </div>
                        </div>
                    </div>

                    <div id="bankFields" class="col-12" style="display: none;">
                        <div class="row g-3 p-3 bg-light" style="border-radius: 15px;">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control border-0 px-3" placeholder="HBL, UBL, etc." style="border-radius: 10px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Account</label>
                                <input type="text" name="account_no" class="form-control border-0 px-3" placeholder="Account no/IBAN" style="border-radius: 10px;">
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-secondary small">Notes / Purpose</label>
                        <textarea name="remarks" class="form-control border-0 bg-light p-3" rows="2" placeholder="e.g. Weekly budget for site work..." style="border-radius: 15px;"></textarea>
                    </div>

                    <div class="col-12 mt-2 text-end">
                        <button type="submit" class="btn btn-primary px-5 py-2 shadow" style="border-radius: 12px; background: #6366f1; border:none;">
                            <i class="ri-send-plane-fill me-2"></i> Release Funds
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card border-0 shadow-sm p-4" style="border-radius: 25px;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="fw-bold text-dark m-0">Recent Allotments</h6>
                <span class="badge bg-primary-subtle text-primary rounded-pill">Latest 5</span>
            </div>

            <?php
            // Join queries taake names aur tehsil nazar aayein
            $recent_sql = "SELECT mi.*, s.staff_name, t.tehsil_name 
                       FROM manager_income mi
                       JOIN staff s ON mi.manager_id = s.staff_id
                       JOIN tehsils t ON mi.tehsil_id = t.tehsil_id
                       ORDER BY mi.created_at DESC LIMIT 5";
            $recent_res = $conn->query($recent_sql);

            if ($recent_res->num_rows > 0) {
                while ($row = $recent_res->fetch_assoc()) {
                    // Icon change logic based on payment mode
                    $icon = ($row['pay_mode'] == 'Bank') ? 'ri-bank-line' : 'ri-money-dollar-circle-line';
                    $bg_class = ($row['pay_mode'] == 'Bank') ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success';

                    // Date formatting
                    $date = date('d M', strtotime($row['transaction_date']));
                    if ($row['transaction_date'] == date('Y-m-d')) {
                        $date = "Today";
                    }
            ?>
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom border-light">
                        <div class="flex-shrink-0 <?php echo $bg_class; ?> rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="<?php echo $icon; ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-0 small fw-bold text-dark">
                                <?php echo $row['staff_name']; ?>
                                <span class="text-muted fw-normal mx-1">|</span>
                                <span class="text-primary font-monospace" style="font-size: 10px;"><?php echo $row['tehsil_name']; ?></span>
                            </p>
                            <p class="mb-0 text-muted small">
                                Rs. <?php echo number_format($row['amount']); ?>
                                <span class="badge bg-light text-dark ms-1" style="font-size: 9px;"><?php echo $row['pay_mode']; ?></span>
                            </p>
                        </div>
                        <div class="text-end text-muted" style="font-size: 10px;"><?php echo $date; ?></div>
                    </div>
            <?php
                }
            } else {
                echo "<p class='text-muted small text-center'>No records found.</p>";
            }
            ?>

            <button class="btn btn-light w-100 btn-sm rounded-pill text-muted mt-2" onclick="loadContent('components/expenses/income-history.php')">View All Reports</button>
        </div>
    </div>
</div>