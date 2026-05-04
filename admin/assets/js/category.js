// ==========================================
// 1. TOAST CONFIGURATION (Top Right)
// ==========================================
const Toast = Swal.mixin({
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

$(document).ready(function () {
    // Select2 Initialization
    $('.select2, .select2-inline').select2({
        width: '100%',
        placeholder: "Select an option",
        allowClear: true
    });

    // Edit Mode check for Sub-Category
    var initialType = $('#typeSelect').val();
    if (initialType) {
        fetchCategoriesInline(initialType);
    }
});

// ==========================================
// 2. CATEGORY TYPE FUNCTIONS
// ==========================================
$(document).off('submit', '#categoryTypeForm').on('submit', '#categoryTypeForm', function (e) {
    e.preventDefault();
    let form = $(this);
    let btn = $('#submitBtn');

    $.ajax({
        url: 'backend/category-type/category-type-process.php',
        type: 'POST',
        data: form.serialize(),
        beforeSend: function () {
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
        },
        success: function (res) {
            if (res.trim() == 'success' || res.trim() == 'updated') {
                Toast.fire({ icon: 'success', title: 'Category Type saved successfully!' });
                loadContent('components/category-type/category-type.php');
            } else {
                Toast.fire({ icon: 'error', title: res });
                btn.prop('disabled', false).html('Save Record');
            }
        },
        error: function () {
            Toast.fire({ icon: 'error', title: 'Path error or Server error!' });
            btn.prop('disabled', false).html('Save Record');
        }
    });
});

// CATEGORY TYPE DELETE
function deleteCategoryType(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this category type?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('backend/category-type/category-type-process.php', {
                action: 'delete_category_type',
                type_id: id
            }, function (res) {
                if (res.trim() == 'deleted') {
                    Toast.fire({ icon: 'success', title: 'Record deleted!' });
                    loadContent('components/category-type/category-type.php');
                } else {
                    Toast.fire({ icon: 'error', title: res });
                }
            });
        }
    });
}

// ==========================================
// 3. CATEGORY FUNCTIONS
// ==========================================
$(document).off('submit', '#addCategoryForm').on('submit', '#addCategoryForm', function (e) {
    e.preventDefault();
    let btn = $(this).find('button[type="submit"]');

    $.ajax({
        url: "backend/category/category-process.php",
        type: "POST",
        data: $(this).serialize(),
        beforeSend: function () {
            btn.attr('disabled', true).html('Saving...');
        },
        success: function (res) {
            if (res.trim() === "success") {
                Toast.fire({ icon: 'success', title: 'Category saved successfully!' });
                loadContent('components/category/category.php');
            } else {
                Toast.fire({ icon: 'error', title: res });
                btn.attr('disabled', false).html('Save Category');
            }
        }
    });
});

// CATEGORY DELETE
function deleteCategory(id) {
    Swal.fire({
        title: 'Delete Category?',
        text: "This will remove the category permanently!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('backend/category/category-process.php', {
                action: 'delete_category',
                category_id: id
            }, function (res) {
                if (res.trim() === 'success') {
                    Toast.fire({ icon: 'success', title: 'Category removed!' });
                    loadContent('components/category/category.php');
                } else {
                    Toast.fire({ icon: 'error', title: res });
                }
            });
        }
    });
}

// ==========================================
// 4. SUB-CATEGORY FUNCTIONS
// ==========================================

$(document).ready(function () {

    $('.select2-inline').select2({
        width: '100%'
    });

    // EDIT MODE AUTO LOAD
    let initialType = $('#typeSelect').val();
    let savedCatId = $('#finalSubCategoryForm').attr('data-cat-id');

    if (initialType !== '' && savedCatId !== '') {

        // page load ke baad automatic category load
        setTimeout(function () {
            fetchCategoriesInline(initialType, savedCatId);
        }, 300);

    }
});


function fetchCategoriesInline(typeId, selectedId = null) {

    if (!typeId) {
        $('#categorySelect')
            .html('<option value="">Select type first...</option>')
            .trigger('change');
        return;
    }

    $.ajax({
        url: 'backend/category/sub_category_process.php',
        type: 'POST',
        data: {
            action: 'fetch_categories',
            type_id: typeId,
            selected_id: selectedId
        },
        success: function (data) {

            $('#categorySelect').html(data);

            // Select2 refresh
            $('#categorySelect').trigger('change');
        }
    });
}


$(document).off('submit', '#finalSubCategoryForm').on('submit', '#finalSubCategoryForm', function (e) {

    e.preventDefault();

    let formData = $(this).serialize();

    $.post(
        'backend/category/sub_category_process.php',
        formData,
        function (res) {

            if (res.trim() === "success") {

                Toast.fire({
                    icon: 'success',
                    title: 'Sub-Category saved!'
                });

                loadContent(
                    'components/category/sub-category-list.php'
                );

            } else {

                Toast.fire({
                    icon: 'error',
                    title: res
                });
            }
        }
    );
});


function deleteSubCategory(id) {

    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this sub-category?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    })

        .then((result) => {

            if (result.isConfirmed) {

                $.post(
                    'backend/category/sub_category_process.php',
                    {
                        action: 'delete_sub_category',
                        sub_id: id
                    },
                    function (res) {

                        if (res.trim() === "success") {

                            Toast.fire({
                                icon: 'success',
                                title: 'Sub-Category deleted!'
                            });

                            loadContent(
                                'components/category/sub-category-list.php'
                            );

                        } else {

                            Toast.fire({
                                icon: 'error',
                                title: res
                            });
                        }
                    }
                );
            }
        });
}

// ==========================================
// 5. UTILITY FUNCTIONS (Search)
// ==========================================
$("#typeSearch").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#typeTable tbody tr").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
});