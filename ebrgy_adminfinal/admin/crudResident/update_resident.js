// Open the edit modal
function openEditResident(residentId) {
    const editModal = new bootstrap.Modal(document.getElementById('editResidentModal'));
    editModal.show();

    // Select the corresponding table row
    const row = document.querySelector(`tr[data-id="${residentId}"]`);
    if (!row) {
        console.error(`Row not found for resident ID: ${residentId}`);
        return;
    }

    // Populate modal fields
    document.getElementById('resident_id').value = residentId;
    document.getElementById('full_name').value = row.cells[1].textContent.trim();
    document.getElementById('age').value = row.cells[2].textContent.trim(); // Include age
    document.getElementById('address').value = row.cells[3].textContent.trim();
    document.getElementById('gender').value = row.cells[4].textContent.trim();
    document.getElementById('sector').value = row.cells[5].textContent.trim();
    document.getElementById('citizenship').value = row.cells[6].textContent.trim();
}

// Update resident
async function updateResident() {
    const formData = new FormData(document.getElementById('editResidentForm'));

    console.log('Form Data to be Sent:');
    for (const [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }

    try {
        const response = await fetch('crudResident/update_resident.php', {
            method: 'POST',
            body: formData,
        });

        const result = await response.json();
        console.log('Update Response:', result); // Debug the response
        alert(result.message);

        if (result.status === 'success') {
            location.reload();
        }
    } catch (error) {
        console.error('Error updating resident:', error);
        alert('Failed to update the resident.');
    }
}
