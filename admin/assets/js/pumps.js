$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2-init').select2({ width: '100%' });
    }
});

function savePump() {
    const id = document.getElementById('p_id').value;
    const p_name = document.getElementById('p_name').value;
    const t_id = document.getElementById('p_tehsil').value;
    const o_name = document.getElementById('o_name').value;
    const o_phone = document.getElementById('o_phone').value;

    if (!p_name || !t_id || !o_name || !o_phone) {
        alert("Please fill required fields (Pump Name, Tehsil, Owner Name, and Phone)");
        return;
    }

    const formData = new FormData();
    formData.append('action', id ? 'update' : 'add');
    formData.append('pump_id', id);
    formData.append('pump_name', p_name);
    formData.append('tehsil_id', t_id);
    formData.append('owner_name', o_name);
    formData.append('owner_phone', o_phone);
    formData.append('owner_account', document.getElementById('o_acc').value);
    formData.append('owner_cnic', document.getElementById('o_cnic').value);

    fetch('backend/pump/process.php', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(data => {
        if(data.trim() === 'success') {
            loadContent('components/pump/pump.php');
        } else {
            alert(data);
        }
    });
}