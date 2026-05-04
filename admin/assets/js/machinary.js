// --- 0. SWEET TOAST CONFIGURATION ---
// Check karein ke Toast pehle se declare to nahi, taake 'already declared' error na aaye
if (typeof Toast === 'undefined') {
    window.Toast = Swal.mixin({
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
}

// --- 1. FORM SUBMISSION LOGIC ---
$(document).off('submit', '#machineryForm').on('submit', '#machineryForm', function (e) {
    e.preventDefault();
    let formData = new FormData(this);

    $.ajax({
        url: 'backend/machinery/machinery-process.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            if (response.trim() === 'success') {
                Toast.fire({
                    icon: 'success',
                    title: 'Data Saved Successfully!'
                });
                loadContent('components/machinery/machinery-list.php');
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'Error: ' + response
                });
            }
        }
    });
});

// --- 2. DROPDOWNS DEPENDENCY LOGIC ---

// District change hone par Tehsil load karein
$(document).off('change', '#district_id').on('change', '#district_id', function () {
    let districtId = $(this).val();
    $('#tehsil_id').html('<option value="">-- Loading Tehsils... --</option>');
    fetchTehsils(districtId);
});

function fetchTehsils(districtId) {
    if (!districtId) {
        $('#tehsil_id').html('<option value="">-- Select District First --</option>');
        return;
    }
    $.post('backend/machinery/machinery-process.php', { action: 'fetch_tehsils', district_id: districtId }, function (data) {
        $('#tehsil_id').html(data);
    });
}

// Tehsil change hone par Manager refresh karein
$(document).off('change', '#tehsil_id').on('change', '#tehsil_id', function () {
    let tehsilId = $(this).val();
    fetchManagers(tehsilId);
});

// Category Type change hone par Categories load karein
$(document).off('change', '#type_id').on('change', '#type_id', function () {
    let typeId = $(this).val();
    $('#expense_category').html('<option value="">-- Loading Categories... --</option>');
    fetchCategories(typeId);
});

function fetchCategories(typeId) {
    if (!typeId) {
        $('#expense_category').html('<option value="">-- Select Type First --</option>');
        return;
    }
    $.post('backend/machinery/machinery-process.php', { action: 'fetch_categories', type_id: typeId }, function (data) {
        $('#expense_category').html(data);
    });
}

// Category change hone par Sub-Categories load karein
$(document).off('change', '#expense_category').on('change', '#expense_category', function () {
    let catId = $(this).val();
    $('#sub_category').html('<option value="">-- Loading Sub-Categories... --</option>');
    fetchSubCategories(catId);
});

function fetchSubCategories(catId) {
    if (!catId) {
        $('#sub_category').html('<option value="">-- Select Category First --</option>');
        return;
    }
    $.post('backend/machinery/machinery-process.php', { action: 'fetch_subcategories', category_id: catId }, function (data) {
        $('#sub_category').html(data);
    });
}

// --- 3. MANAGER FETCHING ---
function fetchManagers(tehsilId) {
    if (!tehsilId) return;
    $.post('backend/machinery/machinery-process.php', { action: 'fetch_managers', tehsil_id: tehsilId }, function (data) {
        if ($('#manager_list').length) {
            $('#manager_list').html(data);
        }
    });
}

// --- 4. EDIT & DELETE FUNCTIONS ---

// Edit function ko global banaya
window.editMachine = function (data) {
    loadContent('components/machinery/add-machinery.php', null, function () {
        setTimeout(function () {
            $('#machine_id').val(data.machine_id);
            $('#registration_no').val(data.registration_no);
            $('#status_container').show();
            $('#status').val(data.status);

            // Step-by-step trigger for dependencies
            $('#district_id').val(data.district_id).trigger('change');
            setTimeout(() => {
                $('#tehsil_id').val(data.tehsil_id);
            }, 500);

            $('#type_id').val(data.type_id).trigger('change');
            setTimeout(() => {
                $('#expense_category').val(data.category_id).trigger('change');
                setTimeout(() => {
                    $('#sub_category').val(data.sub_id);
                }, 500);
            }, 500);

            $('#submitBtn').html('<i class="ri-edit-line"></i> Update Machinery')
                .addClass('btn-success').removeClass('btn-primary');

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }, 500);
    });
};

// Delete function ko global banaya taake component reload par masla na aaye
window.deleteMachine = function (id) {
    if (typeof Swal === 'undefined') {
        alert("SweetAlert library missing!");
        return;
    }

    Swal.fire({
        title: 'Are you sure?',
        text: "This machine will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, keep it'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('backend/machinery/machinery-process.php', {
                action: 'delete_machinery',
                id: id
            }, function (res) {
                if (res.trim() === 'success') {
                    Toast.fire({
                        icon: 'success',
                        title: 'Machinery removed successfully!'
                    });
                    loadContent('components/machinery/machinery-list.php');
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error: ' + res
                    });
                }
            });
        }
    });
};


$(document).ready(function () {
    // Initial load
    fetchFilteredData();

    // Trigger filter on change or typing
    $('.filter-trigger').on('change', function () {
        fetchFilteredData();
    });

    $('#machinerySearch').on('keyup', function () {
        fetchFilteredData();
    });
});

function fetchFilteredData() {
    const district = $('#filterDistrict').val();
    const status = $('#filterStatus').val();
    const search = $('#machinerySearch').val();

    $.ajax({
        url: 'components/machinery/fetch-filtered-machinery.php',
        type: 'POST',
        data: {
            district: district,
            status: status,
            search: search
        },
        success: function (response) {
            $('#machineryListData').html(response);
        }
    });
}