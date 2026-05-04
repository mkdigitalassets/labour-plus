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
<div class="container-fluid mt-4">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold m-0 text-dark">Income Transactions</h5>
                <small class="text-muted">Track all company revenue and receipts</small>
            </div>
            <div class="d-flex gap-2">
                <button onclick="loadContent('components/income/income-entry.php')" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold animate-btn">
                    <i class="fas fa-plus-circle me-2"></i> Add New Entry
                </button>
                <button class="btn btn-light btn-sm rounded-pill border" onclick="IncomeModule.loadList()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle custom-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Location</th>
                            <th>Amount</th>
                            <th>Payment Info</th>
                            <th>Proof</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="incomeTableBody">
                        <!-- Data will be loaded here via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Proof Preview -->
<div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Payment Proof</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="proofPreviewImg" src="" class="img-fluid rounded-3 shadow-sm mb-3" alt="No Image">
                <div id="proofDetails" class="small text-muted"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-table thead th { font-size: 11px; text-uppercase; letter-spacing: 0.5px; color: #6c757d; border-bottom: 2px solid #f8f9fa; }
    .custom-table tbody td { font-size: 14px; padding: 12px 8px; border-bottom: 1px solid #f8f9fa; }
    .amount-text { font-family: 'Inter', sans-serif; font-weight: 700; color: #2ecc71; }
    .badge-method { font-size: 10px; padding: 4px 8px; border-radius: 50px; background: #eef2f7; color: #495057; font-weight: 600; }
</style>