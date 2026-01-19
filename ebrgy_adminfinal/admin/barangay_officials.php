<?php
session_start();

require_once './includes/access_control.php';

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['Staff', 'Admin', 'SuperAdmin'])) {
    header("Location: admin_login.php");
    exit();
}

// Get allowed barangays (for SuperAdmin) or assigned barangay (for others)
$barangayIds = $_SESSION['barangay_ids'] ?? null; // SuperAdmin's barangays (array)
$assignedBarangayId = $_SESSION['barangay_id'] ?? null; // Admin/Staff barangay (single)

// Handle barangay selector POST for SuperAdmin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barangay_selector'])) {
    $_SESSION['selected_barangay_id'] = $_POST['barangay_selector'];
    header("Location: barangay_officials.php");
    exit();
}

// Determine which barangay to filter officials by
if ($_SESSION['role'] === 'SuperAdmin') {
    $selectedBarangay = $_SESSION['selected_barangay_id'] ?? ($barangayIds[0] ?? null);
} else {
    $selectedBarangay = $assignedBarangayId;
}

if (!$selectedBarangay) {
    die("No barangay selected or assigned.");
}

include './includes/db_connection.php';

// Fetch officials filtered by selected barangay
$sql = "SELECT id, photo, name, role FROM barangay_officials WHERE barangay_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $selectedBarangay);
$stmt->execute();
$result = $stmt->get_result();

$officials = [];
while ($row = $result->fetch_assoc()) {
    $officials[] = $row;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Officials - Barangay Management System</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="includes/general.css">
  <link rel="stylesheet" href="includes/header.css">
  <link rel="stylesheet" href="includes/sidebar.css">
  <link rel="stylesheet" href="css/barangay.css">
</head>
<body>
  <?php include('includes/header.php'); ?> <!-- Include Header -->
  <?php include('includes/sidebar.php'); ?> <!-- Include Sidebar -->

  <!-- Main Content Section -->
  <main class="main-container">
    <h1>Barangay Officials</h1>

    <?php if ($_SESSION['role'] === 'SuperAdmin' && !empty($barangayIds)): ?>
    <form method="POST" class="mb-4">
        <label for="barangay_selector" class="form-label">Select Barangay:</label>
        <select name="barangay_selector" id="barangay_selector" class="form-select" onchange="this.form.submit()">
            <?php foreach ($barangayIds as $barangay_id): 
                $barangayName = "Barangay " . $barangay_id; // Replace with actual name from DB if you want
                $selected = ($barangay_id == $selectedBarangay) ? 'selected' : '';
            ?>
                <option value="<?= htmlspecialchars($barangay_id) ?>" <?= $selected ?>><?= htmlspecialchars($barangayName) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php endif; ?>

    <!-- Barangay Officials Table -->
    <div class="officials-table-container">
      <div class="sort-options-container d-flex justify-content-between align-items-center">
        <div>
          <!-- Sorting Dropdown -->
          <label for="sort-by" class="sort-label">Sort:</label>
          <select id="sort-by" class="form-control">
            <option value="default">Sort by Default(ID)</option>
            <option value="name">Sort by Name</option>
            <option value="role">Sort by Role</option>
          </select>
        </div>
        <div class="search-icon-container">
          <!-- Search Input with Icon -->
          <i class="material-icons-outlined">search</i>
          <input type="text" id="search-officials" class="form-control search-input" placeholder="Search..." aria-label="Search">
        </div>
      </div>

      <table class="officials-table table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Photo</th>
            <th>Name</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="officials-table-body">
          <?php if (!empty($officials)): ?>
            <?php foreach ($officials as $official): ?>
              <tr data-id="<?= htmlspecialchars($official['id']) ?>">
                <td><?= htmlspecialchars($official['id']) ?></td>
                <td>
                  <?php if (!empty($official['photo'])): ?>
                    <img src="crudBrgyOfficials/uploads/<?= htmlspecialchars($official['photo']) ?>" 
                         alt="Official Photo" 
                         class="img-thumbnail" 
                         style="width: 100px;">
                  <?php else: ?>
                    No Photo
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($official['name']) ?></td>
                <td><?= htmlspecialchars($official['role']) ?></td>
                <td>
                  <button class="btn btn-info" onclick="openEditOfficial(<?= htmlspecialchars($official['id']) ?>)">Edit</button>
                  <button class="btn btn-danger" onclick="deleteOfficial(<?= htmlspecialchars($official['id']) ?>)">Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5">No officials found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

<!-- Edit Official Modal -->
<!-- Edit Official Modal -->
<div class="modal fade" id="editOfficialModal" tabindex="-1" aria-labelledby="editOfficialModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editOfficialModalLabel">Edit Official</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editOfficialForm">
          <input type="hidden" name="official_id" id="official_id">
          
          <!-- Name input field -->
          <div class="mb-3">
            <label for="name" class="form-label">Name:</label>
            <input type="text" name="name" id="name" class="form-control" required>
          </div>

          <!-- Role select field -->
          <div class="mb-3">
            <label for="role" class="form-label">Role:</label>
            <select name="role" id="role" class="form-control" required>
              <option value="Barangay Chairman">Barangay Chairman</option>
              <option value="Barangay Kagawad">Barangay Kagawad</option>
              <option value="Barangay Treasurer">Barangay Treasurer</option>
              <option value="Barangay Secretary">Barangay Secretary</option>
              <option value="SK Chairman">SK Chairman</option>
            </select>
          </div>

          <!-- Photo input field -->
          <div class="mb-3">
            <label for="photo" class="form-label">Photo:</label>
            <input type="file" name="photo" id="photo" class="form-control">
          </div>

          <!-- Current photo preview (if applicable) -->
          <div class="mb-3" id="currentPhotoPreview" style="display:none;">
            <label for="current_photo" class="form-label">Current Photo:</label>
            <img id="current_photo" src="" alt="Current Photo" class="img-fluid mb-2">
          </div>

          <!-- Update button -->
          <button type="button" onclick="updateOfficial()" class="btn btn-success">Update Official</button>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- Add Official Button -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOfficialModal">
  Add New Official
</button>

<!-- Add Official Modal -->
<!-- Add Official Modal -->
<div class="modal fade" id="addOfficialModal" tabindex="-1" aria-labelledby="addOfficialModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addOfficialModalLabel">Add New Official</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addOfficialForm">
          <div class="mb-3">
            <label for="add_name" class="form-label">Name:</label>
            <input type="text" name="name" id="add_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="add_role" class="form-label">Role:</label>
            <select name="role" id="add_role" class="form-control" required>
              <option value="Barangay Captain">Barangay Captain</option>
              <option value="Barangay Kagawad">Barangay Kagawad</option>
              <option value="Barangay Treasurer">Barangay Treasurer</option>
              <option value="Barangay Secretary">Barangay Secretary</option>
              <option value="SK Chairman">SK Chairman</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="add_photo" class="form-label">Photo:</label>
            <input type="file" name="photo" id="add_photo" class="form-control">
          </div>
          <button type="submit" class="btn btn-success">Add Official</button>
        </form>
      </div>
    </div>
  </div>
</div>


</main>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/utils.js"></script>
  <script src="includes/sidebar.js"></script>
  <script src="includes/header.js"></script>
  <script src="crudOfficials/add_official.js"></script>
  <script src="crudOfficials/fetch_officials.js"></script>
  <script src="crudOfficials/update_officials.js"></script>
  <script src="crudOfficials/delete_officials.js"></script>
  <script src="crudOfficials/sort.js"></script>
</body>
</html>
