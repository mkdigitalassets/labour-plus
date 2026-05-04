$(document).on('submit', '#addPumpForm', function (e) {
    e.preventDefault();

    // Form ka sara data akatha karna
    let formData = $(this).serialize();
    console.log("Sending Data:", formData); // Check karein console mein data show ho raha hai?

    $.ajax({
        url: "backend/pump/pump_process.php", // Path check karein: agar dashboard se call ho raha hai to yehi path hoga
        type: "POST",
        data: formData,
        beforeSend: function () {
            // Button ko disable kar dein taake double click na ho
            $('button[type="submit"]').attr('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i> Saving...');
        },
        success: function (response) {
            console.log("Server Response:", response);
            if (response.trim() === "success") {
                alert("Mubarak ho! Pump register ho gaya.");
                loadContent('components/pump/pump-detail.php'); // Wapis list par le jaye ga
            } else {
                alert("Backend Error: " + response);
            }
            $('button[type="submit"]').attr('disabled', false).html('<i class="ri-save-3-line me-1"></i> Save Pump Record');
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
            alert("Script ka masla hai! Check console.");
            $('button[type="submit"]').attr('disabled', false).html('Try Again');
        }
    });
});
function deletePump(id) {
    if(confirm('Are you sure? This action cannot be undone.')) {
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('pump_id', id);
        fetch('backend/pump/process.php', { method: 'POST', body: fd })
        .then(res => res.text())
        .then(data => {
            if(data.trim() === 'success') loadContent('components/pump/pump.php');
            else alert(data);
        });
    }
}