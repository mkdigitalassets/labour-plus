/**
 * Income Module - Optimized for AJAX Content Loading
 */
const IncomeModule = {
    init: function() {
        console.log("Income Module Initialized...");
        
        // Initialize Select2 if exists
        if ($('.select2-init').length) {
            $('.select2-init').select2({ 
                width: '100%',
                dropdownParent: $('#incomeForm').length ? $('#incomeForm') : null
            });
        }

        // Auto-load list if the table exists on current view
        if ($('#incomeTableBody').length > 0) {
            this.loadList();
        }

        // Global Form Submission
        $(document).off('submit', '#incomeForm').on('submit', '#incomeForm', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            IncomeModule.save(this);
        });
    },

    fetchTehsils: function(districtId) {
        if (!districtId) {
            $('#income_tehsil').html('<option value="">Select Tehsil</option>').trigger('change');
            return;
        }
        $('#income_tehsil').prop('disabled', true);
        $.post('backend/income/income-process.php', { 
            action: 'get_tehsils', 
            district_id: districtId 
        }, function(data) {
            $('#income_tehsil').html('<option value="">Select Tehsil</option>' + data);
            $('#income_tehsil').prop('disabled', false).trigger('change'); 
        });
    },

    toggleUI: function(method) {
        let html = '';
        if(method === 'Cash') {
            html = `<div class="col-md-4"><input type="text" name="receiver_name" class="form-control form-control-sm rounded-pill" placeholder="Receiver Name" required></div>
                    <div class="col-md-4"><input type="text" name="contact_no" class="form-control form-control-sm rounded-pill" placeholder="Contact Num" required></div>
                    <div class="col-md-4"><input type="text" name="cnic" class="form-control form-control-sm rounded-pill" placeholder="CNIC"></div>`;
        } else {
            let placeholder = (method === 'Bank Account') ? 'Bank / Acc No' : 'Mobile Account Detail';
            html = `<div class="col-md-4"><input type="text" name="account_details" class="form-control form-control-sm rounded-pill" placeholder="${placeholder}" required></div>
                    <div class="col-md-4"><input type="text" name="holder_name" class="form-control form-control-sm rounded-pill" placeholder="Holder Name" required></div>
                    <div class="col-md-4"><input type="text" name="contact_no" class="form-control form-control-sm rounded-pill" placeholder="Contact Num" required></div>`;
        }
        $('#dynamic_fields').hide().html(html).fadeIn();
    },

    save: function(form) {
        let btn = $(form).find('.btn-save');
        let formData = new FormData(form);
        formData.append('action', 'save_income');

        btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

        $.ajax({
            url: 'backend/income/income-process.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
                if(res.trim() === 'success') {
                    Swal.fire({ icon: 'success', title: 'Saved!', timer: 1500, showConfirmButton: false }).then(() => {
                        if (typeof loadContent === "function") {
                            loadContent('components/income/income-list.php'); 
                        } else {
                            window.location.href = "index.php?page=income-list";
                        }
                    });
                } else {
                    Swal.fire('Error', res, 'error');
                }
            }
        });
    },

    loadList: function() {
        $('#incomeTableBody').html('<tr><td colspan="6" class="text-center"><div class="spinner-border spinner-border-sm text-success"></div> Loading...</td></tr>');
        $.post('backend/income/income-process.php', { action: 'fetch_income_list' }, function(res) {
            $('#incomeTableBody').html(res);
        });
    },

    delete: function(id) {
        Swal.fire({ title: 'Are you sure?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, Delete' }).then((result) => {
            if (result.isConfirmed) {
                $.post('backend/income/income-process.php', { action: 'delete_income', id: id }, function(res) {
                    if(res.trim() === 'success') {
                        IncomeModule.loadList();
                        Swal.fire('Deleted!', '', 'success');
                    }
                });
            }
        });
    },

    showProof: function(imgUrl, method) {
        $('#proofPreviewImg').attr('src', imgUrl);
        $('#proofDetails').text('Payment Method: ' + method);
        let modal = new bootstrap.Modal(document.getElementById('proofModal'));
        modal.show();
    }
};

// Initialize on page load
$(document).ready(function() {
    IncomeModule.init();
});
$(document).ready(function() {
        if (typeof IncomeModule !== 'undefined') {
            console.log("Auto-initializing Income List...");
            IncomeModule.init(); // Ye function check karega aur loadList call kar dega
        } else {
            console.error("IncomeModule not found! Make sure income.js is included in index.php");
        }
});