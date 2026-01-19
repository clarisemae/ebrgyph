function openEditBlotter(blotterId) {
    const editModal = new bootstrap.Modal(document.getElementById('editBlotterModal'));
    editModal.show();

    // Select the row using the blotter ID
    const row = document.querySelector(`tr[data-id="${blotterId}"]`);
    if (!row) {
        console.error(`Row not found for blotter ID: ${blotterId}`);
        return;
    }

    // Populate modal fields
    document.getElementById('id').value = blotterId;
    document.getElementById('complainant').value = row.cells[1].textContent.trim();
    document.getElementById('accused').value = row.cells[2].textContent.trim();
    document.getElementById('incident_type').value = row.cells[3].textContent.trim();
    document.getElementById('other_incident').value = row.cells[4].textContent.trim();
    document.getElementById('incident_address').value = row.cells[5].textContent.trim();
    document.getElementById('date').value = row.cells[6].textContent.trim();
    document.getElementById('time').value = row.cells[7].textContent.trim();
    document.getElementById('message').value = row.cells[8].textContent.trim();
}




async function updateBlotter() {
    const form = document.getElementById('editBlotterForm');
    const formData = new FormData(form);

    // Debugging: Log form data to verify
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    try {
        const response = await fetch('crudBlotter/update_blotter.php', {
            method: 'POST',
            body: formData,
        });

        const result = await response.json();
        console.log('Server Response:', result); // Log server response for debugging

        if (result.status === 'success') {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message || 'Failed to update blotter record.');
        }
    } catch (error) {
        console.error('Error updating blotter:', error);
        alert('An unexpected error occurred.');
    }
}

