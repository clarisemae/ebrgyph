// Fetch announcements
async function fetchAnnouncements() {
    try {
        const response = await fetch('crudAnnouncement/fetch_announcement.php');
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const announcements = await response.json();

        if (announcements.error) {
            console.error('Server Error:', announcements.error);
            return;
        }

        renderAnnouncementTable(announcements);
    } catch (error) {
        console.error('Error fetching announcements:', error);
    }
}

// Render the table with announcements
function renderAnnouncementTable(announcements) {
    const tableBody = document.getElementById('announcement-table-body');
    tableBody.innerHTML = ''; // Clear existing rows

    announcements.forEach((announcement) => {
        const row = `
            <tr data-id="${announcement.announcement_id}">
                <td>${announcement.row_number}</td> <!-- Use row_number from the backend -->
                <td>${announcement.title}</td>
                <td>${announcement.description}</td>
                <td>${announcement.date}</td>
                <td>
                    <img src="crudAnnouncement/uploads/${announcement.image}" 
                         alt="Announcement Image" 
                         class="table-image clickable-image" 
                         data-bs-toggle="modal" 
                         data-bs-target="#imageModal-${announcement.announcement_id}">
                </td>
                <td>
                    <select class="form-select" onchange="changeAnnouncementStatus(${announcement.announcement_id}, this.value)">
                        <option value="Active" ${announcement.status === 'Active' ? 'selected' : ''}>Active</option>
                        <option value="Inactive" ${announcement.status === 'Inactive' ? 'selected' : ''}>Inactive</option>
                    </select>
                </td>
                <td>
                    <button class="btn btn-info" onclick="openEditAnnouncement(${announcement.announcement_id})">Edit</button>
                    <button class="btn btn-danger" onclick="deleteAnnouncement(${announcement.announcement_id})">Delete</button>
                </td>
            </tr>
        `;
        tableBody.innerHTML += row;

        // Append a modal for each announcement
        const modal = `
            <div class="modal fade" id="imageModal-${announcement.announcement_id}" tabindex="-1" aria-labelledby="imageModalLabel-${announcement.announcement_id}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="imageModalLabel-${announcement.announcement_id}">Announcement Image</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <img src="crudAnnouncement/uploads/${announcement.image}" alt="Announcement Full Image" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modal);
    });
}

// Trigger fetch on page load
document.addEventListener('DOMContentLoaded', fetchAnnouncements);
