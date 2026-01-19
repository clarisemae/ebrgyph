// Delete manage record
async function deleteManage(manageId) {
  if (confirm('Are you sure you want to delete this admin account?')) {
      const formData = new FormData();
      formData.append('admin_id', manageId);
      formData.append('delete_manage', true);

      try {
          const response = await fetch('crudManage/delete_manage.php', {
              method: 'POST',
              body: formData,
          });

          const result = await response.text();
          alert(result); // Display the server response
          location.reload(); // Reload the page to reflect the changes
      } catch (error) {
          console.error('Error deleting admin account:', error);
      }
  }
}
