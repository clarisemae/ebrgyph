async function deleteAnnouncement(announcementId) {
    if (confirm('Are you sure you want to delete this announcement?')) {
        const formData = new FormData();
        formData.append('announcement_id', announcementId);
        formData.append('delete_announcement', true);

        try {
            console.log('Deleting announcement with ID:', announcementId);

            const response = await fetch('crudAnnouncement/delete_announcement.php', {
                method: 'POST',
                body: formData,
            });

            const rawText = await response.text();
            console.log('Raw server response:', rawText);

            const result = JSON.parse(rawText);
            console.log('Parsed response:', result);

            if (result.status === 'success') {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message);
                console.error('Error:', result.message);
            }
        } catch (error) {
            console.error('Error deleting announcement:', error);
            alert('An unexpected error occurred.');
        }
    }
}
