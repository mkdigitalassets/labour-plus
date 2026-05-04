<?php
if (!isset($conn)) {
    // Agar $conn pehle se nahi bana (yani index.php se load nahi ho raha), to include karein
    // Hum file_exists check kar ke path set karte hain
    if (file_exists('backend/config.php')) {
        include('backend/config.php'); // Jab index.php se load ho
    } else {
        include('../../backend/config.php'); // Jab dashboard.php direct chale
    }
} // Path sahi rakhiyega
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-0">
                        <i class="ri-notification-3-line me-2"></i> Pending Expense Approvals
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="pendingExpensesTable">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Manager</th>
                            <th>Location</th>
                            <th>Category Hierarchy</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Hierarchy ke sath data fetch krne ki query
                            $query = "SELECT e.*, s.staff_name as manager_name, d.district_name, t.tehsil_name, 
                                  ct.type_name, c.category_name, sc.sub_name
                                  FROM manager_expenses e
                                  JOIN staff s ON e.manager_id = s.staff_id
                                  JOIN districts d ON e.district_id = d.district_id
                                  JOIN tehsils t ON e.tehsil_id = t.tehsil_id
                                  JOIN expense_category_types ct ON e.type_id = ct.type_id
                                  JOIN expense_categories c ON e.category_id = c.category_id
                                  LEFT JOIN expense_sub_categories sc ON e.sub_id = sc.sub_id
                                  WHERE e.status = 'Pending'
                                  ORDER BY e.expense_date DESC";
                        
                        $result = mysqli_query($conn, $query);
                        
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>
                                    <td>".date('d-M-Y', strtotime($row['expense_date']))."</td>
                                    <td><strong>{$row['manager_name']}</strong></td>
                                    <td><small>{$row['district_name']} > {$row['tehsil_name']}</small></td>
                                    <td>
                                        <span class='badge bg-primary-subtle text-primary'>{$row['type_name']}</span><br>
                                        <small class='text-muted'>{$row['category_name']} " . ($row['sub_name'] ? "> ".$row['sub_name'] : "") . "</small>
                                    </td>
                                    <td class='fw-bold text-danger'>RS ".number_format($row['amount'], 2)."</td>
                                    <td>
                                        <button class='btn btn-sm btn-success' onclick='approveExpense({$row['expense_id']})'><i class='ri-check-line'></i></button>
                                        <button class='btn btn-sm btn-danger' onclick='rejectExpense({$row['expense_id']})'><i class='ri-close-line'></i></button>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-4'>No pending requests found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>