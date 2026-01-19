// Delete blotter record
async function deleteBlotter(blotterId) {
    if (confirm('Are you sure you want to delete this blotter record?')) {
        const formData = new FormData();
        formData.append('blotter_id', blotterId);
        formData.append('delete_blotter', true);

        try {
            const response = await fetch('crudBlotter/delete_blotter.php', {
                method: 'POST',
                body: formData,
            });

            const result = await response.text();
            alert(result); // Display the server response
            location.reload(); // Reload the page to reflect the changes
        } catch (error) {
            console.error('Error deleting blotter record:', error);
        }
    }
}
