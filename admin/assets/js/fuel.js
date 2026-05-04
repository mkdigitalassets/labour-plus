function saveBulkData() {
    let formData = $('#fuelBody :input').serialize();
    let fuelDate = $('#fuel_date').val();

    $.post('backend/fuel/fuel-process.php', formData + '&action=save_fuel_mileage&fuel_date=' + fuelDate, function(res) {
        if(res.trim() === 'success') {
            Swal.fire('Success', 'Records updated!', 'success');
        } else {
            Swal.fire('Error', res, 'error');
        }
    });
}
$(document).ready(function() {
    $('.select2-init').select2({ width: '100%' });

    // Live Sum Calculation on input change
    $(document).on('input', '.qty-input, .meter-input', function() {
        calculatePageTotals();
    });
});

function loadData(page) {
    let data = {
        action: 'fetch_fuel_mileage',
        start_date: $('#start_date').val(),
        end_date: $('#end_date').val(),
        district: $('#f_district').val(),
        tehsil: $('#f_tehsil').val(),
        vehicle_id: $('#f_vehicle').val(),
        limit: $('#page_limit').val(),
        page: page
    };

    $('#tableContainer').fadeIn();
    $('#fuelBody').html('<tr><td colspan="5" class="text-center py-5"><div class="spinner-border text-primary border-2"></div><p class="mt-2 text-muted">Loading vehicles...</p></td></tr>');

    $.post('backend/fuel/fuel-process.php', data, function(res) {
        try {
            let response = JSON.parse(res);
            $('#fuelBody').html(response.html);
            $('#paginationControls').html(response.pagination);
            calculatePageTotals();
        } catch (e) {
            console.error("Parsing Error:", res);
            Swal.fire('Error', 'Invalid server response', 'error');
        }
    });
}

function calculatePageTotals() {
    let totalFuel = 0;
    let totalMeter = 0;
    
    $('.qty-input').each(function() { 
        totalFuel += parseFloat($(this).val()) || 0; 
    });
    $('.meter-input').each(function() { 
        totalMeter += parseFloat($(this).val()) || 0; 
    });
    
    $('#page_total_fuel').text(totalFuel.toFixed(2));
    $('#page_total_meter').text(totalMeter.toFixed(2));
}

function saveBulkData() {
    let fuelDate = $('#start_date').val(); // Using start_date as entry date
    let formData = $('#fuelBody :input').serialize();

    if (!formData) {
        Swal.fire('Info', 'No data to save. Please fetch the list first.', 'info');
        return;
    }

    Swal.fire({
        title: 'Are you sure?',
        text: "This will update records for " + fuelDate,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, Save All!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('backend/fuel/fuel-process.php', formData + '&action=save_fuel_mileage&fuel_date=' + fuelDate, function(res) {
                if(res.trim() === 'success') {
                    Swal.fire('Saved!', 'Records have been updated successfully.', 'success');
                } else {
                    Swal.fire('Error', res, 'error');
                }
            });
        }
    });
}
$(document).on('keydown', '.qty-input, .meter-input', function(e) {
    let currentInput = $(this);
    let inputs = $('.qty-input, .meter-input'); // Tamam inputs ki list
    let index = inputs.index(currentInput);

    // --- Keyboard Navigation ---
    if (e.which === 13 || e.which === 40) { // Enter ya Down Arrow
        e.preventDefault();
        inputs.eq(index + 2).focus().select(); // +2 isliye kyunke aik row mein 2 inputs hain
    } else if (e.which === 38) { // Up Arrow
        e.preventDefault();
        if (index > 1) {
            inputs.eq(index - 2).focus().select();
        }
    }
});

// --- Decimal & Numeric Validation ---
$(document).on('input', '.qty-input, .meter-input', function() {
    let val = $(this).val();
    
    // Sirf numbers aur point allow karein (Regex)
    val = val.replace(/[^0-9.]/g, '');
    
    // Agar do bar point lagane ki koshish karein to block karein
    if ((val.match(/\./g) || []).length > 1) {
        val = val.substring(0, val.lastIndexOf("."));
    }

    // Point ke baad sirf 2 digits allow karein
    if (val.includes('.')) {
        let parts = val.split('.');
        if (parts[1].length > 2) {
            val = parts[0] + '.' + parts[1].substring(0, 2);
        }
    part0 = parts[0];
    }
    
    $(this).val(val);
});

// Cursor focus par text select kar le taake typing asaan ho
$(document).on('focus', '.qty-input, .meter-input', function() {
    $(this).select();
});
$(document).ready(function() {
    $('.select2-init').select2({ width: '100%' });

    // Excel Navigation for Single Column (Add Fuel Page)
    $(document).on('keydown', '.qty-input', function(e) {
        let inputs = $('.qty-input');
        let index = inputs.index(this);
        if (e.which === 13 || e.which === 40) { // Enter/Down
            e.preventDefault();
            inputs.eq(index + 1).focus().select();
        } else if (e.which === 38) { // Up
            e.preventDefault();
            inputs.eq(index - 1).focus().select();
        }
    });

    // Numeric Validation
    $(document).on('input', '.qty-input', function() {
        let val = $(this).val().replace(/[^0-9.]/g, '');
        if ((val.match(/\./g) || []).length > 1) val = val.substring(0, val.lastIndexOf("."));
        if (val.includes('.')) {
            let parts = val.split('.');
            if (parts[1].length > 2) val = parts[0] + '.' + parts[1].substring(0, 2);
        }
        $(this).val(val);
    });
});

function loadVehiclesForFuel(page = 1) {
    let data = {
        action: 'fetch_vehicles_fuel',
        date: $('#fuel_date').val(),
        district: $('#f_district').val(),
        tehsil: $('#f_tehsil').val(),
        v_type: $('#f_v_type').val(),
        vehicle_id: $('#f_vehicle_id').val(),
        limit: $('#page_limit').val(),
        page: page
    };

    $('#fuelTableContainer').fadeIn();
    $('#vehicleFuelBody').html('<tr><td colspan="4" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>');

    $.post('backend/fuel/fuel-process.php', data, function(res) {
        let response = JSON.parse(res);
        $('#vehicleFuelBody').html(response.html);
        $('#paginationLinks').html(response.pagination);
    });
}

function saveBulkFuel() {
    let fuelDate = $('#fuel_date').val();
    let formData = $('#vehicleFuelBody :input').serialize();
    if (!formData) return;

    Swal.fire({
        title: 'Save Fuel Entries?',
        text: "Date: " + fuelDate,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Save All'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('backend/fuel/fuel-process.php', formData + '&action=save_bulk_fuel&fuel_date=' + fuelDate, function(res) {
                if(res.trim() === 'success') {
                    Swal.fire('Saved!', 'Records updated successfully.', 'success');
                } else {
                    Swal.fire('Error', res, 'error');
                }
            });
        }
    });
}

function resetFuelFilters() {
    $('#fuelFilterForm')[0].reset();
    $('.select2-init').val('').trigger('change');
    $('#fuelTableContainer').hide();
}
// 1. Calculation Function
function calculateAddFuelTotals() {
    let totalFuel = 0;
    $('.qty-input').each(function() {
        totalFuel += parseFloat($(this).val()) || 0;
    });
    $('#page_total_fuel').text(totalFuel.toFixed(2));
}

