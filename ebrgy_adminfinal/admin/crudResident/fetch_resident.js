async function fetchResidents() {
    try {
        const response = await fetch('crudResident/fetch_residents.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const residents = await response.json();
        renderResidentTable(residents);
    } catch (error) {
        console.error('Error fetching residents:', error);
    }
}

function renderResidentTable(residents) {
    const tableBody = document.getElementById('resident-table-body');
    tableBody.innerHTML = ''; // Clear existing rows

    residents.forEach((resident) => {
        const row = `
            <tr data-id="${resident.resident_id}">
                <td>${resident.row_number}</td>
                <td>${resident.full_name}</td>
                <td>${resident.age}</td>
                <td>${resident.address}</td>
                <td>${resident.gender}</td>
                <td>${resident.sector}</td>
                <td>${resident.citizenship}</td>
                <td>
                    <button class="btn btn-info" onclick="openEditResident(${resident.resident_id})">Edit</button>
                    <button class="btn btn-danger" onclick="deleteResident(${resident.resident_id})">Delete</button>
                </td>
            </tr>
        `;
        tableBody.innerHTML += row;
    });
}

document.addEventListener('DOMContentLoaded', fetchResidents);
