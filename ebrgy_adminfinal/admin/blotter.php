<?php
session_start();

require_once './includes/access_control.php';
include './includes/db_connection.php';

// Check login and role
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['Staff', 'Admin', 'SuperAdmin'])) {
    header("Location: admin_login.php");
    exit();
}

// For SuperAdmin: allow selecting barangay, for others use assigned barangay_id
$barangayId = $_SESSION['selected_barangay_id'] ?? $_SESSION['barangay_id'] ?? null;

if (!$barangayId) {
    // No barangay selected or assigned, redirect to login or show error
    header("Location: admin_login.php");
    exit();
}

// Handle SuperAdmin barangay selection form submission
if ($_SESSION['role'] === 'SuperAdmin') {
    $barangayIds = $_SESSION['barangay_ids'] ?? [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barangay_selector'])) {
        $selected = $_POST['barangay_selector'];
        // Validate that selected barangay is allowed
        if (in_array($selected, $barangayIds)) {
            $_SESSION['selected_barangay_id'] = $selected;
            header("Location: blotter.php");
            exit();
        }
    }
}

// Fetch blotter records for the effective barangay
$sql = "SELECT *, ROW_NUMBER() OVER (ORDER BY id) AS row_number FROM incident_report WHERE barangay_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $barangayId);
$stmt->execute();
$result = $stmt->get_result();

$blotter_records = [];
while ($row = $result->fetch_assoc()) {
    $blotter_records[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Blotter Records - Barangay Management System</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="includes/general.css" />
  <link rel="stylesheet" href="includes/header.css" />
  <link rel="stylesheet" href="includes/sidebar.css" />
  <link rel="stylesheet" href="css/blotter.css" />
</head>
<body>
  <?php include('includes/header.php'); ?> <!-- Include Header -->
  <?php include('includes/sidebar.php'); ?> <!-- Include Sidebar -->

  <main class="main-container">
    <h1>Blotter Records</h1>

    <!-- SuperAdmin Barangay Selector -->
    <?php if ($_SESSION['role'] === 'SuperAdmin'): ?>
      <form method="POST" action="" class="mb-4 w-25">
        <label for="barangay_selector" class="form-label">Select Barangay:</label>
        <select name="barangay_selector" id="barangay_selector" class="form-select" onchange="this.form.submit()">
          <?php
            foreach ($barangayIds as $bid):
                // You can replace this with actual barangay names from your database
                $barangayName = "Barangay " . htmlspecialchars($bid);
                $selected = ($bid == $barangayId) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($bid) ?>" <?= $selected ?>><?= $barangayName ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    <?php endif; ?>

    <!-- Blotter Records Table -->
    <div class="blotter-table-container">
      <div class="sort-options-container d-flex justify-content-between align-items-center mb-3">
        <div>
          <label for="sort-by" class="sort-label">Sort:</label>
          <select id="sort-by" class="form-control">
            <option value="default">Sort by Default(ID)</option>
            <option value="complainant">Sort by Complainant</option>
            <option value="incident_type">Sort by Incident Type</option>
            <option value="date">Sort by Date</option>
          </select>
        </div>
        <div class="search-icon-container d-flex align-items-center">
          <i class="material-icons-outlined me-2">search</i>
          <input type="text" id="search-blotter" class="form-control search-input" placeholder="Search..." aria-label="Search" />
        </div>
      </div>

      <table class="blotter-table table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Complainant</th>
            <th>Accused</th>
            <th>Incident Type</th>
            <th>Other Incident</th>
            <th>Incident Address</th>
            <th>Date</th>
            <th>Time</th>
            <th>Narrative/Salaysay</th>
            <th>Incident Photo</th>
            <th>Submitted At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="blotter-table-body">
          <?php if (!empty($blotter_records)): ?>
            <?php foreach ($blotter_records as $record): ?>
              <tr data-id="<?= htmlspecialchars($record['id']) ?>">
                <td><?= htmlspecialchars($record['row_number']) ?></td>
                <td><?= htmlspecialchars($record['complainant']) ?></td>
                <td><?= htmlspecialchars($record['accused']) ?></td>
                <td><?= htmlspecialchars($record['incident_type']) ?></td>
                <td><?= !empty($record['other_incident']) ? htmlspecialchars($record['other_incident']) : 'null' ?></td>
                <td><?= htmlspecialchars($record['incident_address']) ?></td>
                <td><?= htmlspecialchars($record['date']) ?></td>
                <td><?= htmlspecialchars($record['time']) ?></td>
                <td><?= htmlspecialchars($record['message']) ?></td>
                <td>
                  <?php if (!empty($record['incident_photo'])): ?>
                    <img src="uploads/<?= htmlspecialchars($record['incident_photo']) ?>" 
                         alt="Incident Photo" 
                         class="img-thumbnail incident-photo" 
                         data-bs-toggle="modal" 
                         data-bs-target="#photoModal-<?= htmlspecialchars($record['id']) ?>" 
                         style="width: 100px; cursor: pointer;">
                  <?php else: ?>
                    No Photo
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($record['created_at']) ?></td>
                <td>
                  <button class="btn btn-info" onclick="openEditBlotter(<?= htmlspecialchars($record['id']) ?>)">Edit</button>
                  <button class="btn btn-danger" onclick="deleteBlotter(<?= htmlspecialchars($record['id']) ?>)">Delete</button>
                  <button class="btn btn-warning" onclick="printBlotter(<?= htmlspecialchars($record['id']) ?>)">Print</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="12" class="text-center">No blotter records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="modal fade" id="editBlotterModal" tabindex="-1" aria-labelledby="editBlotterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBlotterModalLabel">Edit Blotter Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form id="editBlotterForm">
    <input type="hidden" id="id" name="id"> <!-- Hidden ID -->

    <div class="mb-3">
        <label for="complainant" class="form-label">Complainant:</label>
        <input type="text" id="complainant" name="complainant" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="accused" class="form-label">Accused:</label>
        <input type="text" id="accused" name="accused" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="incident_type" class="form-label">Incident Type:</label>
        <input type="text" id="incident_type" name="incident_type" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="other_incident" class="form-label">Other Incident:</label>
        <input type="text" id="other_incident" name="other_incident" class="form-control">
    </div>

    <div class="mb-3">
        <label for="incident_address" class="form-label">Incident Address:</label>
        <input type="text" id="incident_address" name="incident_address" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="date" class="form-label">Date:</label>
        <input type="date" id="date" name="date" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="time" class="form-label">Time:</label>
        <input type="time" id="time" name="time" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="message" class="form-label">Narrative/Salaysay:</label>
        <textarea id="message" name="message" class="form-control" rows="4" required></textarea>
    </div>

    <button type="button" class="btn btn-success" onclick="updateBlotter()">Update Record</button>
</form>

            </div>
          </div>
        </div>
    </div>


    <!-- Add Blotter Button -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBlotterModal">
      Add New Record
    </button>

    <!-- Add Blotter Modal -->
<div class="modal fade" id="addBlotterModal" tabindex="-1" aria-labelledby="addBlotterModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addBlotterModalLabel">Add New Blotter Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addBlotterForm">
          <div class="mb-3">
            <label for="add_complainant" class="form-label">Complainant:</label>
            <input type="text" name="complainant" id="add_complainant" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="add_accused" class="form-label">Accused:</label>
            <input type="text" name="accused" id="add_accused" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="add_incident_type" class="form-label">Incident Type:</label>
            <input type="text" name="incident_type" id="add_incident_type" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="add_other_incident" class="form-label">Other Incident:</label>
            <input type="text" name="other_incident" id="add_other_incident" class="form-control">
          </div>
          <div class="mb-3">
            <label for="add_incident_address" class="form-label">Incident Address:</label>
            <input type="text" name="incident_address" id="add_incident_address" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="add_date" class="form-label">Date:</label>
            <input type="date" name="date" id="add_date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="add_time" class="form-label">Time:</label>
            <input type="time" name="time" id="add_time" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="add_message" class="form-label">Narrative/Salaysay:</label>
            <textarea name="message" id="add_message" class="form-control" rows="4" required></textarea>
          </div>
          <button type="submit" class="btn btn-success">Add Record</button>
        </form>
      </div>
    </div>
  </div>
</div>


  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/utils.js"></script>
  <script src="includes/sidebar.js"></script>
  <script src="includes/header.js"></script>
  <script src="crudBlotter/add_blotter.js"></script>
  <script src="crudBlotter/fetch_blotter.js"></script>
  <script src="crudBlotter/update_blotter.js"></script>
  <script src="crudBlotter/delete_blotter.js"></script>
  <script src="crudBlotter/sort_blotter.js"></script>
  <script src="crudBlotter/print.js"></script>
</body>
</html>
