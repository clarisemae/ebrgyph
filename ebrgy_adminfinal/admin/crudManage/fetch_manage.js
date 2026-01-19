// Fetch admin accounts
async function fetchManageAccounts() {
    try {
        const response = await fetch('crudManage/fetch_manage.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const manageAccounts = await response.json();
        renderManageTable(manageAccounts);
    } catch (error) {
        console.error('Error fetching admin accounts:', error);
    }
}

function renderManageTable(manageAccounts) {
    const tableBody = document.getElementById('manage-table-body');
    tableBody.innerHTML = ''; // Clear existing rows

    manageAccounts.forEach((account) => {
        const row = `
            <tr data-id="${account.id}">
                <td>${account.row_number}</td>
                <td>${account.adminName}</td>
                <td>${account.adminUsername}</td>
                <td>${account.email}</td>
                <td>${account.phone}</td>
                <td>${account.role}</td>
                <td>${account.created}</td>
                <td>${account.updated}</td>
                <td>
                    <button class="btn btn-info" onclick="openEditAccount(${account.id})">Edit</button>
                    <button class="btn btn-danger" onclick="deleteManage(${account.id})">Delete</button>
                </td>
            </tr>
        `;
        tableBody.innerHTML += row;
    });
}

document.addEventListener('DOMContentLoaded', fetchManageAccounts);
