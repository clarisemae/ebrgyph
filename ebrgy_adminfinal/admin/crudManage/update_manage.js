// Function to open the edit modal for a manage account
function openEditAccount(accountId) {
    const editModal = new bootstrap.Modal(document.getElementById('editAccountModal'));
    editModal.show();

    // Select the row using the correct ID
    const row = document.querySelector(`tr[data-id="${accountId}"]`);
    if (!row) {
        console.error(`Row not found for account ID: ${accountId}`);
        return;
    }

    // Populate modal fields with the correct data
    document.getElementById('adminName').value = row.cells[1].textContent.trim();
    document.getElementById('adminUsername').value = row.cells[2].textContent.trim();
    document.getElementById('email').value = row.cells[3].textContent.trim();
    document.getElementById('phone').value = row.cells[4].textContent.trim();
    document.getElementById('role').value = row.cells[5].textContent.trim();

    // Attach the record ID to the form for updating
    document.getElementById('editAccountForm').elements['id'].value = accountId;
}

// Update manage account
async function updateAccount() {
    const form = document.getElementById('editAccountForm');
    const formData = new FormData(form);

    try {
        const response = await fetch('crudManage/update_manage.php', {
            method: 'POST',
            body: formData,
        });

        const result = await response.json();
        if (result.status === 'success') {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message || 'Failed to update account.');
        }
    } catch (error) {
        console.error('Error updating account:', error);
        alert('An unexpected error occurred.');
    }
}
