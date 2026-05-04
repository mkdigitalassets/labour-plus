$(document).ready(function () {
    const SwalToast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // 1. District Change par Tehsil Load karna (FIXED)
    $(document).off('change', '#dist_select').on('change', '#dist_select', function () {
        let distId = $(this).val();
        let tehsilDropdown = $('#teh_select');

        if (distId) {
            tehsilDropdown.html('<option>Loading...</option>');
            $.post('backend/company/company-process.php', {
                action: 'get_tehsils',
                district_id: distId
            }, function (data) {
                tehsilDropdown.html(data);
                // Agar Select2 use ho raha hai to use refresh karein
                if ($('.select2').length > 0) {
                    $('.select2').select2({ width: '100%' });
                }
            });
        } else {
            tehsilDropdown.html('<option value="">First Select District</option>');
        }
    });

    // 2. Form Submit (Add & Edit)
    $(document).off('submit', '#companyForm').on('submit', '#companyForm', function (e) {
        e.preventDefault();
        let formData = $(this).serialize();
        $.ajax({
            url: 'backend/company/company-process.php',
            type: 'POST',
            data: formData + '&action=save_company',
            success: function (res) {
                let response = res.trim();
                if (response === 'success' || response === 'updated') {
                    SwalToast.fire({ icon: 'success', title: 'Saved Successfully!' });
                    setTimeout(() => loadContent('components/company/company.php'), 500);
                } else {
                    SwalToast.fire({ icon: 'error', title: 'Error: ' + response });
                }
            }
        });
    });

    // 3. Table Filtering logic
    $(document).on('keyup change', '#comp_companySearch, #comp_filterDistrict, #comp_filterTehsil, #comp_filterStatus', function () {
        if ($('#companyTableBody').length === 0) return;

        var searchText = $('#comp_companySearch').val().toLowerCase().trim();
        var filterDist = $('#comp_filterDistrict').val().toLowerCase().trim();
        var filterTehsil = $('#comp_filterTehsil').val().toLowerCase().trim();
        var filterStatus = $('#comp_filterStatus').val().toLowerCase().trim();

        $('#companyTableBody tr').each(function () {
            var row = $(this);
            var rowName = row.find('td:nth-child(2)').text().toLowerCase();
            var rowDist = row.find('td:nth-child(3)').text().toLowerCase();
            var rowTehsil = row.find('td:nth-child(4)').text().toLowerCase();
            var rowStatus = row.find('td:nth-child(5)').text().toLowerCase();

            var matchSearch = (searchText === "") || (rowName.indexOf(searchText) > -1);
            var matchDist = (filterDist === "") || (rowDist.indexOf(filterDist) > -1);
            var matchTehsil = (filterTehsil === "") || (rowTehsil.indexOf(filterTehsil) > -1);
            var matchStatus = (filterStatus === "") || (rowStatus.indexOf(filterStatus) > -1);

            if (matchSearch && matchDist && matchTehsil && matchStatus) row.show();
            else row.hide();
        });
    });
});

// 4. List Page Filters ke liye Tehsil fetch karna
function fetchTehsilsForCompanyFilter(districtName) {
    var tehsilDropdown = $('#comp_filterTehsil');
    if (!districtName) {
        tehsilDropdown.html('<option value="">All Tehsils</option>');
        return;
    }
    $.post('backend/company/company-process.php', {
        action: 'fetch_tehsils_by_name',
        district_name: districtName
    }, function (html) {
        tehsilDropdown.html('<option value="">All Tehsils</option>' + html);
    });
}

// 5. Delete Company Function
window.deleteCompany = function (id) {
    Swal.fire({
        title: 'Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('backend/company/company-process.php', { action: 'delete_company', company_id: id }, function (res) {
                if (res.trim() == 'deleted') {
                    loadContent('components/company/company.php');
                }
            });
        }
    });
};