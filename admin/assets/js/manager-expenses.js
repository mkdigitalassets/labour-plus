  if (typeof ManagerPayment !== 'undefined') {
        ManagerPayment.init();
}

$(document).ready(function() {
    console.log("Expense System Initialized");

    // 1. TEHSIL LOAD
    $(document).on('change', '#district_id', function() {
        let dist_id = $(this).val();
        if(dist_id) {
            $.post('backend/expenses/manager-expense-process.php', { action: 'fetch_tehsils', district_id: dist_id }, function(data) {
                $('#tehsil_id').html(data);
                $('#manager_list').html('<option value="">-- Select Tehsil First --</option>');
            });
        }
    });

    // 2. MANAGER LOAD
    $(document).on('change', '#tehsil_id', function() {
        let teh_id = $(this).val();
        if(teh_id != "") {
            $.post('backend/expenses/manager-expense-process.php', { action: 'fetch_managers', tehsil_id: teh_id }, function(data) {
                $('#manager_list').html(data);
            });
        }
    });

    // 3. CATEGORY & SUB-CATEGORY
    $(document).on('change', '#type_id', function() {
        let type_id = $(this).val();
        $.post('backend/expenses/manager-expense-process.php', { action: 'fetch_categories', type_id: type_id }, function(data) {
            $('#expense_category').html(data);
            $('#sub_category').html('<option value="">-- Select Category First --</option>');
        });
    });

    $(document).on('change', '#expense_category', function() {
        let cat_id = $(this).val();
        $.post('backend/expenses/manager-expense-process.php', { action: 'fetch_subcategories', category_id: cat_id }, function(data) {
            $('#sub_category').html(data);
        });
    });

    // 4. REGISTRATION / ITEM FETCH
    $(document).on('change', '#sub_category', function() {
        let sub_id = $(this).val();
        if(sub_id) {
            $.post('backend/expenses/manager-expense-process.php', { action: 'fetch_reg_numbers', sub_id: sub_id }, function(data) {
                $('#registration_container').html(data);
            });
        }
    });

    // 5. PAYMENT FIELDS
    $(document).on('change', '#payment_method', function() {
        renderPaymentFields($(this).val());
    });
    renderPaymentFields('Cash');

    // 6. FORM SUBMISSION (Save & Update)
    $(document).on('submit', '#expenseForm', function(e) {
        e.preventDefault();
        
        if($('#manager_list').val() === "" || $('#manager_list').val() === null) {
            Swal.fire('Error!', 'Please select a Manager first', 'error');
            return false;
        }

        let formData = new FormData(this);
        formData.append('action', 'save_manager_expense');

        let btn = $('#submitBtn');
        btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i> Saving...');

        $.ajax({
            url: 'backend/expenses/manager-expense-process.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if(response.trim() === "success") {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Expense record has been saved.',
                        icon: 'success',
                        confirmButtonColor: '#6366f1'
                    }).then(() => {
                        loadContent('components/expenses/expense-history.php');
                    });
                } else {
                    Swal.fire('Failed!', 'Server Response: ' + response, 'error');
                    btn.prop('disabled', false).html('<i class="ri-save-line"></i> Save Expense');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Could not connect to the server.', 'error');
                btn.prop('disabled', false).html('Save Expense');
            }
        });
    });
});

// --- GLOBAL FUNCTIONS ---

function renderPaymentFields(method, data = {}) {
    let fields = '';
    const container = $('#dynamic_payment_fields');
    if (method === 'Cash') {
        fields = `<div class="col-md-4"><label class="small fw-bold">RECEIVER NAME</label><input type="text" name="pay_owner_name" value="${data.pay_owner_name || ''}" class="form-control" required></div>
                  <div class="col-md-4"><label class="small fw-bold">CNIC</label><input type="text" name="pay_cnic" value="${data.pay_cnic || ''}" class="form-control"></div>
                  <div class="col-md-4"><label class="small fw-bold">CONTACT</label><input type="text" name="pay_contact" value="${data.pay_contact || ''}" class="form-control"></div>`;
    } else {
        fields = `<div class="col-md-3"><label class="small fw-bold">BANK/APP</label><input type="text" name="bank_name" value="${data.bank_name || ''}" class="form-control" required></div>
                  <div class="col-md-3"><label class="small fw-bold">HOLDER NAME</label><input type="text" name="pay_owner_name" value="${data.pay_owner_name || ''}" class="form-control" required></div>
                  <div class="col-md-3"><label class="small fw-bold">ACC/IBAN</label><input type="text" name="pay_acc_no" value="${data.pay_acc_no || ''}" class="form-control" required></div>
                  <div class="col-md-3"><label class="small fw-bold">CONTACT</label><input type="text" name="pay_contact" value="${data.pay_contact || ''}" class="form-control"></div>`;
    }
    container.hide().html(fields).fadeIn(300);
}

// DELETE FUNCTION WITH SWEETALERT
function deleteExpense(expenseId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('backend/expenses/manager-expense-process.php', { action: 'delete_expense', expense_id: expenseId }, function(res) {
                if (res.trim() === 'success') {
                    Swal.fire('Deleted!', 'Record has been deleted.', 'success');
                    loadContent('components/expenses/expense-history.php');
                } else {
                    Swal.fire('Error!', 'Could not delete: ' + res, 'error');
                }
            });
        }
    });
}

// EDIT PAGE FUNCTION
function goToEditPage(data) {
    loadContent('components/expenses/manager-expense.php', null, function() {
        setTimeout(function() {
            // Fill Form Fields
            $('#expense_id').val(data.expense_id);
            $('#amount').val(data.amount);
            $('#expense_desc').val(data.description);
            $('#expense_date').val(data.expense_date);
            
            // Payment method and fields
            $('#payment_method').val(data.payment_method).trigger('change');
            renderPaymentFields(data.payment_method, {
                pay_owner_name: data.pay_owner_name,
                pay_acc_no: data.pay_acc_no,
                pay_contact: data.pay_contact,
                pay_cnic: data.pay_cnic,
                bank_name: data.bank_name
            });

            // Cascading Drops
            $('#district_id').val(data.district_id).trigger('change');
            
            // Wait for tehsils to load, then select and trigger managers
            setTimeout(() => {
                $('#tehsil_id').val(data.tehsil_id).trigger('change');
                setTimeout(() => { 
                    $('#manager_list').val(data.manager_id); 
                }, 500);
            }, 500);

            // Category Cascading
            $('#type_id').val(data.type_id).trigger('change');
            setTimeout(() => {
                $('#expense_category').val(data.category_id).trigger('change');
                setTimeout(() => {
                    $('#sub_category').val(data.sub_id).trigger('change');
                }, 500);
            }, 500);

            // UI Changes
            $('#submitBtn').html('<i class="ri-edit-line"></i> Update Expense').removeClass('btn-primary').addClass('btn-success');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }, 600);
    });
}

function updateExpenseStatus(id, status) {
    if (confirm('Are you sure you want to ' + status + ' this expense?')) {
        // Path ko 'backend/expenses/...' pr set karein
        fetch('backend/expenses/update_expense_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id + '&status=' + status
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes('success')) {
                alert('Expense ' + status + ' successfully!');
                // Path components se start ho raha hai index.php ke mutabiq
                loadContent('components/expenses/pending-expenses.php'); 
            } else {
                alert('Error: ' + data);
            }
        });
    }
}
// Inhe table ke buttons ke 'onclick' event mein call karein
function approveExpense(id) { updateExpenseStatus(id, 'Approved'); }
function rejectExpense(id) { updateExpenseStatus(id, 'Rejected'); }