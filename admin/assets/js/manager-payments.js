$(document).ready(function () {
    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });

    // Save/Update Manager Payment
    $(document).off('submit', '#managerPaymentForm').on('submit', '#managerPaymentForm', function (e) {
        e.preventDefault();
        let form = $(this);
        let pId = form.find('input[name="payment_id"]').val();
        let action = (pId && pId !== "") ? 'update_manager_payment' : 'save_manager_payment';

        $.post('backend/payments/manager-process.php', form.serialize() + '&action=' + action, function (res) {
            if (res.trim() === 'success') {
                Toast.fire({ icon: 'success', title: 'Payment Saved!' });
                loadContent('components/payments/manager-list.php');
            } else {
                Swal.fire('Error', res, 'error');
            }
        });
    });

    // Reuse Salary Dropdown Logic for Manager
    $(document).on('change', '#salary_district', function () {
        $.post('backend/salary/salary-process.php', { action: 'fetch_tehsils', district_id: $(this).val() }, function (data) {
            $('#salary_tehsil').html(data);
        });
    });

    $(document).on('change', '#salary_tehsil', function () {
        $.post('backend/salary/salary-process.php', { action: 'fetch_staff_filtered', tehsil_id: $(this).val(), role: 'Manager' }, function (data) {
            $('#staff_name_list').html(data);
        });
    });
});