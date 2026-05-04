$(document).ready(function() {
    console.log("Attendance System Initialized");

    // 1. PAGE LOAD PAR DATA FETCH
    if ($('#attendanceSheet').length > 0) {
        loadAttendanceSheet();
    }

    // 2. DISTRICT -> TEHSIL FETCH (Cascading Logic)
    $(document).off('change', '#f_dist').on('change', '#f_dist', function() {
        let dist_id = $(this).val();
        
        // Dropdown loading state
        $('#f_teh').html('<option>Loading Tehsils...</option>');
        
        // PHP ab empty district_id par bhi saari tehsils bhejega
        $.post('backend/attendance/attendance-process.php', { 
            action: 'fetch_tehsils', 
            district_id: dist_id 
        }, function(data) {
            $('#f_teh').html(data);
            loadAttendanceSheet(); 
        });
    });

    // 3. AUTO-LOAD ON ANY FILTER CHANGE
    $(document).on('change', '#f_teh, #f_reg, #f_fuel, #f_vtype, #f_comp, #att_date', function() {
        loadAttendanceSheet();
    });

    // 4. SAVE ATTENDANCE
    $(document).on('submit', '#attendanceForm', function(e) {
        e.preventDefault();
        let btn = $('#saveAttBtn');
        
        let attendanceData = [];
        $('.att-check:checked').each(function() {
            attendanceData.push({ 
                id: $(this).val(), 
                status: $(this).closest('tr').find('.row-status').val() 
            });
        });

        if (attendanceData.length === 0) {
            Swal.fire('Error!', 'Please select at least one machinery', 'error');
            return false;
        }

        btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i> Saving...');

        $.post('backend/attendance/attendance-process.php', {
            action: 'save_bulk_attendance',
            att_date: $('#att_date').val(),
            attendance_data: attendanceData
        }, function(res) {
            btn.prop('disabled', false).html('Save Attendance');
            if (res.trim() === 'success') {
                Swal.fire('Success!', 'Attendance recorded.', 'success');
                loadAttendanceSheet();
            } else {
                Swal.fire('Response', res, 'info');
            }
        });
    });

    // 5. REPORT FILTERS - DISTRICT CHANGE
    $(document).on('change', '#r_dist', function() {
        let dist_id = $(this).val();
        $('#r_tehsil').html('<option>Loading...</option>');
        $.post('backend/attendance/attendance-process.php', { 
            action: 'fetch_tehsils', 
            district_id: dist_id 
        }, function(data) {
            $('#r_tehsil').html(data);
        });
    });
});

// --- LOAD ATTENDANCE SHEET FUNCTION ---
function loadAttendanceSheet() {
    let formData = {
        action: 'load_attendance_sheet',
        date: $('#att_date').val(),
        district_id: $('#f_dist').val(),
        tehsil_id: $('#f_teh').val(),
        vehicle_id: $('#f_reg').val(),
        fuel_type: $('#f_fuel').val(),
        v_type_id: $('#f_vtype').val(),
        company_id: $('#f_comp').val()
    };

    $('#attendanceSheet').html('<tr><td colspan="5" class="text-center text-muted"><i class="ri-loader-4-line ri-spin"></i> Loading Machinery...</td></tr>');

    $.post('backend/attendance/attendance-process.php', formData, function(res) {
        $('#attendanceSheet').html(res);
    });
}


// --- LOAD REPORT FUNCTION ---
function loadReport() {
    let s_date = $('#s_date').val();
    let e_date = $('#e_date').val();
    
    if(!s_date || !e_date) {
        Swal.fire('Error', 'Please select start and end date', 'warning');
        return;
    }

    $('#printPeriod').text("Period: " + s_date + " to " + e_date);

    let formData = {
        action: 'generate_report',
        start_date: s_date,
        end_date: e_date,
        district_id: $('#r_dist').val(),
        tehsil_id: $('#r_tehsil').val(),
        vehicle_id: $('#r_reg').val(),
        fuel_type: $('#r_fuel').val()
    };

    $('#reportData').html('<tr><td colspan="7" class="text-center"><i class="ri-loader-4-line ri-spin"></i> Generating Report...</td></tr>');

    $.post('backend/attendance/attendance-process.php', formData, function(res) {
        $('#reportData').html(res);
    });
}

// --- RESET FILTERS ---
// Reset Function
function resetAttendanceFilters() {
    $('#att_date').val(new Date().toISOString().slice(0, 10)); // Today's date
    $('#f_dist, #f_reg, #f_fuel, #f_comp').val('');
    $('#f_teh').html('<option value="">-- All Tehsils --</option>');
    
    // Dropdown reset ke baad data dobara load karein
    $.post('backend/attendance/attendance-process.php', { action: 'fetch_tehsils', district_id: '' }, function(data) {
        $('#f_teh').html(data);
        loadAttendanceSheet();
    });
}

// Select All functionality (jo aapne image mein check kiya hua hai)
$(document).on('change', '#selectAll', function() {
    $('.att-check').prop('checked', this.checked);
});
// Global Page Navigation
function goToPage(path) {
    if (typeof loadContent === "function") {
        loadContent(path);
    } else {
        window.location.href = path;
    }
}
$(document).ready(function() {
    // Page load hote hi report chalane ke liye
    loadMonthlyReport();

    // Generate button ka click event (agar onclick kaam nahi kar raha)
    $('.btn-primary:contains("Generate")').on('click', function(e) {
        e.preventDefault();
        loadMonthlyReport();
    });
});

function loadMonthlyReport() {
    // Selectors ko mazeed robust banaya gaya hai (image_4f0d3c.png ke mutabiq)
    let month = $('#report_month').val() || $('input[type="month"]').val();
    let dist = $('#r_dist').val() || $('#district').val() || $('select[name="district"]').val();
    let teh = $('#r_tehsil').val() || $('#tehsil').val();
    let fuel = $('#r_fuel').val() || $('#fuel_type').val();

    // Console debugging ke liye
    console.log("Fetching report for:", month, "District:", dist);

    // Initial Loading State
    if ($('#reportData').length) {
        $('#reportData').html('<tr><td colspan="40" class="py-5 text-center text-primary"><i class="fas fa-spinner fa-spin"></i> Loading Records...</td></tr>');
    }

    $.ajax({
        url: 'backend/attendance/attendance-process.php', 
        type: 'POST',
        // Response handle karne ke liye DataType pehle hi set karein
        dataType: 'json', 
        data: {
            action: 'generate_monthly_calendar_report',
            month: month,
            district_id: dist,
            tehsil_id: teh,
            fuel_type: fuel
        },
        success: function(data) {
            console.log("Data successfully rendered");

            // 1. Header (Dates 1-31) inject karein
            if ($('#calendarHeader').length) {
                $('#calendarHeader').html(data.header);
            }

            // 2. Body (Attendance Records) inject karein
            if ($('#reportData').length) {
                $('#reportData').html(data.body);
            } else {
                // Fallback: Agar ID nahi milti to table ke tbody mein inject karein
                $('#reportTable tbody').html(data.body);
            }

            // 3. Month Heading Update (Professional Format)
            if (month && $('#monthHeader').length) {
                let dateObj = new Date(month + "-01");
                let monthName = dateObj.toLocaleString('default', { month: 'long', year: 'numeric' });
                $('#monthHeader').html("For the Month of: <span style='color:#000;'>" + monthName + "</span>");
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error Details:", status, error);
            let errorMsg = '<tr><td colspan="40" class="text-danger py-4 text-center"><b>Server Connection Failed!</b> (Code: ' + xhr.status + ')</td></tr>';
            $('#reportData').html(errorMsg);
        }
    });
}

function resetReport() {
    // Current month par wapis set karne ke liye (Optional)
    let now = new Date();
    let currentMonth = now.toISOString().slice(0, 7);
    $('#report_month').val(currentMonth);
    
    $('#r_dist, #r_fuel').val('');
    $('#r_tehsil').html('<option value="">-- All Tehsils --</option>');
    
    loadMonthlyReport();
}

function deleteRowAttendance(vid, month) {
    Swal.fire({
        title: 'Delete Entire Row?',
        text: "This will remove all attendance for this vehicle in " + month + "!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('backend/attendance/attendance-process.php', { 
                action: 'delete_monthly_row', 
                vehicle_id: vid, 
                month: month 
            }, function(res) {
                Swal.fire('Deleted!', 'Attendance records have been removed.', 'success');
                loadMonthlyReport();
            });
        }
    });
}