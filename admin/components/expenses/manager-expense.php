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



<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h4 class="fw-bold m-0 text-dark">Add Manager Expense</h4>

        <p class="text-muted small m-0 pt-1">Record daily expenses with bill and transaction proofs.</p>

    </div>

    <button class="btn btn-light border rounded-pill px-4 shadow-sm" onclick="loadContent('components/expenses/expense-history.php')">

        <i class="ri-history-line me-1"></i> View History

    </button>

</div>



<div class="card border-0 shadow-sm" style="border-radius: 20px;">

    <div class="card-body p-4 p-md-5">

        <form id="expenseForm" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="action" value="save_manager_expense">

            <input type="hidden" name="expense_id" id="expense_id">



            <div class="row g-4">

                <div class="col-md-4">

                    <label class="form-label small fw-bold text-muted text-uppercase">Select District</label>

                    <select name="district_id" id="district_id" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">

                        <option value="">-- Choose District --</option>

                        <?php

                        $d_query = mysqli_query($conn, "SELECT * FROM districts");

                        while ($d = mysqli_fetch_assoc($d_query)) {

                            echo "<option value='" . $d['district_id'] . "'>" . $d['district_name'] . "</option>";
                        }

                        ?>

                    </select>

                </div>

                <div class="col-md-4">

                    <label class="form-label small fw-bold text-muted text-uppercase">Select Tehsil</label>

                    <select name="tehsil_id" id="tehsil_id" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">

                        <option value="">-- Select District First --</option>

                    </select>

                </div>

                <div class="col-md-4">

                    <label class="form-label small fw-bold text-muted text-uppercase">Manager Name</label>

                    <select name="manager_id" id="manager_list" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">

                        <option value="">-- Select Tehsil First --</option>

                    </select>

                </div>



                <div class="col-md-4">

                    <label class="form-label small fw-bold text-muted text-uppercase">Category Type</label>

                    <select name="type_id" id="type_id" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">

                        <option value="">-- Select Type --</option>

                        <?php

                        $t_query = mysqli_query($conn, "SELECT * FROM expense_category_types WHERE status='Active'");

                        while ($t = mysqli_fetch_assoc($t_query)) {

                            echo "<option value='" . $t['type_id'] . "'>" . $t['type_name'] . "</option>";
                        }

                        ?>

                    </select>

                </div>

                <div class="col-md-4">

                    <label class="form-label small fw-bold text-muted text-uppercase">Category</label>

                    <select name="category_id" id="expense_category" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">

                        <option value="">-- Select Type First --</option>

                    </select>

                </div>

                <div class="col-md-4">

                    <label class="form-label small fw-bold text-muted text-uppercase">Sub Category</label>

                    <select name="sub_id" id="sub_category" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">

                        <option value="">-- Select Category First --</option>

                    </select>

                </div>



                <div class="col-md-12" id="registration_container">

                </div>



                <div class="col-md-4">

                    <label class="form-label small fw-bold text-muted text-uppercase">Amount (Rs.)</label>

                    <input type="number" name="amount" id="amount" class="form-control border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;" placeholder="0.00" required>

                </div>

                <div class="col-md-4">

                    <label class="form-label small fw-bold text-muted text-uppercase">Expense Date</label>

                    <input type="date" name="expense_date" id="expense_date" value="<?php echo date('Y-m-d'); ?>" class="form-control border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;">

                </div>

                <div class="col-md-4">

                    <label class="form-label small fw-bold text-muted text-uppercase">Payment Method</label>

                    <select name="payment_method" id="payment_method" class="form-select border-0 shadow-sm bg-light" style="height: 50px; border-radius: 12px;" onchange="renderPaymentFields(this.value)">

                        <option value="Cash">-- Select Payment Method --</option>
                        <option value="Cash">Cash</option>

                        <option value="Mobile Acc">Mobile Account</option>

                        <option value="Bank Account">Bank Account</option>

                    </select>

                </div>



                <div class="col-md-12">

                    <div id="dynamic_payment_fields" class="row g-3 p-3 mb-2" style="background: #f8f9fa; border-radius: 15px; border: 1px dashed #dee2e6;">

                    </div>

                </div>



                <div class="col-md-12">

                    <label class="form-label small fw-bold text-muted text-uppercase">Description</label>

                    <textarea name="description" id="expense_desc" class="form-control border-0 shadow-sm bg-light" style="border-radius: 12px;" rows="2" placeholder="Expense details..."></textarea>

                </div>



                <div class="col-md-6">

                    <label class="form-label small fw-bold text-muted text-uppercase">Bill Attachment</label>

                    <input type="file" name="bill_attachment" id="bill_attachment" class="form-control border-0 shadow-sm bg-light" style="padding-top: 12px; height: 50px; border-radius: 12px;" accept="image/*">

                    <div id="old_bill_preview" class="mt-2"></div>

                </div>

                <div class="col-md-6">

                    <label class="form-label small fw-bold text-muted text-uppercase">Transaction Proof</label>

                    <input type="file" name="transaction_attachment" id="transaction_attachment" class="form-control border-0 shadow-sm bg-light" style="padding-top: 12px; height: 50px; border-radius: 12px;" accept="image/*">

                    <div id="old_trans_preview" class="mt-2"></div>

                </div>



                <div class="col-12 text-end pt-3">

                    <hr class="opacity-5">

                    <button type="submit" id="submitBtn" class="btn btn-primary px-5 py-2 rounded-pill shadow" style="background: #6366f1; border:none;">

                        <i class="ri-save-line me-1"></i> Save Expense

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>