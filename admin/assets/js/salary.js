$(document).ready(function () {
    // 1. Toast Notification Setup
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // --- 2. EDIT MODE AUTO-FILL LOGIC ---
    let salaryId = $('input[name="salary_id"]').val();
    if (salaryId && salaryId !== "") {
        let oldDistId = $('#salary_district').val();
        let oldTehsilId = $('#old_tehsil_val').val();
        let oldRole = $('#old_role_val').val();
        let oldStaffId = $('#old_staff_val').val();

        if (oldDistId) {
            $.post('backend/salary/salary-process.php', {
                action: 'fetch_tehsils_by_name',
                district_name: oldDistId
            }, function (tehsilHtml) {
                $('#salary_tehsil').html(tehsilHtml);
                $('#salary_tehsil').val(oldTehsilId);

                if (oldTehsilId && oldRole) {
                    $.post('backend/salary/salary-process.php', {
                        action: 'fetch_staff_filtered',
                        tehsil_id: oldTehsilId,
                        role: oldRole
                    }, function (staffHtml) {
                        $('#staff_name_list').html(staffHtml);
                        $('#staff_name_list').val(oldStaffId);

                        if (oldStaffId) {
                            fetchFixedSalary(oldStaffId);
                        }
                    });
                }
            });
        }
    }

    // --- 3. GLOBAL MULTI-FILTER LOGIC (Unique Prefixes Applied) ---
    $(document).on('keyup change', '#sal_salarySearch, #sal_filterRole, #sal_filterDistrict, #sal_filterTehsil, #sal_filterStatus', function () {

        if ($('#salaryTable').length === 0) return;

        var searchTerm = ($('#sal_salarySearch').val() || "").toLowerCase().trim();
        var roleTerm = ($('#sal_filterRole').val() || "").toLowerCase().trim();
        var districtTerm = ($('#sal_filterDistrict').val() || "").toLowerCase().trim();
        var tehsilTerm = ($('#sal_filterTehsil').val() || "").toLowerCase().trim();
        var statusTerm = ($('#sal_filterStatus').val() || "").toLowerCase().trim();

        $("#salaryTable tbody tr").each(function () {
            var $row = $(this);
            if ($row.attr('id') === 'noDataRow') return;

            // Column Data Extraction
            var rowAllText = $row.text().toLowerCase();
            var rowRole = $row.find('td:nth-child(2)').text().toLowerCase().trim();
            var rowLocation = $row.find('td:nth-child(3)').text().toLowerCase().trim(); // Ismein District aur Tehsil dono hain
            var rowStatus = $row.find('td:nth-child(13)').text().toLowerCase().trim(); // Status 13th column hai (StatusBadge wala)

            // Matching Logic
            var matchesSearch = (searchTerm === "") || (rowAllText.indexOf(searchTerm) > -1);
            var matchesRole = (roleTerm === "") || (rowRole === roleTerm); // Role exact match
            var matchesDistrict = (districtTerm === "") || (rowLocation.indexOf(districtTerm) > -1);
            var matchesTehsil = (tehsilTerm === "") || (rowLocation.indexOf(tehsilTerm) > -1);
            var matchesStatus = (statusTerm === "") || (rowStatus === statusTerm);

            if (matchesSearch && matchesRole && matchesDistrict && matchesTehsil && matchesStatus) {
                $row.show();
            } else {
                $row.hide();
            }
        });

        // Handle "No Records" message
        updateNoRecordsMessage();
    });

    function updateNoRecordsMessage() {
        var visibleRows = $('#salaryTable tbody tr:visible').not('#noDataRow').length;
        if (visibleRows === 0) {
            if ($('#noDataRow').length === 0) {
                $('#salaryTable tbody').append('<tr id="noDataRow"><td colspan="14" class="text-center p-5 text-muted">No records found matching your filters.</td></tr>');
            }
        } else {
            $('#noDataRow').remove();
        }
    }


    // --- 4. SAVE / UPDATE SALARY AJAX ---
    $(document).off('submit', '#salaryForm').on('submit', '#salaryForm', function (e) {
        e.preventDefault();
        let form = $(this);
        let submitBtn = form.find('button[type="submit"]');
        let sId = form.find('input[name="salary_id"]').val();
        let actionName = (sId && sId !== "") ? 'update_salary' : 'save_salary';

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

        $.ajax({
            url: 'backend/salary/salary-process.php',
            type: 'POST',
            data: form.serialize() + '&action=' + actionName,
            success: function (response) {
                if (response.trim().includes('success')) {
                    Toast.fire({
                        icon: 'success',
                        title: (actionName === 'update_salary') ? 'Record Updated!' : 'Record Saved!'
                    });
                    setTimeout(() => { loadContent('components/salary/salary-list.php'); }, 1200);
                } else {
                    Swal.fire('Error', response, 'error');
                    submitBtn.prop('disabled', false).html(sId ? 'Update Record' : 'Save Record');
                }
            }
        });
    });

    // --- 5. PAYMENT METHOD TOGGLE ---
    $(document).on('change', '#payment_method', function () {
        if ($(this).val() === 'Bank') {
            $('#bankFields').hide().removeClass('d-none').fadeIn();
        } else {
            $('#bankFields').addClass('d-none').hide();
        }
    });

    // --- 6. REAL-TIME CALCULATION TRIGGER ---
    $(document).on('input', 'input[name="working_days"], input[name="leaves"], input[name="bonus_amount"], #fixed_salary, #paid_amount', function () {
        calculateSalary();
    });

    // --- 7. DUPLICATE MONTH CHECK ---
    $(document).on('change', 'input[name="salary_month"]', function () {
        let staffId = $('#staff_name_list').val();
        let monthVal = $(this).val();
        if (staffId && monthVal && !$('input[name="salary_id"]').val()) {
            $.post('backend/salary/salary-process.php', {
                action: 'check_duplicate_salary',
                staff_id: staffId,
                month: monthVal.substring(0, 7)
            }, function (res) {
                if (res.trim() === 'exists') {
                    Swal.fire('Duplicate Entry', 'Is mahine ki salary pehle hi add ho chuki hai!', 'error');
                    $('input[name="salary_month"]').val('');
                }
            });
        }
    });

    // --- 9. DISTRICT -> TEHSIL (For Form) ---
  $(document).on('change', '#salary_district', function () {
    let distId = $(this).val(); // Form mein District ID hoti hai
    let tehsilDropdown = $('#salary_tehsil');
    
    if (distId) {
        tehsilDropdown.html('<option>Loading...</option>');
        // Yahan 'fetch_tehsils' use karein kyunke form ko ID chahiye
        $.post('backend/salary/salary-process.php', { 
            action: 'fetch_tehsils', 
            district_id: distId 
        }, function (data) {
            tehsilDropdown.html(data);
            $('#staff_name_list').html('<option value="">-- Select Name --</option>');
        });
    }
});

    // --- TEHSIL + ROLE SE STAFF LOAD ---
    $(document).on('change', '#salary_tehsil, #staff_role', function () {
        let tehsilId = $('#salary_tehsil').val();
        let role = $('#staff_role').val();
        if (tehsilId && role) {
            $('#staff_name_list').html('<option>Loading...</option>');
            $.post('backend/salary/salary-process.php', { action: 'fetch_staff_filtered', tehsil_id: tehsilId, role: role }, function (data) {
                $('#staff_name_list').html(data);
            });
        }
    });
});

// --- FILTER TEHSIL FUNCTION ---
function fetchTehsilsForSalaryFilter(districtName) {
    var tehsilDropdown = $('#sal_filterTehsil');

    if (!districtName) {
        tehsilDropdown.html('<option value="">All Tehsils</option>').trigger('change');
        return;
    }

    $.ajax({
        url: 'backend/salary/salary-process.php',
        type: 'POST',
        data: {
            action: 'fetch_tehsils_for_filter', // Sidebar filter ke liye ye action alag hai
            district_name: districtName
        },
        success: function (data) {
            tehsilDropdown.html(data).trigger('change');
        }
    });
}

// --- 8. CALCULATION FUNCTION ---
window.extraRem = 0;
function calculateSalary() {
    let fixedSalary = parseFloat($('#fixed_salary').val()) || 0;
    let workingDays = parseFloat($('input[name="working_days"]').val()) || 0;
    let leaves = parseFloat($('input[name="leaves"]').val()) || 0;
    let bonus = parseFloat($('input[name="bonus_amount"]').val()) || 0;
    let previousBalance = parseFloat(window.extraRem) || 0;
    let amountPaidNow = parseFloat($('#paid_amount').val()) || 0;

    let perDaySalary = fixedSalary / 30;
    let leaveDeduction = perDaySalary * leaves;
    $('#deduction_amount').val(Math.round(leaveDeduction));

    let payableDays = (workingDays > 0) ? (workingDays - leaves) : (30 - leaves);
    let basePayable = perDaySalary * payableDays;
    let totalPayableTotal = basePayable + bonus + previousBalance;

    $('#total_payable_readonly').val(Math.round(totalPayableTotal));
    $('#net_salary_hidden').val(Math.round(totalPayableTotal));

    let currentRemaining = totalPayableTotal - amountPaidNow;
    $('#remaining_balance').val(currentRemaining > 0 ? Math.round(currentRemaining) : 0);
}

// --- 11. FETCH FIXED SALARY ---
function fetchFixedSalary(staffId) {
    if (!staffId) return;
    $.post('backend/salary/salary-process.php', { action: 'get_salary_amount', staff_id: staffId }, function (data) {
        let res = JSON.parse(data);
        $('#fixed_salary').val(res.fixed_salary);
        window.extraRem = parseFloat(res.previous_remaining) || 0;
        calculateSalary();
    });
}

// --- 12. DELETE SALARY FUNCTION ---
function deleteSalary(id) {
    if (!id) return;

    Swal.fire({
        title: 'Are you sure?',
        text: "You Want To Delete This Salary?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('backend/salary/salary-process.php', {
                action: 'delete_salary',
                salary_id: id
            }, function (response) {
                if (response.trim() === 'success') {
                    Swal.fire(
                        'Deleted!',
                        'Salary record deleted Successfull.',
                        'success'
                    );
                    // Table refresh karne ke liye list dobara load karein
                    setTimeout(() => {
                        loadContent('components/salary/salary-list.php');
                    }, 1000);
                } else {
                    Swal.fire('Error', 'Delete nahi ho saka: ' + response, 'error');
                }
            });
        }
    });
}

