$(document).ready(function() {

    // ==========================================
    // 1. FILTERS & SEARCH LOGIC (Vehicle Type)
    // ==========================================
    $(document).on('keyup change', '#vt_search, #vt_fuelFilter, #vt_statusFilter', function() {
        
        let searchText = $('#vt_search').val().toLowerCase().trim();
        let fuelFilter = $('#vt_fuelFilter').val().toLowerCase().trim();
        let statusFilter = $('#vt_statusFilter').val().toLowerCase().trim();

        // Table body ki rows par loop
        $('#vehicleTypeTable tr').not('#no-results-row').each(function() {
            let row = $(this);
            
            // Column data mapping
            let typeName = row.find('td:nth-child(2)').text().toLowerCase();
            let fuelType = row.find('td:nth-child(3)').text().toLowerCase().trim();
            let status   = row.find('td:nth-child(4)').text().toLowerCase().trim();

            // Comparison Logic
            let matchSearch = (searchText === "") || (typeName.indexOf(searchText) > -1);
            let matchFuel   = (fuelFilter === "") || (fuelType === fuelFilter);
            let matchStatus = (statusFilter === "") || (status === statusFilter);

            // Row display toggle
            if (matchSearch && matchFuel && matchStatus) {
                row.show();
            } else {
                row.hide();
            }
        });

        // --- NO RECORD FOUND LOGIC ---
        // Pehle se mojud error message ko remove karein
        $('#no-results-row').remove();

        // Check karein ke kya saari rows hidden hain
        if ($('#vehicleTypeTable tr:visible').length === 0) {
            $('#vehicleTypeTable').append('<tr id="no-results-row"><td colspan="5" class="text-center py-4 text-muted">No matching records found.</td></tr>');
        }
    });


    // ==========================================
    // 2. VEHICLE TYPE FORM SUBMISSION
    // ==========================================
    $(document).on('submit', '#vTypeForm', function(e) {
        e.preventDefault();
        
        let btn = $('#saveBtn');
        let btnText = $('#btnText');
        
        btn.prop('disabled', true).css('opacity', '0.7');
        btnText.text('Saving...');

        $.ajax({
            url: 'backend/vehicle_type/vehicle-type-process.php',
            type: 'POST',
            data: $(this).serialize() + '&action=save_vtype',
            success: function(res) {
                let response = res.trim();
                if(response === 'success' || response === 'updated') {
                    alert('Success: Data saved successfully!');
                    loadContent('components/vehicle_type/vehicle-type.php');
                } else {
                    alert('Server Error: ' + response);
                    btn.prop('disabled', false).css('opacity', '1');
                    btnText.text('Save Vehicle Type');
                }
            },
            error: function() {
                alert('Critical Error: Request failed.');
                btn.prop('disabled', false).css('opacity', '1');
                btnText.text('Save Vehicle Type');
            }
        });
    });


    // ==========================================
    // 3. VEHICLE FORM (MAIN) & DEPENDENCIES
    // ==========================================
    
    $(document).on('change', '#v_dist', function() {
        let district_id = $(this).val();
        if(district_id) {
            $.post('backend/vehicle/vehicle-process.php', { action: 'get_tehsils', district_id: district_id }, function(html) {
                $('#v_teh').html(html);
                $('#v_comp').html('<option value="">Select Company</option>');
            });
        }
    });

    $(document).on('change', '#v_teh', function() {
        let tehsil_id = $(this).val();
        if(tehsil_id) {
            $.post('backend/vehicle/vehicle-process.php', { action: 'get_companies', tehsil_id: tehsil_id }, function(html) {
                $('#v_comp').html(html);
            });
        }
    });

    $(document).on('change', '#rent_cat', function() {
        if($(this).val() !== 'Non-Rental') {
            $('#rental_fields').slideDown().css('display', 'flex');
        } else {
            $('#rental_fields').slideUp();
            $('input[name="company_rent"], input[name="lp_rent"]').val(0);
        }
    });

    $(document).on('submit', '#vehicleForm', function(e) {
        e.preventDefault();
        let formData = $(this).serialize() + '&action=save_vehicle';
        
        $.ajax({
            url: 'backend/vehicle/vehicle-process.php',
            type: 'POST',
            data: formData,
            success: function(res) {
                if(res.trim() == 'success' || res.trim() == 'updated') {
                    $('#vehicleModal').modal('hide');
                    alert('Data saved successfully!');
                    loadContent('components/vehicle/vehicle.php');
                } else {
                    alert('Error: ' + res);
                }
            }
        });
    });

});

// ==========================================
// 4. GLOBAL FUNCTIONS (Window Scope)
// ==========================================

window.deleteVType = function(id) {
    if(confirm('Are you sure you want to delete this vehicle type?')) {
        $.post('backend/vehicle_type/vehicle-type-process.php', { action: 'delete_vtype', v_type_id: id }, function(res) {
            if(res.trim() === 'deleted') {
                loadContent('components/vehicle_type/vehicle-type.php');
            } else {
                alert('Error deleting: ' + res);
            }
        });
    }
};

window.deleteVehicle = function(id) {
    if(confirm('Remove this vehicle?')) {
        $.post('backend/vehicle/vehicle-process.php', {action:'delete_vehicle', vehicle_id:id}, function(res) {
            if(res.trim()=='deleted') loadContent('components/vehicle/vehicle.php');
        });
    }
};

window.openVehicleModal = function(id = '') {
    $('#modalBody').load('components/vehicle/add-vehicle.php?id=' + id, function() {
        $('#vehicleModal').modal('show');
    });
};