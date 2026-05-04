$(document).ready(function () {

    // 1. Toast Notification Configuration (Choty alerts ke liye)
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

    // 2. INSERT & UPDATE Logic (Tehsil Save/Edit)
    $(document).off('submit', '#tehsilForm').on('submit', '#tehsilForm', function (e) {
        e.preventDefault();

        let submitBtn = $(this).find('button[type="submit"]');
        let originalBtnHtml = submitBtn.html();

        // Button ko disable karky loader dikhana
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

        $.ajax({
            url: 'backend/tehsil/tehsil-process.php',
            type: 'POST',
            data: $(this).serialize() + '&action=save_tehsil',
            success: function (response) {
                submitBtn.prop('disabled', false).html(originalBtnHtml);
                var res = response.trim();

                if (res === 'success' || res === 'updated') {
                    // --- SUCCESS LOGIC ---
                    Toast.fire({
                        icon: 'success',
                        title: res === 'success' ? 'Tehsil Added Successfully!' : 'Tehsil Updated Successfully!'
                    });

                    // 1.5 second baad wapis list par le jana
                    setTimeout(() => {
                        loadContent('components/tehsil/tehsil.php');
                    }, 1500);

                } else if (res === 'exists') {
                    // --- DUPLICATE ENTRY ALERT (Staff System Style) ---
                    Swal.fire({
                        icon: 'warning',
                        title: 'Duplicate Entry!',
                        text: 'Is District mein ye Tehsil pehle se registered hai. Baraye meherbani naam check karein.',
                        confirmButtonColor: '#6366f1'
                    });

                } else {
                    // --- DATABASE YA SERVER ERROR ---
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Occurred',
                        text: response
                    });
                }
            },
            error: function () {
                submitBtn.prop('disabled', false).html(originalBtnHtml);
                Swal.fire('Error', 'Server se connection nahi ho saka!', 'error');
            }
        });
    });

    // 3. DELETE Logic (Tehsil Delete karne ke liye)
    window.deleteTehsil = function (id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Kya aap waqai is tehsil ko khatam karna chahte hain?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6366f1',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'backend/tehsil/tehsil-process.php',
                    type: 'POST',
                    data: { action: 'delete_tehsil', tehsil_id: id },
                    success: function (response) {
                        if (response.trim() === 'deleted') {
                            Toast.fire({
                                icon: 'success',
                                title: 'Tehsil deleted successfully.'
                            });
                            // List refresh karna
                            loadContent('components/tehsil/tehsil.php');
                        } else {
                            Swal.fire('Error!', response, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!', 'Server connection failed.', 'error');
                    }
                });
            }
        });
    };

    // 4. SEARCH BAR FILTER (Table search ke liye)
    $('body').off('keyup', '#tehsilSearch').on('keyup', '#tehsilSearch', function () {
        var value = $(this).val().toLowerCase();
        $("#tehsilTable tbody tr").each(function () {
            // Pure row ka text check karega (Tehsil name aur District name dono cover honge)
            var rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(value) > -1);
        });
    });
});