<?php
session_start();

require_once './includes/access_control.php';

// Check for valid login and role
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['Staff', 'Admin', 'SuperAdmin'])) {
    header("Location: admin_login.php");
    exit();
}

// Get barangay IDs available for this user
// For SuperAdmin, this is an array of barangays they can access
$barangayIds = $_SESSION['barangay_ids'] ?? null;

// For Staff/Admin, single barangay_id
$assignedBarangayId = $_SESSION['barangay_id'] ?? null;

// Handle barangay selector submission (SuperAdmin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barangay_selector'])) {
    $_SESSION['selected_barangay_id'] = $_POST['barangay_selector'];
    header("Location: announcements.php");
    exit();
}

// Determine the barangay to use for filtering
if ($_SESSION['role'] === 'SuperAdmin') {
    // Use selected barangay or default to first allowed barangay
    $selectedBarangay = $_SESSION['selected_barangay_id'] ?? ($barangayIds[0] ?? null);
} else {
    $selectedBarangay = $assignedBarangayId;
}

// Redirect if no barangay set (should not happen normally)
if (!$selectedBarangay) {
    die("No barangay selected or assigned.");
}

// DB connection assumed included in access_control or include here
include './includes/db_connection.php';

// Fetch announcements filtered by selected barangay
$sql = "SELECT 
            ROW_NUMBER() OVER (ORDER BY date DESC) AS row_number,
            announcement_id, title, description, date, image, status 
        FROM announcement 
        WHERE barangay_id = ?
        ORDER BY date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $selectedBarangay);
$stmt->execute();
$result = $stmt->get_result();

$announcements = [];
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Announcements - Barangay Management System</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="includes/general.css" />
  <link rel="stylesheet" href="includes/header.css" />
  <link rel="stylesheet" href="includes/sidebar.css" />
  <link rel="stylesheet" href="css/announcement.css" />
</head>
<body>
<?php include('includes/header.php'); ?> 
<?php include('includes/sidebar.php'); ?>

<main class="main-container">
    <h1>Announcements</h1>

    <?php if ($_SESSION['role'] === 'SuperAdmin' && !empty($barangayIds)): ?>
    <form method="POST" class="mb-4">
        <label for="barangay_selector" class="form-label">Select Barangay:</label>
        <select name="barangay_selector" id="barangay_selector" class="form-select" onchange="this.form.submit()">
            <?php foreach ($barangayIds as $barangay_id): ?>
                <?php 
                    // Replace this with actual barangay name from DB if available
                    $barangayName = "Barangay " . $barangay_id; 
                    $selected = ($barangay_id == $selectedBarangay) ? 'selected' : '';
                ?>
                <option value="<?= htmlspecialchars($barangay_id) ?>" <?= $selected ?>><?= htmlspecialchars($barangayName) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php endif; ?>

    <!-- Announcements Table -->
    <div class="announcement-table-container">
        <div class="sort-options-container d-flex justify-content-between align-items-center mb-3">
            <div>
                <label for="sort-by" class="sort-label">Sort:</label>
                <select id="sort-by" class="form-control">
                    <option value="default">Sort by Default(ID)</option>
                    <option value="title">Sort by Title</option>
                    <option value="date">Sort by Date</option>
                    <option value="status">Sort by Status</option>
                </select>
            </div>
            <div class="search-icon-container">
                <i class="material-icons-outlined">search</i>
                <input type="text" id="search-announcements" class="form-control search-input" placeholder="Search..." aria-label="Search" />
            </div>
        </div>
        <table class="announcement-table table-bordered">
          <thead>
            <tr>
              <th>No.</th>
              <th>Title</th>
              <th>Description</th>
              <th>Date</th>
              <th>Image</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="announcement-table-body">
            <?php if (!empty($announcements)): ?>
                <?php foreach ($announcements as $announcement): ?>
                    <tr data-id="<?= htmlspecialchars($announcement['announcement_id']) ?>">
                        <td><?= htmlspecialchars($announcement['row_number']) ?></td>
                        <td><?= htmlspecialchars($announcement['title']) ?></td>
                        <td><?= htmlspecialchars($announcement['description']) ?></td>
                        <td><?= htmlspecialchars($announcement['date']) ?></td>
                        <td>
                            <img src="crudAnnouncement/uploads/<?= htmlspecialchars($announcement['image']) ?>" alt="Announcement Image" 
                                class="table-image clickable-image" 
                                data-bs-toggle="modal" 
                                data-bs-target="#imageModal-<?= htmlspecialchars($announcement['announcement_id']) ?>">
                        </td>
                        <td>
                            <select class="form-select" onchange="changeAnnouncementStatus(<?= htmlspecialchars($announcement['announcement_id']) ?>, this.value)">
                                <option value="Active" <?= $announcement['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= $announcement['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </td>
                        <td>
                            <button class="btn btn-info" onclick="openEditAnnouncement(<?= htmlspecialchars($announcement['announcement_id']) ?>)">Edit</button>
                            <button class="btn btn-danger" onclick="deleteAnnouncement(<?= htmlspecialchars($announcement['announcement_id']) ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">No announcements found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
    </div>

      <!-- Modal Container -->
      <div id="modals-container">
      <?php if (!empty($announcements)): ?>
          <?php foreach ($announcements as $announcement): ?>
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
          <?php endforeach; ?>
      <?php endif; ?>
      </div>


    <!-- Add Announcement Button -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
      Add New Announcement
    </button>

    <!-- Add Announcement Modal -->
    <div class="modal fade" id="addAnnouncementModal" tabindex="-1" aria-labelledby="addAnnouncementModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addAnnouncementModalLabel">Add New Announcement</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
            <div class="modal-body">
              <form id="addAnnouncementForm" enctype="multipart/form-data">
                <div class="mb-3">
                  <label for="title" class="form-label">Title:</label>
                  <input type="text" name="title" id="title" class="form-control" required>
            </div>
                <div class="mb-3">
                  <label for="add_date" class="form-label">Date:</label>
                  <input type="date" name="date" id="add_date" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label for="description" class="form-label">Description:</label>
                  <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                  <label for="image" class="form-label">Image:</label>
                  <input type="file" name="image" id="image" class="form-control" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status:</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
              <button type="submit" class="btn btn-success">Add Announcement</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Announcement Modal -->
    <div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAnnouncementModalLabel">Edit Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAnnouncementForm" enctype="multipart/form-data">
                    <input type="hidden" name="announcement_id" id="edit_announcement_id">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Title:</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_date" class="form-label">Date:</label>
                        <input type="date" name="date" id="edit_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description:</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_image" class="form-label">Image:</label>
                        <input type="file" name="image" id="edit_image" class="form-control" accept="image/*">
                        <img id="edit_image_preview" src="" alt="Current Image" class="img-fluid mt-2">
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status:</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-success" onclick="updateAnnouncement()">Update Announcement</button>
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
  <script src="crudAnnouncement/fetch_announcement.js"></script>
  <script src="crudAnnouncement/add_announcement.js"></script>
  <script src="crudAnnouncement/update_announcement.js"></script>
  <script src="crudAnnouncement/delete_announcement.js"></script>
  <script src="crudAnnouncement/change_status_announcement.js"></script>
  <script src="crudAnnouncement/sort.js"></script>
</body>
</html>