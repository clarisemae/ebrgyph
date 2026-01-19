<?php
session_start();

require_once './includes/access_control.php';
include './includes/db_connection.php';

// Check for valid login and role
if (!isset($_SESSION['admin_id']) || 
    !isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['Staff', 'Admin', 'SuperAdmin']) || 
    ($_SESSION['role'] !== 'SuperAdmin' && !isset($_SESSION['barangay_id']))) {
    header("Location: admin_login.php");
    exit();
}

// Handle barangay selection by SuperAdmin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barangay_selector']) && $_SESSION['role'] === 'SuperAdmin') {
    $_SESSION['selected_barangay_id'] = $_POST['barangay_selector'];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Determine which barangay to filter users by
if ($_SESSION['role'] === 'SuperAdmin') {
    $barangayId = $_SESSION['selected_barangay_id'] ?? null;

    // If no barangay selected yet, default to first allowed barangay
    if (!$barangayId) {
        if (!empty($_SESSION['barangay_ids']) && is_array($_SESSION['barangay_ids'])) {
            $barangayId = $_SESSION['barangay_ids'][0];
            $_SESSION['selected_barangay_id'] = $barangayId;
        } else {
            // No barangays assigned - show no users
            $barangayId = null;
        }
    }

    // Get list of allowed barangays to show in dropdown
    $allowedBarangays = $_SESSION['barangay_ids'] ?? [];
} else {
    // For Staff/Admin
    $barangayId = $_SESSION['barangay_id'];
    $allowedBarangays = []; // no dropdown for non-SuperAdmin
}

// Fetch users filtered by barangay_id
$users = [];
if ($barangayId !== null) {
    $sql = "SELECT id, full_name, birthdate, gender, civil_status, email, phone, street, barangay, municipality, city, region, emergency_name, emergency_address, emergency_relationship, emergency_phone, created_at FROM barangay_registration WHERE barangay_id = ? ORDER BY id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $barangayId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    $stmt->close();
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Accounts - Barangay Management System</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="includes/general.css" />
  <link rel="stylesheet" href="includes/header.css" />
  <link rel="stylesheet" href="includes/sidebar.css" />
  <link rel="stylesheet" href="css/user_account.css" />
</head>
<body>
  <?php include('includes/header.php'); ?> <!-- Include Header -->
  <?php include('includes/sidebar.php'); ?> <!-- Include Sidebar -->

  <main class="main-container">
    <h1>User Accounts</h1>

    <?php if ($_SESSION['role'] === 'SuperAdmin'): ?>
      <form method="POST" action="" class="mb-4">
        <label for="barangay_selector" class="form-label">Select Barangay:</label>
        <select name="barangay_selector" id="barangay_selector" class="form-select" onchange="this.form.submit()">
          <?php foreach ($allowedBarangays as $bId): ?>
            <?php 
              // For display, you might want to fetch barangay names from DB, here just using ID:
              $barangayName = "Barangay " . htmlspecialchars($bId);
              $selected = ($bId == $barangayId) ? 'selected' : '';
            ?>
            <option value="<?= htmlspecialchars($bId) ?>" <?= $selected ?>><?= $barangayName ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    <?php endif; ?>

    <div class="user-table-container">
      <div class="sort-options-container d-flex justify-content-between align-items-center mb-3">
        <div>
          <label for="sort-by" class="sort-label">Sort:</label>
          <select id="sort-by" class="form-control">
            <option value="default">Sort by Default(ID)</option>
            <option value="name">Sort by Name</option>
            <option value="gender">Sort by Gender</option>
            <option value="civil_status">Sort by Civil Status</option>
          </select>
        </div>
        <div class="search-icon-container d-flex align-items-center">
          <i class="material-icons-outlined">search</i>
          <input type="text" id="search-users" class="form-control search-input" placeholder="Search..." aria-label="Search" />
        </div>
      </div>

      <table class="user-table table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Birthdate</th>
            <th>Gender</th>
            <th>Civil Status</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Barangay</th>
            <th>Municipality</th>
            <th>City</th>
            <th>Region</th>
            <th>Emergency Name</th>
            <th>Emergency Address</th>
            <th>Emergency Relationship</th>
            <th>Emergency Phone</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="user-table-body">
          <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
              <tr data-id="<?= htmlspecialchars($user['id']) ?>">
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['full_name']) ?></td>
                <td><?= htmlspecialchars($user['birthdate']) ?></td>
                <td><?= htmlspecialchars($user['gender']) ?></td>
                <td><?= htmlspecialchars($user['civil_status']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['phone']) ?></td>
                <td><?= htmlspecialchars($user['street']) ?></td>
                <td><?= htmlspecialchars($user['barangay']) ?></td>
                <td><?= htmlspecialchars($user['municipality']) ?></td>
                <td><?= htmlspecialchars($user['city']) ?></td>
                <td><?= htmlspecialchars($user['region']) ?></td>
                <td><?= htmlspecialchars($user['emergency_name']) ?></td>
                <td><?= htmlspecialchars($user['emergency_address']) ?></td>
                <td><?= htmlspecialchars($user['emergency_relationship']) ?></td>
                <td><?= htmlspecialchars($user['emergency_phone']) ?></td>
                <td><?= htmlspecialchars($user['created_at']) ?></td>
                <td>
                  <button class="btn btn-danger" onclick="deleteUser(<?= htmlspecialchars($user['id']) ?>)">Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="18">No user records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/utils.js"></script>
  <script src="includes/sidebar.js"></script>
  <script src="includes/header.js"></script>
  <script src="crudUserAccount/fetch_user.js"></script>
  <script src="crudUserAccount/sort.js"></script>
  <script src="crudUserAccount/delete_user.js"></script>
</body>
</html>
