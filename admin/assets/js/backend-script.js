
//District
    window.validateAndSave = function() {
        console.log("Save button clicked!"); // Check karne ke liye ke function call hua

        const form = document.getElementById('addDistrictForm');
        
        // Validation check
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        // Data collect karein
        const id = document.getElementById('d_id').value;
        const formData = new FormData();
        
        // Agar id hai to update mode, warna add mode
        formData.append('action', id ? 'update' : 'add');
        if(id) formData.append('id', id);
        
        formData.append('district_name', document.getElementById('d_name').value);
        formData.append('region_code', document.getElementById('d_code').value);
        formData.append('status', document.getElementById('d_status').value);

        // Backend ko data bhejein
        fetch('backend/district/process.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            console.log("Server Response:", data); // Check karein server kya keh raha hai
            if (data.trim() === "success") {
                alert(id ? "District Updated Successfully!" : "District Added Successfully!");
                loadContent('components/district/district.php'); // Wapas list page par le jaye
            } else {
                alert("Database Error: " + data);
            }
        })
        .catch(err => {
            console.error("Fetch Error:", err);
            alert("Server connection failed!");
        });
    };
    function deleteDist(id) {
    if(confirm('Delete record?')) {
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        fetch('backend/district/process.php', { method: 'POST', body: fd })
        .then(() => loadContent('components/district/district.php'));
    }
}

//Tehsils

window.saveTehsil = function() {
    const formData = new FormData();
    formData.append('action', 'add_tehsil');
    formData.append('tehsil_name', document.getElementById('t_name').value);
    formData.append('district_id', document.getElementById('t_district').value);

    fetch('backend/tehsil/process_tehsil.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === "success") {
            alert("Tehsil Added!");
            loadContent('components/tehsil/tehsil.php');
        } else {
            alert("Error: " + data);
        }
    });
};
window.filterTehsils = function() {
    let filter = document.getElementById('filterDistrict').value.toUpperCase();
    let rows = document.querySelector("#tehsilTable tbody").rows;
    for (let i = 0; i < rows.length; i++) {
        let districtCol = rows[i].cells[2].textContent.toUpperCase();
        rows[i].style.display = (filter === "" || districtCol.indexOf(filter) > -1) ? "" : "none";
    }
}