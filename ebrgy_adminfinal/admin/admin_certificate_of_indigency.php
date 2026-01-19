<?php
session_start();
require_once './includes/access_control.php';

// Check if user is logged in and has a valid role
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['Staff', 'Admin', 'SuperAdmin'])) {
    header("Location: admin_login.php");
    exit();
}

// For SuperAdmin: If they select a barangay, store the selected barangay_id in session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barangay_selector'])) {
    $_SESSION['selected_barangay_id'] = $_POST['barangay_selector']; // Save selected barangay_id to session
    header("Location: admin_barangay_certificate.php"); // Refresh the page to apply the new barangay selection
    exit();
}

// Get the selected barangay for filtering, default to null or previous selection
$selectedBarangay = $_SESSION['selected_barangay_id'] ?? null;

// Get the list of barangays the SuperAdmin has access to
$barangayIds = $_SESSION['barangay_ids']; // Array of allowed barangays for SuperAdmin
$barangayId = $_SESSION['barangay_id']; // For Admin/Staff, use the assigned barangay_id
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificate of Indigency Records</title>
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
  <!-- Header and Sidebar -->
  <?php include('includes/header.php'); ?>
  <?php include('includes/sidebar.php'); ?>

  <!-- Main Content -->
  <main class="main-container">
      <h1>
        <a href="document_records.php" class="breadcrumb-link">Document Request Record</a> /
        <span class="current-page">Certificate of Indigency Records</span>
      </h1>

    <?php if ($_SESSION['role'] === 'SuperAdmin') { ?>
      <!-- Barangay Selector for SuperAdmin -->
      <form method="POST" action="">
        <label for="barangay_selector">Choose Barangay:</label>
        <select name="barangay_selector" id="barangay_selector" class="form-select mb-4" onchange="this.form.submit()">
          <?php
          foreach ($barangayIds as $barangay_id) {
              // Fetch barangay names (you can get actual names from the database)
              $barangayName = "Barangay " . $barangay_id; // Example, replace with actual name
              $selected = ($barangay_id == $selectedBarangay) ? "selected" : ""; // Mark selected barangay
              echo "<option value='$barangay_id' $selected>$barangayName</option>";
          }
          ?>
        </select>
      </form>
    <?php } ?>

      <!-- Sort and Search -->
      <div class="certificate-table-container">
        <div class="sort-options-container d-flex justify-content-between align-items-center">
          <!-- Sort Options -->
          <div>
            <label for="sort-by-certificates" class="form-label">Sort:</label>
            <select id="sort-by-certificates" class="form-control">
              <option value="default">Sort by Default (ID)</option>
              <option value="name">Sort by Full Name</option>
              <option value="date">Sort by Requested Date</option>
              <option value="purpose">Sort by Purpose</option>
            </select>
          </div>
          <!-- Search Bar -->
          <div class="search-icon-container d-flex align-items-center">
            <i class="material-icons-outlined me-2">search</i>
            <input type="text" id="search-certificates" class="form-control search-input" placeholder="Search certificates..." aria-label="Search">
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
            <th>Status</th>
            <th>Citizen</th>
            <th>Address</th>
            <th>Requested Date</th>
            <th>Email</th>
            <th>Purpose</th>
            <th>Other Details</th>
            <th>ID Type</th>
            <th>ID Photo</th>
            <th>Time Requested</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="certificate-table-body">
          <?php if (!empty($barangay_certificates)): ?>
            <?php foreach ($barangay_certificates as $record): ?>
              <tr data-id="<?= $record['id'] ?>">
                <td><?= htmlspecialchars($record['id']) ?></td>
                <td><?= htmlspecialchars($record['document_type']) ?></td>
                <td><?= htmlspecialchars($record['fullname']) ?></td>
                <td><?= htmlspecialchars($record['age']) ?></td>
                <td><?= htmlspecialchars($record['status']) ?></td>
                <td><?= htmlspecialchars($record['citizen']) ?></td>
                <td><?= htmlspecialchars($record['address']) ?></td>
                <td><?= htmlspecialchars($record['requested_date']) ?></td>
                <td><?= htmlspecialchars($record['email']) ?></td>
                <td><?= htmlspecialchars($record['indigency_purpose']) ?></td>
                <td><?= htmlspecialchars($record['indigency_other_details'] ?: 'N/A') ?></td>
                <td><?= htmlspecialchars($record['id_type']) ?></td>
                <td>
                  <?php if (!empty($record['id_photo_url'])): ?>
                    <img src="crudIndigency/uploads/<?= htmlspecialchars($record['id_photo_url']) ?>" 
                        alt="ID Photo" 
                        class="img-thumbnail" 
                        style="width: 100px;">
                  <?php else: ?>
                    <p>No Photo</p>
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
        <h5 class="modal-title" id="addRecordModalLabel">Add New Certificate</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addRecordForm" enctype="multipart/form-data">
          <!-- Document Type -->
          <div class="mb-3">
            <label for="document_type" class="form-label">Document Type:</label>
            <select id="document_type" name="document_type" class="form-select" required>
              <option value="CERTIFICATE OF INDIGENCY">Certificate of Indigency</option>
            </select>
          </div>

          <!-- Full Name -->
          <div class="mb-3">
            <label for="fullname" class="form-label">Full Name:</label>
            <input type="text" id="fullname" name="fullname" class="form-control" required>
          </div>

          <!-- Age -->
          <div class="mb-3">
            <label for="age" class="form-label">Age:</label>
            <input type="number" id="age" name="age" class="form-control" required>
          </div>

          <!-- Status -->
          <div class="mb-3">
            <label for="status" class="form-label">Status:</label>
            <select id="status" name="status" class="form-select" required>
              <option value="single">Single</option>
              <option value="married">Married</option>
              <option value="widowed">Widowed</option>
              <option value="separated">Separated</option>
              <option value="divorced">Divorced</option>
            </select>
          </div>

          <!-- Citizen -->
          <div class="mb-3">
            <label for="citizen" class="form-label">Citizen:</label>
            <select id="citizen" name="citizen" class="form-select" required>
              <option value="Filipino">Filipino</option>
              <option value="Non-Filipino">Non-Filipino</option>
            </select>
          </div>

          <!-- Address -->
          <div class="mb-3">
            <label for="address" class="form-label">Address:</label>
            <input type="text" id="address" name="address" class="form-control" required>
          </div>

          <!-- Requested Date -->
          <div class="mb-3">
            <label for="requested_date" class="form-label">Requested Date:</label>
            <input type="date" id="requested_date" name="requested_date" class="form-control" required>
          </div>

          <!-- Email -->
          <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" id="email" name="email" class="form-control" required>
          </div>

          <!-- Purpose -->
          <div class="mb-3">
            <label for="indigency_purpose" class="form-label">Purpose:</label>
            <textarea id="indigency_purpose" name="indigency_purpose" class="form-control" rows="2" required></textarea>
          </div>

          <!-- Other Details -->
          <div class="mb-3">
            <label for="indigency_other_details" class="form-label">Other Details:</label>
            <textarea id="indigency_other_details" name="indigency_other_details" class="form-control" rows="2"></textarea>
          </div>

          <!-- ID Type -->
          <div class="mb-3">
            <label for="id_type" class="form-label">ID Type:</label>
            <select id="id_type" name="id_type" class="form-select" required>
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

          <!-- ID Photo Upload -->
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
                <h5 class="modal-title" id="editRecordModalLabel">Edit Certificate</h5>
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
                        <label for="edit_status" class="form-label">Status:</label>
                        <select id="edit_status" name="status" class="form-select" required>
                            <option value="single">Single</option>
                            <option value="married">Married</option>
                            <option value="widowed">Widowed</option>
                            <option value="separated">Separated</option>
                            <option value="divorced">Divorced</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_citizen" class="form-label">Citizen:</label>
                        <select id="edit_citizen" name="citizen" class="form-select" required>
                            <option value="Filipino">Filipino</option>
                            <option value="Non-Filipino">Non-Filipino</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address:</label>
                        <input type="text" id="edit_address" name="address" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_requested_date" class="form-label">Requested Date:</label>
                        <input type="date" id="edit_requested_date" name="requested_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email:</label>
                        <input type="email" id="edit_email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_indigency_purpose" class="form-label">Purpose:</label>
                        <textarea id="edit_indigency_purpose" name="indigency_purpose" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_indigency_other_details" class="form-label">Other Details:</label>
                        <textarea id="edit_indigency_other_details" name="indigency_other_details" class="form-control" rows="2"></textarea>
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

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/utils.js"></script>
  <script src="includes/sidebar.js"></script>
  <script src="includes/header.js"></script>
  <script src="crudIndigency/sort.js"></script>
  <script src="crudIndigency/fetch_cert.js"></script>
  <script src="crudIndigency/add_cert.js"></script>
  <script src="crudIndigency/delete_cert.js"></script>
  <script src="crudIndigency/update_cert.js"></script>
  <script src="crudIndigency/print_cert.js"></script>
</body>
</html>