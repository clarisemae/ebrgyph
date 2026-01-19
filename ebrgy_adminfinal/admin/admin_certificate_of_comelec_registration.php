<?php
session_start();

require_once './includes/access_control.php';
include './includes/db_connection.php';

// Check for valid login and role
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['Staff', 'Admin', 'SuperAdmin'])) {
    header("Location: admin_login.php");
    exit();
}

// For SuperAdmin: If they select a barangay, save it in session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barangay_selector'])) {
    $_SESSION['selected_barangay_id'] = $_POST['barangay_selector'];
    header("Location: admin_certificate_of_comelec_registration.php");
    exit();
}

$selectedBarangay = $_SESSION['selected_barangay_id'] ?? null;
$barangayIds = $_SESSION['barangay_ids'] ?? []; // SuperAdmin accessible barangays
$barangayId = $_SESSION['barangay_id'] ?? null; // Admin/Staff assigned barangay

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificate of Comelec Registration Records</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="includes/general.css">
  <link rel="stylesheet" href="includes/header.css">
  <link rel="stylesheet" href="includes/sidebar.css">
  <link rel="stylesheet" href="css/certificate.css">
</head>
<body>
  <?php include('includes/header.php'); ?>
  <?php include('includes/sidebar.php'); ?>

  <!-- Main Content Section -->
  <main class="main-container">
      <h1>
        <a href="document_records.php" class="breadcrumb-link">Document Request Record</a> /
        <span class="current-page">Certificate of Comelec Registration Records</span>
      </h1>

      <?php if ($_SESSION['role'] === 'SuperAdmin'): ?>
      <form method="POST" class="mb-4 w-25">
        <label for="barangay_selector" class="form-label">Choose Barangay:</label>
        <select name="barangay_selector" id="barangay_selector" class="form-select" onchange="this.form.submit()">
          <?php foreach ($barangayIds as $id):
            $barangayName = "Barangay " . htmlspecialchars($id); // Replace with actual names from DB if available
            $selected = ($id == ($selectedBarangay ?? $barangayIds[0])) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($id) ?>" <?= $selected ?>><?= $barangayName ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    <?php endif; ?>

    <!-- Certificate Records Table -->
    <div class="certificate-table-container">
      <div class="sort-options-container d-flex justify-content-between align-items-center">
        <div>
          <label for="sort-by-certificates" class="sort-label">Sort:</label>
          <select id="sort-by-certificates" class="form-control">
            <option value="default">Sort by Default(ID)</option>
            <option value="name">Sort by Name</option>
            <option value="purpose">Sort by Purpose</option>
            <option value="date">Sort by Date</option>
          </select>
        </div>
        <div class="search-icon-container">
          <i class="material-icons-outlined">search</i>
          <input type="text" id="search-certificates" class="form-control search-input" placeholder="Search..." aria-label="Search">
        </div>
      </div>

      <!-- Table -->
      <table class="certificate-table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Document Type</th>
            <th>Full Name</th>
            <th>Age</th>
            <th>Postal Address</th>
            <th>Resident Address</th>
            <th>Remarks</th>
            <th>Date of Birth</th>
            <th>Email</th>
            <th>Requested Date</th>
            <th>ID Type</th>
            <th>ID Photo (1x1)</th>
            <th>ID Photo</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="certificate-table-body">
          <?php if (!empty($certificate_of_comelec_registration)): ?>
            <?php foreach ($certificate_of_comelec_registration as $record): ?>
              <tr>
                <td><?= htmlspecialchars($record['id']) ?></td>
                <td><?= htmlspecialchars($record['document_type']) ?></td>
                <td><?= htmlspecialchars($record['fullname']) ?></td>
                <td><?= htmlspecialchars($record['age']) ?></td>
                <td><?= htmlspecialchars($record['postal_address']) ?></td>
                <td><?= htmlspecialchars($record['resident_address']) ?></td>
                <td><?= htmlspecialchars($record['remarks']) ?></td>
                <td><?= htmlspecialchars($record['date_of_birth']) ?></td>
                <td><?= htmlspecialchars($record['email']) ?></td>
                <td><?= htmlspecialchars($record['requested_date']) ?></td>
                <td><?= htmlspecialchars($record['id_type']) ?></td>
                <td>
                  <?php if (!empty($record['photo_1x1_url'])): ?>
                    <img src="uploads/<?= htmlspecialchars($record['photo_1x1_url']) ?>" 
                         alt="ID Photo 1x1" class="img-thumbnail" style="width: 100px;">
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($record['id_photo_url'])): ?>
                    <img src="uploads/<?= htmlspecialchars($record['id_photo_url']) ?>" 
                         alt="ID Photo" class="img-thumbnail" style="width: 100px;">
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($record['created_at']) ?></td>
                <td>
                      <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editRecordModal" onclick="loadEditForm(<?= $record['id'] ?>)">Edit</button>
                      <button class="btn btn-danger btn-sm" onclick="deleteRecord(<?= $record['id'] ?>)">Delete</button>
                      <button class="btn btn-warning btn-sm" onclick="printRecord(<?= $record['id'] ?>)">Print</button>
                      <input type="checkbox" 
                          class="form-check-input" 
                          id="doneCheckbox-<?= $record['id'] ?>" 
                          <?= $record['is_checked'] ? 'checked' : '' ?> 
                          onchange="markAsDone(<?= $record['id'] ?>, this.checked)">
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="16" class="text-center">No records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Add Record Button -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRecordModal">
      Add New Record
    </button>

    <!-- Add Modal -->
    <div class="modal fade" id="addRecordModal" tabindex="-1" aria-labelledby="addRecordModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addRecordModalLabel">Add New Certificate of Comelec Registration</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="addRecordForm">
              <!-- Document Type -->
              <div class="mb-3">
                <label for="document_type" class="form-label">Document Type:</label>
                <select id="document_type" name="document_type" class="form-select" required>
                  <option value="CERTIFICATE OF COMELEC REGISTRATION">Certificate of Comelec Registration</option>
                </select>
              </div>

              <!-- Full Name -->
              <div class="mb-3">
                <label for="fullname" class="form-label">Full Name:</label>
                <input type="text" id="fullname" name="fullname" class="form-control" placeholder="Enter full name" required>
              </div>

              <!-- Age -->
              <div class="mb-3">
                <label for="age" class="form-label">Age:</label>
                <input type="number" id="age" name="age" class="form-control" placeholder="Enter age" required>
              </div>

              <!-- Postal Address -->
              <div class="mb-3">
                <label for="postal_address" class="form-label">Postal Address:</label>
                <input type="text" id="postal_address" name="postal_address" class="form-control" placeholder="Enter postal address" required>
              </div>

              <!-- Resident Address -->
              <div class="mb-3">
                <label for="resident_address" class="form-label">Resident Address:</label>
                <input type="text" id="resident_address" name="resident_address" class="form-control" placeholder="Enter resident address" required>
              </div>

              <!-- Remarks -->
              <div class="mb-3">
                <label for="remarks" class="form-label">Remarks:</label>
                <textarea id="remarks" name="remarks" class="form-control" rows="3" placeholder="Enter remarks"></textarea>
              </div>

              <!-- Date of Birth -->
              <div class="mb-3">
                <label for="date_of_birth" class="form-label">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" required>
              </div>

              <!-- Email -->
              <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter email" required>
              </div>

              <!-- Requested Date -->
              <div class="mb-3">
                <label for="requested_date" class="form-label">Requested Date:</label>
                <input type="date" id="requested_date" name="requested_date" class="form-control" required>
              </div>

              <!-- ID Type -->
              <div class="mb-3">
                <label for="id_type" class="form-label">ID Type:</label>
                <select id="id_type" name="id_type" class="form-select" required>
                  <option value="" disabled selected>Select ID Type</option>
                  <option value="Barangay_ID">Barangay ID</option>
                  <option value="National_ID">National ID</option>
                  <option value="Passport">Passport</option>
                  <option value="Drivers_License">Driver's License</option>
                  <option value="SSS_ID">SSS ID</option>
                  <option value="PRC_ID">PRC ID</option>
                  <option value="Senior_Citizen_ID">Senior Citizen ID</option>
                  <option value="School_ID">School ID</option>
                  <option value="PhilHealth_ID">PhilHealth ID</option>
                  <option value="PWD_ID">PWD ID</option>
                </select>
              </div>

              <!-- ID Photo (1x1) -->
              <div class="mb-3">
                <label for="photo_1x1_url" class="form-label">ID Photo (1x1):</label>
                <input type="file" id="photo_1x1_url" name="photo_1x1_url" class="form-control" accept="image/*" required>
              </div>

              <!-- ID Photo -->
              <div class="mb-3">
                <label for="id_photo_url" class="form-label">ID Photo:</label>
                <input type="file" id="id_photo_url" name="id_photo_url" class="form-control" accept="image/*" required>
              </div>

              <!-- Submit Button -->
              <div class="text-end">
                <button type="submit" class="btn btn-success">Add Record</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

<!-- Edit Modal -->
<div class="modal fade" id="editRecordModal" tabindex="-1" aria-labelledby="editRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRecordModalLabel">Edit COMELEC Registration Certificate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editRecordForm" enctype="multipart/form-data">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="mb-3">
                        <label for="edit_fullname" class="form-label">Full Name:</label>
                        <input type="text" id="edit_fullname" name="fullname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_age" class="form-label">Age:</label>
                        <input type="number" id="edit_age" name="age" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_postal_address" class="form-label">Postal Address:</label>
                        <input type="text" id="edit_postal_address" name="postal_address" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_resident_address" class="form-label">Resident Address:</label>
                        <input type="text" id="edit_resident_address" name="resident_address" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_remarks" class="form-label">Remarks:</label>
                        <input type="text" id="edit_remarks" name="remarks" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="edit_date_of_birth" class="form-label">Date of Birth:</label>
                        <input type="date" id="edit_date_of_birth" name="date_of_birth" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email:</label>
                        <input type="email" id="edit_email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_requested_date" class="form-label">Requested Date:</label>
                        <input type="date" id="edit_requested_date" name="requested_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_id_type" class="form-label">ID Type:</label>
                        <select id="edit_id_type" name="id_type" class="form-select" required>
                          <option value="Barangay_ID">Barangay ID</option>
                          <option value="National_ID">National ID</option>
                          <option value="Passport">Passport</option>
                          <option value="Drivers_License">Driver's License</option>
                          <option value="SSS_ID">SSS ID</option>
                          <option value="PRC_ID">PRC ID</option>
                          <option value="Senior_Citizen_ID">Senior Citizen ID</option>
                          <option value="School_ID">School ID</option>
                          <option value="PhilHealth_ID">PhilHealth ID</option>
                          <option value="PWD_ID">PWD ID</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_photo_1x1_url" class="form-label">1x1 Photo:</label>
                        <input type="file" id="edit_photo_1x1_url" name="photo_1x1_url" class="form-control" accept="image/*">
                        <div class="mt-2">
                            <img id="current_photo_1x1" src="" alt="Current 1x1 Photo" style="width: 100px; height: auto;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_id_photo_url" class="form-label">ID Photo:</label>
                        <input type="file" id="edit_id_photo_url" name="id_photo_url" class="form-control" accept="image/*">
                        <div class="mt-2">
                            <img id="current_id_photo" src="" alt="Current ID Photo" style="width: 100px; height: auto;">
                        </div>
                    </div>
                    <div class="text-end">
                    <button type="button" class="btn btn-success" onclick="updateCertificate()">Update Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
  </main>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/utils.js"></script>
  <script src="includes/sidebar.js"></script>
  <script src="includes/header.js"></script>
  <script src="crudCOMELEC/sort.js"></script>
  <script src="crudCOMELEC/fetch_cert.js"></script>
  <script src="crudCOMELEC/add_cert.js"></script>
  <script src="crudCOMELEC/delete_cert.js"></script>
  <script src="crudCOMELEC/print_cert.js"></script>
  <script src="crudCOMELEC/update_cert.js"></script>
</body>
</html>
