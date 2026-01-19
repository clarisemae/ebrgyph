// Open Edit Modal and Populate Fields
function openEditCertificate(certificateId) {
    const editModal = new bootstrap.Modal(document.getElementById('editRecordModal'));
    editModal.show();

    const row = document.querySelector(`tr[data-id="${certificateId}"]`);
    if (!row) {
        console.error(`Row not found for certificate ID: ${certificateId}`);
        return;
    }

    // Populate modal fields
    document.getElementById('edit_id').value = certificateId;
    document.getElementById('edit_fullname').value = row.cells[2].textContent.trim();
    document.getElementById('edit_age').value = row.cells[3].textContent.trim();
    document.getElementById('edit_status').value = row.cells[4].textContent.trim();
    document.getElementById('edit_citizen').value = row.cells[5].textContent.trim();
    document.getElementById('edit_address').value = row.cells[6].textContent.trim();
    document.getElementById('edit_requested_date').value = row.cells[7].textContent.trim();
    document.getElementById('edit_email').value = row.cells[8].textContent.trim();
    document.getElementById('edit_barangay_certificate_purpose').value = row.cells[9].textContent.trim();
    document.getElementById('edit_barangay_other_details').value = row.cells[10].textContent.trim();
    document.getElementById('edit_id_type').value = row.cells[11].textContent.trim();

    const imageCell = row.cells[12].querySelector('img');
    if (imageCell) {
        const imagePath = 'crudBrgyCertificate/uploads/';  // Adjust the path as necessary
        document.getElementById('current_id_photo').src = imagePath + imageCell.src.split('/').pop();
    } else {
        document.getElementById('current_id_photo').src = '';
    }
}

// Update Certificate
async function updateCertificate() {
    const form = document.getElementById('editRecordForm');
    const formData = new FormData(form);

    try {
        const response = await fetch('crudBrgyCertificate/update_cert.php', {
            method: 'POST',
            body: formData,
        });

        const result = await response.json();
        console.log('Response Data:', result);

        if (result.status === 'success') {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message || 'Failed to update the certificate.');
        }
    } catch (error) {
        console.error('Error updating certificate:', error);
        alert('An unexpected error occurred. Check the console for more details.');
    }
}
