async function fetchOfficials() {
    try {
        const response = await fetch('crudOfficials/fetch_officials.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const officials = await response.json();
        renderOfficialsTable(officials);
    } catch (error) {
        console.error('Error fetching officials:', error);
    }
}

function renderOfficialsTable(officials) {
    const tableBody = document.getElementById('officials-table-body');
    tableBody.innerHTML = ''; // Clear existing rows

    officials.forEach((official) => {
        const row = `
            <tr data-id="${official.id}">
                <td>${official.row_number}</td>
                <td>
                  ${official.photo
                      ? `<img src="crudOfficials/${official.photo}" 
                             alt="Official Photo" 
                             class="img-thumbnail clickable-image" 
                             data-bs-toggle="modal" 
                             data-bs-target="#photoModal-${official.id}">`
                      : `<p>No Photo</p>`
                  }
                </td>
                <td>${official.name}</td>
                <td>${official.role}</td>
                <td>
                    <button class="btn btn-info" onclick="openEditOfficial(${official.id})">Edit</button>
                    <button class="btn btn-danger" onclick="deleteOfficial(${official.id})">Delete</button>
                </td>
            </tr>
        `;
        tableBody.innerHTML += row;

        // Dynamically append the modal for the photo
        if (official.photo) {
            const modal = `
                <div class="modal fade" id="photoModal-${official.id}" tabindex="-1" aria-labelledby="photoModalLabel-${official.id}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="photoModalLabel-${official.id}">Barangay Official Photo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <img src="crudOfficials/${official.photo}" alt="Official Full Image" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modal);
        }
    });
}


document.addEventListener('DOMContentLoaded', fetchOfficials);
