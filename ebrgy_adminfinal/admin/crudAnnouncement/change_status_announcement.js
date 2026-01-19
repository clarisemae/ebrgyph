async function changeAnnouncementStatus(announcementId, newStatus) {
    try {
        const formData = new FormData();
        formData.append('announcement_id', announcementId);
        formData.append('new_status', newStatus);

        const response = await fetch('crudAnnouncement/change_status_announcement.php', {
            method: 'POST',
            body: formData,
        });

        const rawText = await response.text();
        console.log('Raw server response:', rawText);

        const result = JSON.parse(rawText);
        console.log('Parsed response:', result);

        if (result.status === 'success') {
            alert(result.message);
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error updating announcement status:', error);
        alert('An unexpected error occurred.');
    }
}
