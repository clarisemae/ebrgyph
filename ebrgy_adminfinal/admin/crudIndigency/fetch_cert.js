// Fetch Barangay Certificates
async function fetchCertificates() {
  try {
    const response = await fetch("crudIndigency/fetch_cert.php"); // Adjust path as needed
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }
    const certificates = await response.json();
    renderCertificateTable(certificates);
  } catch (error) {
    console.error("Error fetching certificates:", error);
  }
}

function renderCertificateTable(certificates) {
  const tableBody = document.getElementById("certificate-table-body");
  tableBody.innerHTML = ""; // Clear existing rows

  certificates.forEach((cert) => {
    const row = `
            <tr data-id="${cert.id}">
                <td>${cert.row_number}</td>
                <td>${cert.document_type}</td>
                <td>${cert.fullname}</td>
                <td>${cert.age}</td>
                <td>${cert.status}</td>
                <td>${cert.citizen}</td>
                <td>${cert.address}</td>
                <td>${cert.requested_date}</td>
                <td>${cert.email}</td>
                <td>${cert.indigency_purpose || "N/A"}</td>
                <td>${cert.indigency_other_details || "N/A"}</td>
                <td>${cert.id_type}</td>
                <td>
                    ${
                      cert.id_photo_url
                        ? `<img src="${cert.id_photo_url}" 
                            alt="ID Photo" 
                            class="img-thumbnail clickable-image" 
                            data-bs-toggle="modal" 
                            data-bs-target="#photoModal-${cert.id}" 
                            style="width: 100px; cursor: pointer;">`
                        : `<p>No Photo</p>`
                    }
                </td>
                <td>${cert.created_at}</td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="openEditCertificate(${
                          cert.id
                        })">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteCertificate(${
                          cert.id
                        })">Delete</button>
                        <button class="btn btn-warning btn-sm" onclick="printCertificate(${
                          cert.id
                        })">Print</button>
                        <input type="checkbox" 
                          class="form-check-input" 
                          id="doneCheckbox-${cert.id}" 
                          ${cert.is_checked === "1" ? "checked" : ""} 
                          onchange="markAsDone(${cert.id}, this.checked)">
                    </td>
            </tr>
        `;

    tableBody.innerHTML += row;

    // Dynamically add a modal for the ID photo
    if (cert.id_photo_url) {
      const modal = `
                <div class="modal fade" id="photoModal-${cert.id}" tabindex="-1" aria-labelledby="photoModalLabel-${cert.id}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="photoModalLabel-${cert.id}">ID Photo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <img src="crudIndigency/${cert.id_photo_url}" alt="Full ID Photo" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            `;
      document.body.insertAdjacentHTML("beforeend", modal);
    }
  });
}

async function markAsDone(id, isChecked) {
  try {
    const response = await fetch("sendIndCert.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id, is_checked: isChecked ? 1 : 0 }), // Send data as JSON
    });

    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    const data = await response.json();
    console.log("Server Response:", data);

    if (data.success) {
      console.log(`Checkbox for ID ${id} successfully updated.`);
    } else {
      console.error("Error:", data.message);
    }
  } catch (error) {
    console.error("Error in markAsDone:", error);
  }
}

// Call fetchCertificates when the DOM is fully loaded
document.addEventListener("DOMContentLoaded", fetchCertificates);