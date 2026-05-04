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
        <h4 class="fw-bold m-0 text-dark">Expense History</h4>
        <p class="text-muted small m-0 pt-1">Track all manager expenses, descriptions, and bills.</p>
    </div>
    <button class="btn btn-primary rounded-circle shadow-lg position-fixed"
        onclick="loadContent('components/expenses/manager-expense.php')"
        onmouseover="this.style.transform='scale(1.1) rotate(90deg)';"
        onmouseout="this.style.transform='scale(1) rotate(0deg)';"
        style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 9999; border: none; transition: 0.3s; background: #6366f1;">
        <i class="ri-add-line fs-3"></i>
    </button>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-merge">
                    <span class="input-group-text bg-light border-0"><i class="ri-search-line text-muted"></i></span>
                    <input type="text" id="expenseSearch" class="form-control bg-light border-0"
                        placeholder="Search by Manager, Tehsil, or Category..." style="border-radius: 0 10px 10px 0;">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="expenseTable">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th class="ps-4 py-3 border-0 text-uppercase small fw-bold text-muted">Date</th>
                        <th class="py-3 border-0 text-uppercase small fw-bold text-muted">Manager / Location</th>
                        <th class="py-3 border-0 text-uppercase small fw-bold text-muted">Category Hierarchy</th>
                        <th class="py-3 border-0 text-uppercase small fw-bold text-muted">Amount</th>
                        <th class="py-3 border-0 text-uppercase small fw-bold text-muted">Payment Info</th>
                        <th class="py-3 border-0 text-uppercase small fw-bold text-muted">Proofs</th>
                        <th class="pe-4 py-3 border-0 text-uppercase small fw-bold text-muted text-end">Action</th>
                    </tr>
                </thead>
                <tbody id="expenseTableBody">
                    <?php
                    // Query updated to fetch staff_name properly
                    $query = mysqli_query($conn, "SELECT e.*, t.tehsil_name, s.staff_name, 
                                    ct.type_name, c.category_name, sc.sub_name 
                                    FROM manager_expenses e
                                    JOIN tehsils t ON e.tehsil_id = t.tehsil_id
                                    JOIN staff s ON e.manager_id = s.staff_id
                                    JOIN expense_category_types ct ON e.type_id = ct.type_id
                                    JOIN expense_categories c ON e.category_id = c.category_id
                                    LEFT JOIN expense_sub_categories sc ON e.sub_id = sc.sub_id
                                    WHERE e.status = 'Approved' 
                                    ORDER BY e.expense_id DESC");

                    if (mysqli_num_rows($query) > 0) {
                        while ($row = mysqli_fetch_assoc($query)) {
                            $bill_path = !empty($row['bill_attachment']) ? 'uploads/expenses/' . $row['bill_attachment'] : '';
                            $proof_path = !empty($row['transaction_attachment']) ? 'uploads/expenses/' . $row['transaction_attachment'] : '';
                    ?>
                            <tr>
                                <td class="ps-4 small text-muted"><?php echo date('d M, Y', strtotime($row['expense_date'])); ?></td>
                                <td>
                                    <!-- Displaying Manager Name clearly -->
                                    <div class="d-flex align-items-center">
                                        <div class="bg-soft-primary rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                            <i class="ri-user-fill"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['staff_name']); ?></div>
                                            <div class="text-muted small" style="font-size: 11px;"><i class="ri-map-pin-line"></i> <?php echo htmlspecialchars($row['tehsil_name']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-bold text-primary"><?php echo htmlspecialchars($row['type_name']); ?></div>
                                    <div class="small text-dark">> <?php echo htmlspecialchars($row['category_name']); ?></div>
                                    <?php if ($row['sub_name']): ?>
                                        <div class="text-muted italic" style="font-size: 11px;">>> <?php echo htmlspecialchars($row['sub_name']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-dark">Rs. <?php echo number_format($row['amount'], 2); ?></td>
                                <td>
                                    <span class="badge rounded-pill px-2 py-1 <?php echo ($row['payment_method'] == 'Cash') ? 'bg-soft-success text-success' : 'bg-soft-primary text-primary'; ?> mb-1">
                                        <?php echo $row['payment_method']; ?>
                                    </span>
                                    <div class="small text-muted" style="font-size: 10px;">
                                        <strong><?php echo htmlspecialchars($row['pay_owner_name']); ?></strong><br>
                                        <?php echo htmlspecialchars($row['pay_acc_no']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if ($bill_path): ?>
                                            <a href="<?php echo $bill_path; ?>" target="_blank" class="btn btn-xs btn-light border p-1" title="View Bill">
                                                <i class="ri-file-list-3-line text-primary"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($proof_path): ?>
                                            <a href="<?php echo $proof_path; ?>" target="_blank" class="btn btn-xs btn-light border p-1" title="View Proof">
                                                <i class="ri-screenshot-line text-success"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                        <button class="btn btn-sm btn-white text-primary border-end"
                                            onclick='goToEditPage(<?php echo json_encode($row); ?>)' title="Edit Record">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button class="btn btn-sm btn-white text-danger"
                                            onclick="deleteExpense(<?php echo $row['expense_id']; ?>)" title="Delete Record">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="7" class="text-center py-5 text-muted">No expense records found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Real-time Search Logic
    $(document).ready(function() {
        $("#expenseSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#expenseTableBody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>

<style>
    .bg-soft-primary {
        background-color: #e0e9ff;
        color: #4e73df;
    }

    .bg-soft-success {
        background-color: #e8fadf;
        color: #2dce89;
    }

    .bg-soft-danger {
        background-color: #ffe5e5;
        color: #f5365c;
    }

    .btn-xs {
        padding: 0.1rem 0.3rem;
        font-size: 0.75rem;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fe;
        transition: 0.2s;
        cursor: default;
    }

    .table thead th {
        font-size: 11px;
        letter-spacing: 0.5px;
    }

    .italic {
        font-style: italic;
    }

    .btn-white {
        background: #fff;
        border: 1px solid #eee;
    }

    .btn-white:hover {
        background: #f8f9fa;
    }
</style>