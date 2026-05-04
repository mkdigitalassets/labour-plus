// Bank Fields ko show/hide karne ka function
function toggleBankFields(show) {
    const bankFields = document.getElementById('bankFields');
    bankFields.style.display = show ? 'block' : 'none';
}

// Tehsil select hone par managers load karne ka logic
function filterManagers(tehsilId) {
    if (!tehsilId) return;

    var managerSelect = $('#managerSelect');
    managerSelect.html('<option value="">Loading...</option>');

    $.ajax({
        url: 'backend/expenses/manager-income-process.php',
        type: 'POST',
        data: {
            tehsil_id: tehsilId,
            action: 'fetch_managers' // Ye backend ko batata hai ke "FETCH" karna hai
        },
        success: function (response) {
            managerSelect.html(response); // Manager ke names yahan inject honge
        }
    });
}



// Is line ko dhyan se dekhen, ye "document" par listen karta hai
$(document).on("submit", "#managerIncomeForm", function (e) {
    e.preventDefault();

    // Yahan alert laga kar check karein ke button kaam kar raha hai
    // alert("Form submit ho raha hai!"); 

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    $.ajax({
        url: "backend/expenses/manager-income-process.php",
        type: "POST",
        data: $(this).serialize(),
        success: function (response) {
            if (response.trim() == "success") {
                Toast.fire({
                    icon: 'success',
                    title: 'Data saved successfully!'
                });
                $("#managerIncomeForm")[0].reset();
                // Agar history reload karni hai to:
                // loadContent('components/expenses/income-history.php');
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response
                });
            }
        },
        error: function () {
            Toast.fire({
                icon: 'error',
                title: 'Server connect nahi ho raha!'
            });
        }
    });
});

// --- 1. DELETE LOGIC ---
$(document).on("click", ".delete-btn", function () {
    let rowId = $(this).data("id");
    let rowElement = $(this).closest("tr"); // Row ko hide karne ke liye

    Swal.fire({
        title: 'ARE YOU SURE?',
        text: "YOU WANT TO DELETE THIS ENTRY!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'YES DELETE!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'backend/expenses/manager-income-process.php',
                type: 'POST',
                data: {
                    action: 'delete_income',
                    id: rowId
                },
                success: function (response) {
                    if (response.trim() == "success") {
                        Swal.fire('Deleted!', 'Record delete ', 'success');
                        rowElement.fadeOut(500); // Table se row gayab kar do
                    } else {
                        Swal.fire('Error!', response, 'error');
                    }
                }
            });
        }
    });
});

// --- 2. PRINT LOGIC ---
function printReceipt(id) {
    // Ye aik naya window khole ga jahan sirf receipt hogi
    window.open('components/expenses/print-receipt.php?id=' + id, '_blank', 'width=800,height=600');
}