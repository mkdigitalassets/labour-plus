$(document).ready(function () {

    // --- 1. CREATE / UPDATE STAFF LOGIC ---
    $(document).off('submit', '#staffForm').on('submit', '#staffForm', function (e) {
        e.preventDefault();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        var formData = $(this).serialize();

        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-2"></i> Processing...');

        $.ajax({
            url: 'backend/staff/staff-process.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                submitBtn.prop('disabled', false).html(originalBtnText);
                var res = response.trim();
                if (res === "success" || res === "updated") {
                    Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    }).fire({
                        icon: 'success',
                        title: res === "success" ? 'Employee Registered!' : 'Employee Updated!'
                    });
                    setTimeout(function () {
                        if (typeof loadContent === 'function') {
                            loadContent('components/staff/staff-list.php');
                        }
                    }, 1000);
                } else if (res === "duplicate") {
                    Swal.fire({ icon: 'warning', title: 'Already Exists!', text: 'CNIC is already registered.' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response });
                }
            }
        });
    });

    // --- 2. ADD/EDIT FORM DEPENDENT DROPDOWN (By ID) ---
    $(document).on('change', '#staff_district', function () {
        var districtID = $(this).val();
        var tehsilSelect = $('#tehsil_id');
        if (districtID) {
            tehsilSelect.html('<option value="">Loading...</option>');
            $.ajax({
                url: 'backend/staff/staff-process.php',
                type: 'POST',
                data: { action: 'fetch_tehsils', district_id: districtID },
                success: function (html) { tehsilSelect.html(html); }
            });
        } else {
            tehsilSelect.html('<option value="">Select District First</option>');
        }
    });


    // Staff Management Filtering

    $(document).on('keyup change', '#stf_employeeSearch, #stf_roleFilter, #stf_districtFilter, #stf_tehsilFilter, #stf_statusFilter', function () {

        // Safety check: code sirf tab chale jab Staff table mojud ho
        if ($('#employeeTableBody').length === 0) return;

        // 1. Get filter values safely
        var searchText = ($('#stf_employeeSearch').val() || "").toLowerCase().trim();
        var filterRole = ($('#stf_roleFilter').val() || "").toLowerCase().trim();
        var filterDist = ($('#stf_districtFilter').val() || "").toLowerCase().trim();
        var filterTehsil = ($('#stf_tehsilFilter').val() || "").toLowerCase().trim();
        var filterStatus = ($('#stf_statusFilter').val() || "").toLowerCase().trim();

        // 2. Iterate through table rows
        $('#employeeTableBody tr').each(function () {
            var row = $(this);
            if (row.attr('id') === 'noDataRow') return;

            // 3. Extract text from columns (Check indices based on your table structure)
            var rowName = row.find('td:nth-child(1)').text().toLowerCase().trim();
            var rowRole = row.find('td:nth-child(2)').text().toLowerCase().trim();
            var rowDist = row.find('td:nth-child(3)').text().toLowerCase().trim();
            var rowTehsil = row.find('td:nth-child(4)').text().toLowerCase().trim();
            var rowContact = row.find('td:nth-child(5)').text().toLowerCase().trim();
            var rowStatus = row.find('td:nth-child(6)').text().toLowerCase().trim();

            // 4. Advanced Matching Logic
            var matchSearch = (searchText === "") || (rowName.indexOf(searchText) > -1) || (rowContact.indexOf(searchText) > -1);
            var matchRole = (filterRole === "") || (rowRole.indexOf(filterRole) > -1);
            var matchDist = (filterDist === "") || (rowDist.indexOf(filterDist) > -1);
            var matchTehsil = (filterTehsil === "") || (rowTehsil.indexOf(filterTehsil) > -1);

            // Status matching using indexOf for partial badge text safety
            var matchStatus = (filterStatus === "") || (rowStatus.indexOf(filterStatus) > -1);

            // 5. Final Visibility Toggle
            if (matchSearch && matchRole && matchDist && matchTehsil && matchStatus) {
                row.show();
            } else {
                row.hide();
            }
        });

        // 6. Handle "No Records Found" message
        var visibleRows = $('#employeeTableBody tr:visible').not('#noDataRow').length;
        if (visibleRows === 0) {
            if ($('#noDataRow').length === 0) {
                $('#employeeTableBody').append('<tr id="noDataRow"><td colspan="8" class="text-center p-5 text-muted">No records found.</td></tr>');
            }
        } else {
            $('#noDataRow').remove();
        }
    });


});


/**
     * AJAX for Staff Tehsils
     */
function fetchTehsilsForStaffFilter(districtName) {
    var tehsilDropdown = $('#stf_tehsilFilter');

    if (!districtName) {
        tehsilDropdown.html('<option value="">All Tehsils</option>').trigger('change');
        return;
    }

    $.ajax({
        url: 'backend/staff/staff-process.php',
        type: 'POST',
        data: {
            action: 'fetch_tehsils_by_name',
            district_name: districtName
        },
        success: function (html) {
            tehsilDropdown.html('<option value="">All Tehsils</option>' + html).trigger('change');
        }
    });
}

// --- 5. DELETE STAFF FUNCTION ---
function deleteStaff(staffId) {
    Swal.fire({
        title: 'Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'backend/staff/staff-process.php',
                type: 'POST',
                data: { action: 'delete_staff', staff_id: staffId },
                success: function (response) {
                    if (response.trim() === "deleted") {
                        Swal.fire('Deleted!', 'Employee removed.', 'success');
                        loadContent('components/staff/staff-list.php');
                    }
                }
            });
        }
    });
}