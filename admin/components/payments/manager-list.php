<div class="container-fluid mt-4">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold m-0">Manager Payments</h5>
            <button onclick="loadContent('components/payments/manager-entry.php')" class="btn btn-success btn-sm rounded-pill">
                Add New
            </button>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" id="mgr_search" class="form-control form-control-sm" placeholder="Search Manager...">
                </div>
                <div class="col-md-3">
                    <select id="mgr_filterMethod" class="form-select form-select-sm">
                        <option value="">All Methods</option>
                        <option>Cash</option>
                        <option>Bank Account</option>
                        <option>Mobile Account</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Manager</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Purpose</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="managerPaymentTableBody">
                        <!-- AJAX content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>fetchManagerPayments();</script>