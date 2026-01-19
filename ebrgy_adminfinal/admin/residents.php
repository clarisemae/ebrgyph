<?php
session_start();
require_once './includes/access_control.php';

// Check if the user is SuperAdmin and allow them to select a barangay
if ($_SESSION['role'] === 'SuperAdmin') {
    // Get the list of barangays SuperAdmin can access
    $barangayIds = $_SESSION['barangay_ids']; // Array of allowed barangays for SuperAdmin
    $selectedBarangay = $_SESSION['selected_barangay_id'] ?? $barangayIds[0]; // Default to first barangay if none selected

    // If SuperAdmin selects a barangay, store it in the session
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barangay_selector'])) {
        $_SESSION['selected_barangay_id'] = $_POST['barangay_selector']; // Store selected barangay in session
        header("Location: residents.php"); // Refresh the page to apply the new barangay
        exit();
    }
} else {
    // For Admin/Staff, use their assigned barangay_id
    $selectedBarangay = $_SESSION['barangay_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resident Records - Barangay Management System</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="includes/general.css">
  <link rel="stylesheet" href="includes/header.css">
  <link rel="stylesheet" href="includes/sidebar.css">
  <link rel="stylesheet" href="css/resident.css">
</head>
<body>
  <?php include('includes/header.php'); ?> <!-- Include Header -->
  <?php include('includes/sidebar.php'); ?> <!-- Include Sidebar -->

  <!-- Main Content Section -->
  <main class="main-container">
    <h1>Resident Records</h1>

    <!-- Barangay Selector for SuperAdmin -->
    <?php if ($_SESSION['role'] === 'SuperAdmin'): ?>
        <form method="POST" action="">
            <label for="barangay_selector">Choose Barangay:</label>
            <select name="barangay_selector" id="barangay_selector" class="form-select mb-4 w-25" onchange="this.form.submit()">
                <?php
                // Loop through the barangays SuperAdmin can access
                foreach ($barangayIds as $barangay_id) {
                    $barangayName = "Barangay " . $barangay_id; // Example name (replace with actual name from the database)
                    $selected = ($barangay_id == $selectedBarangay) ? "selected" : ""; // Mark selected barangay
                    echo "<option value='$barangay_id' $selected>$barangayName</option>";
                }
                ?>
            </select>
        </form>
    <?php endif; ?>

    <!-- Resident Records Table -->
    <div class="resident-table-container">
          <div class="sort-options-container d-flex justify-content-between align-items-center">
            <div>
              <!-- Sorting Dropdown -->
              <label for="sort-by" class="sort-label">Sort:</label>
              <select id="sort-by" class="form-control">
                <option value="default">Sort by Default(Number)</option>
                <option value="name">Sort by Name</option>
                <option value="gender">Sort by Gender</option>
                <option value="sector">Sort by Sector</option>
              </select>
            </div>
            <div class="search-icon-container">
              <!-- Search Input with Icon -->
              <i class="material-icons-outlined">search</i>
              <input type="text" id="search-residents" class="form-control search-input" placeholder="Search..." aria-label="Search">
              <span class="search-icon"></span>
            </div>
          </div>

      <table class="resident-table table table-bordered">
          <thead>
            <tr>
              <th>No.</th>
              <th>Name</th>
              <th>Age</th>
              <th>Address</th>
              <th>Gender</th>
              <th>Sector</th>
              <th>Citizenship</th>
              <th>Actions</th>
            </tr>
          </thead>

        <tbody id="resident-table-body">
        <?php
        // Fetch resident data based on selected barangay for SuperAdmin or assigned barangay for Admin/Staff
        $stmt = $conn->prepare("SELECT * FROM resident WHERE barangay_id = ?");
        $stmt->bind_param("i", $selectedBarangay);
        $stmt->execute();
        $residents = $stmt->get_result();

        if ($residents->num_rows > 0):
            while ($resident = $residents->fetch_assoc()):
        ?>
            <tr data-id="<?= htmlspecialchars($resident['resident_id']) ?>">
                <td><?= htmlspecialchars($resident['row_number']) ?></td> <!-- Display row_number -->
                <td><?= htmlspecialchars($resident['full_name']) ?></td>
                <td><?= htmlspecialchars($resident['age']) ?></td>
                <td><?= htmlspecialchars($resident['address']) ?></td>
                <td><?= htmlspecialchars($resident['gender']) ?></td>
                <td><?= htmlspecialchars($resident['sector']) ?></td>
                <td><?= htmlspecialchars($resident['citizenship']) ?></td>
                <td>
                <button class="btn btn-info" onclick="openEditResident(<?= htmlspecialchars($resident['resident_id']) ?>)">Edit</button>
                <button class="btn btn-danger" onclick="deleteResident(<?= htmlspecialchars($resident['resident_id']) ?>)">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No residents found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Edit Resident Modal -->
<div class="modal fade" id="editResidentModal" tabindex="-1" aria-labelledby="editResidentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editResidentModalLabel">Edit Resident</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <form id="editResidentForm">
          <input type="hidden" name="resident_id" id="resident_id">
          <div class="mb-3">
              <label for="full_name" class="form-label">Full Name:</label>
              <input type="text" name="full_name" id="full_name" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="age" class="form-label">Age:</label>
              <input type="number" name="age" id="age" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="address" class="form-label">Address:</label>
              <input type="text" name="address" id="address" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="gender" class="form-label">Gender:</label>
              <select name="gender" id="gender" class="form-select" required>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
              </select>
          </div>
          <div class="mb-3">
              <label for="sector" class="form-label">Sector:</label>
              <select name="sector" id="sector" class="form-select" required>
                  <option value="General">General</option>
                  <option value="PWD">PWD</option>
                  <option value="Senior">Senior</option>
                  <option value="Solo Parent">Solo Parent</option>
                </select>
          </div>
          <div class="mb-3">
              <label for="citizenship" class="form-label">Citizenship:</label>
              <input type="text" name="citizenship" id="citizenship" class="form-control" required>
          </div>
          <button type="button" onclick="updateResident()" class="btn btn-success">Update Resident</button>
      </form>
      </div>
    </div>
  </div>
</div>


<!-- Add Resident Button -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResidentModal">
  Add New Resident
</button>

<!-- Add Resident Modal -->
<div class="modal fade" id="addResidentModal" tabindex="-1" aria-labelledby="addResidentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addResidentModalLabel">Add New Resident</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Add Resident Form -->
        <form id="addResidentForm">
            <div class="mb-3">
                <label for="add_full_name" class="form-label">Full Name:</label>
                <input type="text" name="full_name" id="add_full_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="add_age" class="form-label">Age:</label>
                <input type="number" name="age" id="add_age" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="add_address" class="form-label">Address:</label>
                <input type="text" name="address" id="add_address" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="add_gender" class="form-label">Gender:</label>
                <select name="gender" id="add_gender" class="form-select" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="mb-3">
              <label for="add_sector" class="form-label">Sector:</label>
              <select name="sector" id="add_sector" class="form-select" required>
                  <option value="General">General</option>
                  <option value="PWD">PWD</option>
                  <option value="Senior">Senior</option>
                  <option value="Solo Parent">Solo Parent</option>
                </select>
          </div>
            <div class="mb-3">
                <label for="add_citizenship" class="form-label">Citizenship:</label>
                <input type="text" name="citizenship" id="add_citizenship" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Add Resident</button>
        </form>
      </div>
    </div>
  </div>
</div>


  </main>

  <!-- Include JavaScript -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/utils.js"></script>
  <script src="includes/sidebar.js"></script>
  <script src="includes/header.js"></script>
  <script src="crudResident/add_resident.js"></script>
  <script src="crudResident/fetch_resident.js"></script>
  <script src="crudResident/update_resident.js"></script>
  <script src="crudResident/delete_resident.js"></script>
  <script src="crudResident/sort_residents.js"></script>
</body>
</html>
