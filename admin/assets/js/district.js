$(document).ready(function () {
    // 1. Toast Notification Configuration
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // 2. INSERT & UPDATE LOGIC
    $(document).off('submit', '#districtForm').on('submit', '#districtForm', function (e) {
        e.preventDefault();

        let submitBtn = $(this).find('button[type="submit"]');
        let originalBtnHtml = submitBtn.html();

        // Loader dikhayein
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

        $.ajax({
            url: 'backend/district/district-process.php',
            type: 'POST',
            data: $(this).serialize() + '&action=save_district',
            success: function (response) {
                submitBtn.prop('disabled', false).html(originalBtnHtml);
                var res = response.trim();

                if (res === 'success' || res === 'updated') {
                    // Success Message
                    Toast.fire({
                        icon: 'success',
                        title: res === 'success' ? 'District added successfully!' : 'District updated successfully!'
                    });

                    setTimeout(() => {
                        loadContent('components/district/district.php');
                    }, 1500);

                } else if (res === 'exists') {
                    // --- PROFESSIONAL DUPLICATE ALERT (Matching Staff/Tehsil Design) ---
                    Swal.fire({
                        icon: 'warning',
                        title: 'Already Exists!',
                        text: 'This District is Already Exist! So Choose Another District Name',
                        confirmButtonColor: '#6366f1'
                    });

                } else {
                    // General Database Error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response
                    });
                }
            },
            error: function () {
                submitBtn.prop('disabled', false).html(originalBtnHtml);
                Swal.fire('Error', 'Network connection failed!', 'error');
            }
        });
    });

    // 3. DELETE LOGIC
    window.deleteDistrict = function (id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This district will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6366f1',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Toast.fire({ icon: 'info', title: 'Deleting...', timer: 800 });

                $.ajax({
                    url: 'backend/district/district-process.php',
                    type: 'POST',
                    data: { action: 'delete_district', district_id: id },
                    success: function (response) {
                        if (response.trim() === 'deleted') {
                            Toast.fire({
                                icon: 'success',
                                title: 'District has been deleted.'
                            });
                            loadContent('components/district/district.php');
                        } else {
                            Swal.fire('Error!', response, 'error');
                        }
                    }
                });
            }
        });
    };

    // 4. SEARCH BAR LOGIC
    $('body').off('keyup', '#districtSearch').on('keyup', '#districtSearch', function () {
        var value = $(this).val().toLowerCase();
        $("#districtTable tbody tr").each(function () {
            var rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(value) > -1);
        });
    });
});