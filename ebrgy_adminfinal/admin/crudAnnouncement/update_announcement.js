// Open the Edit Modal for Announcement
function openEditAnnouncement(announcementId) {
    const editModal = new bootstrap.Modal(document.getElementById('editAnnouncementModal'));
    editModal.show();

    // Select the row using the correct ID
    const row = document.querySelector(`tr[data-id="${announcementId}"]`);
    if (!row) {
        console.error(`Row not found for announcement ID: ${announcementId}`);
        return;
    }

    // Populate modal fields with the correct data
    document.getElementById('edit_announcement_id').value = announcementId;
    document.getElementById('edit_title').value = row.cells[1].textContent.trim();
    document.getElementById('edit_description').value = row.cells[2].textContent.trim();
    document.getElementById('edit_date').value = row.cells[3].textContent.trim();

    // Retrieve the image source from the <img> element inside the <td>
    const imageElement = row.cells[4].querySelector('img');
    if (imageElement) {
        const imagePath = imageElement.getAttribute('src'); // Get the src attribute of the image
        document.getElementById('edit_image_preview').src = imagePath; // Set the preview image in the modal
    } else {
        console.error('Image not found in the selected row.');
    }
}


// Update Announcement
async function updateAnnouncement() {
    const form = document.getElementById('editAnnouncementForm');
    const formData = new FormData(form);

    try {
        const response = await fetch('crudAnnouncement/update_announcement.php', {
            method: 'POST',
            body: formData,
        });

        const result = await response.json();
        if (result.status === 'success') {
            alert(result.message);
            location.reload(); // Reload the page to show updated data
        } else {
            alert(result.message || 'Failed to update announcement.');
        }
    } catch (error) {
        console.error('Error updating announcement:', error);
        alert('An unexpected error occurred.');
    }
}
