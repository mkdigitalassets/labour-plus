$(document).off('change', '#payment_method').on('change', '#payment_method', function() {
    if($(this).val() === 'Bank Transfer') {
        $('#bank_details_div').removeClass('d-none');
    } else {
        $('#bank_details_div').addClass('d-none');
        $('input[name="bank_name"], input[name="account_info"]').val('');
    }
});
$(document).ready(function () {
    console.log("Manager Entry: Script Started");

    const form = $('#managerPaymentForm');
    const isEdit = form.data('edit-mode'); 

   function loadEditData() {
    const form = $('#managerPaymentForm');
    const isEdit = form.data('edit-mode'); 

    if (isEdit === true || isEdit === "true") {
        const distId = form.data('dist');
        const tehId  = form.data('teh');
        const mgrId  = form.data('mgr');

        console.log("Edit Mode Active. District:", distId);
        
        if (distId) {
            // District dropdown select karein
            $('#salary_district').val(distId);

            // Step 2: Tehsils fetch karein
            $.post('backend/salary/salary-process.php', { 
                action: 'fetch_tehsils', 
                district_id: distId 
            }, function(tData) {
                $('#salary_tehsil').html(tData);
                
                // Tehsil fetch hone ke baad value set karein
                if (tehId) {
                    setTimeout(() => {
                        $('#salary_tehsil').val(tehId);
                        
                        // Step 3: Manager fetch karein
                        $.post('backend/salary/salary-process.php', { 
                            action: 'fetch_staff_filtered', 
                            tehsil_id: tehId, 
                            role: 'Manager' 
                        }, function(sData) {
                            $('#staff_name_list').html(sData);
                            if (mgrId) {
                                setTimeout(() => {
                                    $('#staff_name_list').val(mgrId);
                                }, 100);
                            }
                        });
                    }, 100);
                }
            });
        }
    }
}

    // Load data after a short delay for dynamic content
    setTimeout(loadEditData, 300);

    // --- Manual Change Events ---
    $(document).off('change', '#salary_district').on('change', '#salary_district', function() {
        let dId = $(this).val();
        if(dId) {
            $.post('backend/salary/salary-process.php', { action: 'fetch_tehsils', district_id: dId }, function(res) {
                $('#salary_tehsil').html(res);
                $('#staff_name_list').html('<option value="">-- Select Name --</option>');
            });
        }
    });

    $(document).off('change', '#salary_tehsil').on('change', '#salary_tehsil', function() {
        let tId = $(this).val();
        if(tId) {
            $.post('backend/salary/salary-process.php', { action: 'fetch_staff_filtered', tehsil_id: tId, role: 'Manager' }, function(res) {
                $('#staff_name_list').html(res);
            });
        }
    });

    // --- Form Submission ---
    $(document).off('submit', '#managerPaymentForm').on('submit', '#managerPaymentForm', function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        let pId = $('input[name="payment_id"]').val();
        
        formData.append('action', (pId && pId !== "") ? 'update_manager_payment' : 'save_manager_payment');

        $.ajax({
            url: 'backend/payments/manager-process.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                if (res.trim() === 'success') {
                    Swal.fire({ icon: 'success', title: 'Payment Saved!', showConfirmButton: false, timer: 1500 });
                    loadContent('components/payments/manager-list.php');
                } else {
                    Swal.fire('Error', res, 'error');
                }
            }
        });
    });
    
});

// Delete Function (Global)
function deleteManagerPayment(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This record will be deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('backend/payments/manager-process.php', { action: 'delete_manager_payment', payment_id: id }, function(res) {
                if (res.trim() === 'success') {
                    Swal.fire('Deleted!', 'Record removed.', 'success');
                    loadContent('components/payments/manager-list.php');
                } else {
                    Swal.fire('Error', res, 'error');
                }
            });
        }
    });
}