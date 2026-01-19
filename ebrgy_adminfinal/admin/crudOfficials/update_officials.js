// Open Edit Modal and Populate Fields for Barangay Official
function openEditOfficial(officialId) {
    const editModal = new bootstrap.Modal(document.getElementById('editOfficialModal'));
    editModal.show();

    const row = document.querySelector(`tr[data-id="${officialId}"]`);
    if (!row) {
        console.error(`Row not found for official ID: ${officialId}`);
        return;
    }

    // Populate modal fields with correct data
    document.getElementById('official_id').value = officialId;
    document.getElementById('name').value = row.cells[2].textContent.trim();  // Name field should be from 3rd column (index 2)
    document.getElementById('role').value = row.cells[3].textContent.trim();  // Role field should be from 4th column (index 3)

    // If the photo exists, set it in the modal preview
    const photoCell = row.cells[1].querySelector('img');
    if (photoCell) {
        const photoPath = 'crudOfficials/uploads/'; // Adjust path as necessary
        document.getElementById('current_photo').src = photoPath + photoCell.src.split('/').pop();
        document.getElementById('currentPhotoPreview').style.display = 'block';  // Show current photo preview
    } else {
        document.getElementById('currentPhotoPreview').style.display = 'none';  // Hide if no photo
    }
}


// Update Official
async function updateOfficial() {
    const form = document.getElementById('editOfficialForm');
    const formData = new FormData(form);

    try {
        const response = await fetch('crudOfficials/update_officials.php', {
            method: 'POST',
            body: formData,
        });

        const result = await response.json();
        console.log('Response Data:', result);

        if (result.status === 'success') {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message || 'Failed to update the official.');
        }
    } catch (error) {
        console.error('Error updating official:', error);
        alert('An unexpected error occurred. Check the console for more details.');
    }
}