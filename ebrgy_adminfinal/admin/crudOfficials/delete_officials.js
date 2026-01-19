// Delete official
async function deleteOfficial(officialId) {
    if (confirm('Are you sure you want to delete this official?')) {
        const formData = new FormData();
        formData.append('official_id', officialId);
        formData.append('delete_official', true);

        try {
            const response = await fetch('crudOfficials/delete_officials.php', {
                method: 'POST',
                body: formData,
            });

            const result = await response.text(); // Fetch the server response
            alert(result); // Display the response
            location.reload(); // Reload the page to reflect the changes
        } catch (error) {
            console.error('Error deleting official:', error);
            alert('An error occurred while trying to delete the official.');
        }
    }
}