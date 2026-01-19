async function fetchUsers() {
    try {
        const response = await fetch('crudUserAccount/fetch_user.php');
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const users = await response.json();
        renderUserTable(users);
    } catch (error) {
        console.error('Error fetching users:', error);
    }
}

function renderUserTable(users) {
    const tableBody = document.getElementById('user-table-body');
    tableBody.innerHTML = ''; // Clear existing rows

    users.forEach((user) => {
        const row = `
            <tr data-id="${user.id}">
                <td>${user.id}</td>
                <td>${user.full_name}</td>
                <td>${user.birthdate || 'N/A'}</td>
                <td>${user.gender}</td>
                <td>${user.civil_status}</td>
                <td>${user.email}</td>
                <td>${user.phone || 'N/A'}</td>
                <td>${user.street}, ${user.barangay}, ${user.municipality}, ${user.city}, ${user.region}</td>
                <td>${user.emergency_name || 'N/A'}</td>
                <td>${user.emergency_address || 'N/A'}</td>
                <td>${user.emergency_relationship || 'N/A'}</td>
                <td>${user.emergency_phone || 'N/A'}</td>
                <td>${user.created_at}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})">Delete</button>
                </td>
            </tr>
        `;
        tableBody.innerHTML += row;
    });
}

document.addEventListener('DOMContentLoaded', fetchUsers);