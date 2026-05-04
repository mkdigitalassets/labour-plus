// Submit User Form with Loading State
$(document).off('submit', '#userForm').on('submit', '#userForm', function (e) {
    e.preventDefault();

    let form = $(this);
    let submitBtn = form.find('button[type="submit"]');
    let originalBtnText = submitBtn.html();

    // Loading Start
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Saving...');

    $.ajax({
        url: 'backend/auth/auth-process.php',
        type: 'POST',
        data: form.serialize() + '&action=save_user',
        success: function (res) {
            setTimeout(function () {
                if (res.trim() === 'success') {
                    Swal.fire('Success', 'User profile updated!', 'success');
                    loadContent('components/auth/auth-list.php');
                } else {
                    Swal.fire('Error', res, 'error');
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            }, 600);
        },
        error: function () {
            Swal.fire('Error', 'Server communication failed!', 'error');
            submitBtn.prop('disabled', false).html(originalBtnText);
        }
    });
});

// Fetch Tehsils
function fetchTehsils(distId) {
    let role = $('#roleSelector').val();
    if (!distId) return;
    $.post('backend/auth/auth-process.php', { action: 'get_tehsils', role: role, district_id: distId }, function (data) {
        $('#tehsil_dropdown').html(data);
        fetchStaffByLocation(); // Staff refresh karein
    });
}

// Fetch Staff
function fetchStaffByLocation() {
    let role = $('#roleSelector').val();
    let dist = $('#district_id').val();
    let teh = $('#tehsil_dropdown').val();

    // Agar user admin nahi hai to dist/teh ka hona zaroori hai staff load karne ke liye
    if (role.toLowerCase() !== 'admin' && !dist) return;

    $('#staff_dropdown').html('<option value="">Loading...</option>');
    $.post('backend/auth/auth-process.php', {
        action: 'get_staff_by_location',
        role: role,
        district_id: dist,
        tehsil_id: teh
    }, function (data) {
        $('#staff_dropdown').html(data);
    });
}

// 1. Jab Role select ho -> Districts load hon
// A. Jab Role badle -> Districts load hon
function toggleRegionalFields(role) {
    const fields = $('#regionalFields');
    if (role && role.toLowerCase() !== 'admin') {
        fields.fadeIn();
        $.post('backend/auth/auth-process.php', { action: 'get_districts_from_staff', role: role }, function (data) {
            $('#district_id').html(data); // District dropdown fill hoga
            $('#tehsil_dropdown').html('<option value="">Select Tehsil</option>');
            $('#staff_dropdown').html('<option value="">Select Name</option>');
        });
    } else {
        fields.fadeOut();
        fetchStaffByLocation(); // Admin ke liye direct
    }
}

// B. Jab District badle -> Tehsils load hon
function fetchTehsils(distId) {
    let role = $('#roleSelector').val();
    if (!distId) return;

    $.post('backend/auth/auth-process.php', { action: 'get_tehsils_from_staff', role: role, district_id: distId }, function (data) {
        $('#tehsil_dropdown').html(data); // Tehsil dropdown fill hoga
        $('#staff_dropdown').html('<option value="">Select Tehsil First</option>');
    });
}

// C. Jab Tehsil badle -> Staff Names load hon
function fetchStaffByLocation() {
    let role = $('#roleSelector').val();
    let dist = $('#district_id').val();
    let teh = $('#tehsil_dropdown').val();

    // Final call staff_name lane ke liye
    $.post('backend/auth/auth-process.php', {
        action: 'get_staff_names_only',
        role: role,
        district_id: dist,
        tehsil_id: teh
    }, function (data) {
        $('#staff_dropdown').html(data);
    });
}

// 2. Jab District select ho -> Tehsils load hon
// B. Jab District badle -> Tehsils load hon
function fetchTehsils(distId) {
    let role = $('#roleSelector').val();
    if (!distId) return;

    $.post('backend/auth/auth-process.php', { action: 'get_tehsils_from_staff', role: role, district_id: distId }, function (data) {
        $('#tehsil_dropdown').html(data); // Tehsil dropdown fill hoga
        $('#staff_dropdown').html('<option value="">Select Tehsil First</option>');
    });
}

// 3. Jab Tehsil select ho -> Final Staff Names load hon
function fetchStaffByLocation() {
    let role = $('#roleSelector').val();
    let teh = $('#tehsil_dropdown').val();

    // Staff sirf tab fetch karein jab Role aur Tehsil dono hon (Non-Admin ke liye)
    if (role.toLowerCase() !== 'admin' && !teh) {
        $('#staff_dropdown').html('<option value="">Select Tehsil First</option>');
        return;
    }

    $.post('backend/auth/auth-process.php', {
        action: 'get_staff_names_only',
        role: role,
        tehsil_id: teh
    }, function (data) {
        $('#staff_dropdown').html(data);
    });
}


// Password toggle
function togglePass() {
    let x = document.getElementById("userPass");
    let icon = $(this).find('i');
    if (x.type === "password") {
        x.type = "text";
        icon.removeClass('ri-eye-line').addClass('ri-eye-off-line');
    } else {
        x.type = "password";
        icon.removeClass('ri-eye-off-line').addClass('ri-eye-line');
    }
}

// Initial check for edit mode
$(document).ready(function () {
    let currentRole = $('#roleSelector').val();
    if (currentRole && currentRole !== 'admin') {
        $('#regionalFields').show();
    } else {
        $('#regionalFields').hide();
    }
});

// Delete User Function
function deleteUser(userId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('backend/auth/auth-process.php', {
                action: 'delete_user',
                user_id: userId
            }, function (res) {
                if (res.trim() === 'deleted') {
                    Swal.fire('Deleted!', 'User has been removed.', 'success');
                    loadContent('components/auth/auth-list.php'); // List refresh karein
                } else {
                    Swal.fire('Error', res, 'error');
                }
            });
        }
    });
}