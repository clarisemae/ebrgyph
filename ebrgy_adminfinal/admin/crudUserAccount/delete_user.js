// Delete user
async function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('delete_user', true);

        try {
            const response = await fetch('crudUserAccount/delete_user.php', {
                method: 'POST',
                body: formData,
            });

            const result = await response.text(); // Fetch the server response
            alert(result); // Display the response
            location.reload(); // Reload the page to reflect the changes
        } catch (error) {
            console.error('Error deleting user:', error);
            alert('An error occurred while trying to delete the user.');
        }
    }
}
