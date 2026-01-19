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
  <title>Barangay Certificate Records</title>
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
      <span class="current-page">Barangay Certificate Records</span>
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
          <?php
            // Fetch the certificates based on the selected barangay
            $barangayIdToUse = $selectedBarangay ?? $barangayId; // Use SuperAdmin's selected barangay, otherwise use Admin's assigned barangay

            // Fetch the records for the current barangay
            $sql = "SELECT * FROM barangay_certificate WHERE barangay_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $barangayIdToUse);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0):
                while ($record = $result->fetch_assoc()):
          ?>
                  <tr>
                    <td><?= htmlspecialchars($record['id']) ?></td>
                    <td><?= htmlspecialchars($record['document_type']) ?></td>
                    <td><?= htmlspecialchars($record['fullname']) ?></td>
                    <td><?= htmlspecialchars($record['age']) ?></td>
                    <td><?= htmlspecialchars($record['status']) ?></td>
                    <td><?= htmlspecialchars($record['citizen']) ?></td>
                    <td><?= htmlspecialchars($record['address']) ?></td>
                    <td><?= htmlspecialchars($record['requested_date']) ?></td>
                    <td><?= htmlspecialchars($record['email']) ?></td>
                    <td><?= htmlspecialchars($record['barangay_certificate_purpose'] ?: 'N/A') ?></td>
                    <td><?= htmlspecialchars($record['id_type']) ?></td>
                    <td>
                        <?php if (!empty($record['id_photo_url'])): ?>
                            <img src="uploads/<?= htmlspecialchars($record['id_photo_url']) ?>" 
                                 alt="ID Photo" 
                                 class="img-thumbnail" 
                                 style="width: 100px;">
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
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
          <?php
                endwhile;
            else:
          ?>
                <tr>
                  <td colspan="1" class="text-center">No records found.</td>
                </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/utils.js"></script>
  <script src="includes/sidebar.js"></script>
  <script src="includes/header.js"></script>
  <script src="crudBrgyCertificate/sort.js"></script>
  <script src="crudBrgyCertificate/fetch_cert.js"></script>
  <script src="crudBrgyCertificate/update_cert.js"></script>
  <script src="crudBrgyCertificate/add_cert.js"></script>
  <script src="crudBrgyCertificate/delete_cert.js"></script>
  <script src="crudBrgyCertificate/print_cert.js"></script>

</body>
</html>
