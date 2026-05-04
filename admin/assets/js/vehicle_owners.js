$(document).ready(function () {

    // --- 0. SWEET TOAST CONFIGURATION ---
    const Toast = (typeof Swal !== 'undefined') ? Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    }) : null;

    // --- 1. FILTERS & SEARCH LOGIC ---
    function filterOwnerTable() {
        let searchTerm = $('#own_companySearch').val().toLowerCase().trim();
        let districtFilt = $('#own_filterDistrict').val().toLowerCase().trim();
        let tehsilFilt = $('#own_filterTehsil').val().toLowerCase().trim();
        let statusFilt = $('#own_filterStatus').val().toLowerCase().trim();

        $('table tbody tr').not('#no-results-row').each(function () {
            let row = $(this);

            let name = row.find('.owner-name-cell').text().toLowerCase();
            let cnic = row.find('.cnic-cell').text().toLowerCase();
            let contact = row.find('.contact-cell').text().toLowerCase();
            let district = row.find('.district-cell').text().toLowerCase().trim();
            let tehsil = row.find('.tehsil-cell').text().toLowerCase().trim();

            // Status text uthane ke liye badge target karein
            let statusText = row.find('.badge').last().text().toLowerCase().trim();

            let matchesSearch = (searchTerm === "") || (name.includes(searchTerm)) || (cnic.includes(searchTerm)) || (contact.includes(searchTerm));
            let matchesDistrict = (districtFilt === "") || (district === districtFilt);
            let matchesTehsil = (tehsilFilt === "") || (tehsil === tehsilFilt);
            let matchesStatus = (statusFilt === "") || (statusText === statusFilt);

            if (matchesSearch && matchesDistrict && matchesTehsil && matchesStatus) {
                row.show();
            } else {
                row.hide();
            }
        });

        $('#no-results-row').remove();
        if ($('table tbody tr:visible').length === 0) {
            $('table tbody').append('<tr id="no-results-row"><td colspan="11" class="text-center py-5 text-muted">No matching records found.</td></tr>');
        }
    }

    // --- 2. EVENT LISTENERS FOR FILTERS ---
    $(document).on('keyup', '#own_companySearch', filterOwnerTable);
    $(document).on('change', '#own_filterStatus, #own_filterTehsil', filterOwnerTable);

    // List view wala District filter
    $(document).on('change', '#own_filterDistrict', function () {
        let d_name = $(this).val();
        let tehDropdown = $('#own_filterTehsil');

        if (d_name) {
            tehDropdown.html('<option value="">Loading...</option>');
            $.post('backend/owner/owner-process.php', { action: 'get_tehsils_by_name', d_name: d_name }, function (data) {
                tehDropdown.html('<option value="">All Tehsils</option>' + data);
                filterOwnerTable();
            });
        } else {
            tehDropdown.html('<option value="">All Tehsils</option>');
            filterOwnerTable();
        }
    });

    // --- 3. ADD/EDIT FORM LOGIC (District ID based) ---
    $(document).on('change', '#own_dist', function () {
        let d_id = $(this).val();
        let tehDropdown = $('#own_teh');

        if (d_id) {
            tehDropdown.html('<option>Loading...</option>');
            $.post('backend/owner/owner-process.php', { action: 'get_tehsils', district_id: d_id }, function (data) {
                tehDropdown.html(data);
            });
        } else {
            tehDropdown.html('<option value="">First Select District</option>');
        }
    });

    // --- 4. FORM SUBMIT ---
    $(document).off('submit', '#ownerForm').on('submit', '#ownerForm', function (e) {
        e.preventDefault();
        let btn = $(this).find('button[type="submit"]');
        let originalText = btn.text();
        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: 'backend/owner/owner-process.php',
            type: 'POST',
            data: $(this).serialize() + '&action=save_owner',
            success: function (res) {
                let response = res.trim();
                if (response === 'success' || response === 'updated') {
                    if (Toast) {
                        Toast.fire({
                            icon: 'success',
                            title: response === 'success' ? 'Owner Registered!' : 'Owner Updated!'
                        });
                    }
                    setTimeout(() => loadContent('components/owner/owner.php'), 1500);
                } else {
                    if (Toast) Toast.fire({ icon: 'error', title: response });
                    btn.prop('disabled', false).text(originalText);
                }
            },
            error: function () {
                if (Toast) Toast.fire({ icon: 'error', title: 'Connection Error!' });
                btn.prop('disabled', false).text(originalText);
            }
        });
    });

}); // End document ready

// --- 5. DELETE OWNER ---
function deleteOwner(id) {
    if (typeof Swal === 'undefined') {
        if (confirm('Delete this owner?')) {
            // Basic fallback logic
        }
        return;
    }

    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('backend/owner/owner-process.php', { action: 'delete_owner', owner_id: id }, function (res) {
                if (res.trim() === 'deleted') {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Deleted!', showConfirmButton: false, timer: 2000 });
                    loadContent('components/owner/owner.php');
                } else {
                    Swal.fire('Error', res, 'error');
                }
            });
        }
    });
}