// Delete resident
async function deleteResident(residentId) {
    if (confirm('Are you sure you want to delete this resident?')) {
      const formData = new FormData();
      formData.append('resident_id', residentId);
      formData.append('delete_resident', true);
  
      try {
        const response = await fetch('crudREsident/delete_resident.php', {
          method: 'POST',
          body: formData,
        });
  
        const result = await response.text();
        alert(result); // Display the server response
        location.reload(); // Reload the page to reflect the changes
      } catch (error) {
        console.error('Error deleting resident:', error);
      }
    }
  }
  