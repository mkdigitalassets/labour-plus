// CREATE / UPDATE OR OWNER
$(document).off('submit', '#addOwnerForm').on('submit', '#addOwnerForm', function (e) {
    e.preventDefault();

    const submitBtn = $(this).find('button[type="submit"]');
    const originalBtnText = submitBtn.html();

    var ownerId = $('input[name="owner_id"]').val();
    var formData = $(this).serialize();

    if (formData.indexOf('owner_id') === -1) {
        formData += "&owner_id=" + ownerId;
    }

    // Loader show karna
    submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line btn-loader"></i> Processing...');

    $.ajax({
        url: 'backend/owner/owner-process.php',
        type: 'POST',
        data: formData,
        success: function (response) {
            submitBtn.prop('disabled', false).html(originalBtnText);

            var res = response.trim();

            if (res === "success" || res === "updated") {

                // Pehle check karte hain ke SweetAlert load hai ya nahi
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });

                    Toast.fire({
                        icon: 'success',
                        title: res === "success" ? 'Registered Successfully!' : 'Updated Successfully!'
                    });
                } else {
                    // Agar Swal load nahi hua to purana alert dikha do taake kaam na ruke
                    alert(res === "success" ? 'Registered Successfully!' : 'Updated Successfully!');
                }

                // Popup ke foran baad redirect/content load
                setTimeout(function () {
                    if (typeof loadContent === 'function') {
                        loadContent('components/owner/owner-detail.php');
                    }
                }, 1000); // 1 second ka delay taake popup nazar aa jaye

            } else {
                alert("Error from Server: " + response);
            }
        },
        error: function () {
            submitBtn.prop('disabled', false).html(originalBtnText);
            alert("Network Error: Request fail ho gayi.");
        }
    });
});

// DELETE OWNER DETAILS
function deleteOwner(ownerId) {
    // 1. Confirmation Popup (Bada wala darmiyan mein)
    Swal.fire({
        title: 'Are you sure?',
        text: "YOU WANT TO DELETE THIS OWNER?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {

            // 2. Agar user confirm kare to AJAX request
            $.ajax({
                url: 'backend/owner/owner-process.php',
                type: 'POST',
                data: {
                    action: 'delete_owner',
                    owner_id: ownerId
                },
                success: function (response) {
                    var res = response.trim();

                    if (res === "deleted") {
                        // 3. Right Corner mein chota success popup
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });

                        Toast.fire({
                            icon: 'success',
                            title: 'Owner Deleted Successfully!'
                        });

                        // 4. Data refresh karna (wapis list load karna)
                        if (typeof loadContent === 'function') {
                            loadContent('components/owner/owner-detail.php');
                        } else {
                            window.location.reload();
                        }

                    } else {
                        // Agar backend se koi error aaye
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response
                        });
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Server connection failed!', 'error');
                }
            });
        }
    });
}