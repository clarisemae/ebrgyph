// Fetch blotters
async function fetchBlotters() {
    try {
        const response = await fetch('crudBlotter/fetch_blotter.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const blotters = await response.json();
        renderBlotterTable(blotters);
    } catch (error) {
        console.error('Error fetching blotters:', error);
    }
}

function renderBlotterTable(blotters) {
    const tableBody = document.getElementById('blotter-table-body');
    tableBody.innerHTML = ''; // Clear existing rows

    blotters.forEach((blotter) => {
        const row = `
            <tr data-id="${blotter.id}">
                <td>${blotter.row_number}</td>
                <td>${blotter.complainant}</td>
                <td>${blotter.accused}</td>
                <td>${blotter.incident_type}</td>
                <td>${blotter.other_incident !== null ? blotter.other_incident : "null"}</td> <!-- Show null explicitly -->
                <td>${blotter.incident_address}</td>
                <td>${blotter.date}</td>
                <td>${blotter.time}</td>
                <td>${blotter.message}</td>
                <td>
                  ${
                    blotter.incident_photo
                      ? `<img src="${blotter.incident_photo}" 
                             alt="Incident Photo" 
                             class="img-thumbnail clickable-image" 
                             data-bs-toggle="modal" 
                             data-bs-target="#photoModal-${blotter.id}">`
                      : `<p>No Photo</p>`
                  }
                </td>
                <td>${blotter.created_at}</td>
                <td>
                    <button class="btn btn-info" onclick="openEditBlotter(${blotter.id})">Edit</button>
                    <button class="btn btn-danger" onclick="deleteBlotter(${blotter.id})">Delete</button>
                    <button class="btn btn-warning" onclick="printBlotter(${blotter.id})">Print</button>
                    </td>
            </tr>
        `;
        tableBody.innerHTML += row;

        // Dynamically append the modal for the photo
        if (blotter.incident_photo) {
            const modal = `
                <div class="modal fade" id="photoModal-${blotter.id}" tabindex="-1" aria-labelledby="photoModalLabel-${blotter.id}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="photoModalLabel-${blotter.id}">Incident Photo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <img src="crudBlotter/uploads/${blotter.incident_photo}" alt="Incident Full Image" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modal);
        }
    });
}

document.addEventListener('DOMContentLoaded', fetchBlotters);
