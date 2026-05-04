<?php
include('../../backend/config.php');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark">Manager Payments</h4>
        <p class="text-muted small m-0 pt-1">Overview of all payments, advances, and bank transfers to managers.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm" 
        onclick="loadContent('components/payments/manager-entry.php')">
        <i class="ri-add-line me-1"></i> Add New Payment
    </button>
</div>

<!-- Professional Table Card -->
<div class="card border-0 shadow-sm" style="border-radius: 20px;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="managerPaymentTable" class="table table-hover align-middle" style="width:100%">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 text-uppercase small fw-bold ps-3">Manager & Region</th>
                        <th class="border-0 text-uppercase small fw-bold">Date</th>
                        <th class="border-0 text-uppercase small fw-bold">Amount</th>
                        <th class="border-0 text-uppercase small fw-bold">Method</th>
                        <th class="border-0 text-uppercase small fw-bold">Proof</th>
                        <th class="border-0 text-uppercase small fw-bold text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT p.*, st.staff_name, t.tehsil_name 
                            FROM manager_payments p 
                            JOIN staff st ON p.manager_id = st.staff_id 
                            JOIN tehsils t ON st.tehsil_id = t.tehsil_id 
                            ORDER BY p.payment_date DESC";
                    $res = $conn->query($sql);
                    while ($row = $res->fetch_assoc()):
                        $method_class = ($row['payment_method'] == 'Cash') ? 'bg-soft-success text-success' : 'bg-soft-primary text-primary';
                    ?>
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold text-dark"><?= $row['staff_name'] ?></div>
                            <div class="text-muted small"><?= $row['tehsil_name'] ?></div>
                        </td>
                        <td><span class="text-secondary"><?= date('d M, Y', strtotime($row['payment_date'])) ?></span></td>
                        <td><span class="fw-bold text-dark">Rs. <?= number_format($row['amount']) ?></span></td>
                        <td>
                            <span class="badge rounded-pill <?= $method_class ?> px-3">
                                <?= $row['payment_method'] ?>
                            </span>
                        </td>
                        <td>
                            <?php if(!empty($row['payment_proof'])): ?>
                                <a href="uploads/payments/<?= $row['payment_proof'] ?>" target="_blank" class="btn btn-sm btn-light border-0 shadow-sm rounded-circle">
                                    <i class="ri-image-line text-primary"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">No Proof</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-pill border-0" type="button" data-bs-toggle="dropdown">
                                    <i class="ri-more-2-fill"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm" style="border-radius: 12px;">
                                    <li>
                                        <a class="dropdown-item py-2" href="javascript:void(0)" 
                                           onclick="loadContent('components/payments/manager-entry.php?id=<?= $row['payment_id'] ?>')">
                                            <i class="ri-edit-line me-2 text-primary"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2 text-danger" href="javascript:void(0)" 
                                           onclick="deleteManagerPayment(<?= $row['payment_id'] ?>)">
                                            <i class="ri-delete-bin-line me-2"></i> Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>

</script>