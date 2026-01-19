<?php
session_start();

require_once './includes/access_control.php';
include './includes/db_connection.php';

// Check if user is logged in and has a valid role
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['Admin', 'SuperAdmin'])) {

    // If staff tries to access, set error and redirect
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'Staff') {
        $_SESSION['error_message'] = "You do not have permission to access this page.";
    }
    header("Location: ebrgydashboard.php");
    exit();
}

$role = $_SESSION['role']; 
$barangayId = $_SESSION['barangay_id'] ?? null;
$currentUserRole = $_SESSION['role'] ?? null;

// Define barangay options array for SuperAdmin
$barangayOptions = [];
if ($currentUserRole === 'SuperAdmin' && !empty($_SESSION['barangay_ids'])) {
    $barangayOptions = $_SESSION['barangay_ids'];
}

// Handle barangay selector submission only for SuperAdmin
if ($role === 'SuperAdmin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barangay_selector'])) {
    $_SESSION['selected_barangay_id'] = $_POST['barangay_selector'];
    header("Location: manage_accounts.php");
    exit();
}

// Determine the barangay ID to filter accounts by
if ($role === 'SuperAdmin') {
    $barangayIds = $_SESSION['barangay_ids'] ?? [];

    if (empty($barangayIds)) {
        // No barangays assigned; no accounts to show
        $accounts = [];
    } else {
        // Use selected barangay if set, else default to first barangay
        $selectedBarangay = $_SESSION['selected_barangay_id'] ?? $barangayIds[0];
        $barangayId = $selectedBarangay;
    }
} else {
    // For Admin, Staff, use their assigned barangay
    if (!$barangayId) {
        // If barangay_id missing, handle error or redirect
        $_SESSION['error_message'] = "Barangay not assigned.";
        header("Location: ebrgydashboard.php");
        exit();
    }
}

// Prepare and execute SQL query to fetch accounts for the determined barangay
$sql = "SELECT Id AS id, adminName, adminUsername, email, phone, role, created, updated 
        FROM admin WHERE barangay_id = ? ORDER BY Id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $barangayId);
$stmt->execute();

$result = $stmt->get_result();

$accounts = [];
while ($row = $result->fetch_assoc()) {
    $accounts[] = $row;
}

$stmt->close();
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Accounts - Barangay Management System</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="includes/general.css">
  <link rel="stylesheet" href="includes/header.css">
  <link rel="stylesheet" href="includes/sidebar.css">
  <link rel="stylesheet" href="css/manage_accounts.css">
</head>
<body>
  <?php include('includes/header.php'); ?> <!-- Include Header -->
  <?php include('includes/sidebar.php'); ?> <!-- Include Sidebar -->

  <!-- Main Content Section -->
  <main class="main-container">
    <h1>Manage Accounts</h1>
        
    <?php if ($role === 'SuperAdmin' && !empty($barangayIds)): ?>
    <form method="POST" action="" class="mb-3 w-25">
        <label for="barangay_selector" class="form-label">Select Barangay:</label>
        <select name="barangay_selector" id="barangay_selector" class="form-select" onchange="this.form.submit()">
            <?php foreach ($barangayIds as $id): 
                $barangayName = "Barangay " . htmlspecialchars($id); // Replace with actual barangay names from DB if available
                $selected = ($id == $barangayId) ? 'selected' : '';
            ?>
                <option value="<?= htmlspecialchars($id) ?>" <?= $selected ?>><?= $barangayName ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php endif; ?>

    <!-- Manage Accounts Table -->
    <div class="manage-table-container">
      <div class="sort-options-container d-flex justify-content-between align-items-center">
        <div>
          <!-- Sorting Dropdown -->
          <label for="sort-by" class="sort-label">Sort:</label>
          <select id="sort-by" class="form-control">
            <option value="default">Sort by Default(ID)</option>
            <option value="name">Sort by Name</option>
            <option value="role">Sort by Role</option>
            <option value="date">Sort by Date Created</option>
          </select>
        </div>
        <div class="search-icon-container">
          <!-- Search Input with Icon -->
          <i class="material-icons-outlined">search</i>
          <input type="text" id="search-accounts" class="form-control search-input" placeholder="Search..." aria-label="Search">
        </div>
      </div>

      <table class="manage-table table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Username</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Role</th>
              <th>Date Created</th>
              <th>Date Updated</th>
              <th>Actions</th>
            </tr>
          </thead>

        <tbody id="manage-table-body">
        <?php if (!empty($accounts)): ?>
            <?php foreach ($accounts as $account): ?>
              <tr data-id="<?= htmlspecialchars($account['id']) ?>">
                <td><?= htmlspecialchars($account['id']) ?></td>
                <td><?= htmlspecialchars($account['adminName']) ?></td>
                <td><?= htmlspecialchars($account['adminUsername']) ?></td>
                <td><?= htmlspecialchars($account['email']) ?></td>
                <td><?= htmlspecialchars($account['phone']) ?></td>
                <td><?= htmlspecialchars($account['role']) ?></td>
                <td><?= htmlspecialchars($account['created']) ?></td>
                <td><?= htmlspecialchars($account['updated']) ?></td>
                <td>
                  <button class="btn btn-info" onclick="openEditAccount(<?= htmlspecialchars($account['id']) ?>)">Edit</button>
                  <button class="btn btn-danger" onclick="deleteManage(<?= htmlspecialchars($account['id']) ?>)">Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
              <td colspan="9">No accounts found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

<!-- Edit Account Modal -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editAccountModalLabel">Edit Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editAccountForm">
          <input type="hidden" name="id" id="account_id">

          <?php if ($currentUserRole === 'SuperAdmin'): ?>
          <div class="mb-3">
            <label for="add_barangay" class="form-label">Select Barangay:</label>
            <select name="barangay_id" id="add_barangay" class="form-select" required>
              <?php foreach ($barangayOptions as $barangayIdOption): ?>
                <option value="<?= htmlspecialchars($barangayIdOption) ?>">
                  Barangay <?= htmlspecialchars($barangayIdOption) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>

          <div class="mb-3">
              <label for="adminName" class="form-label">Name:</label>
              <input type="text" name="adminName" id="adminName" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="adminUsername" class="form-label">Username:</label>
              <input type="text" name="adminUsername" id="adminUsername" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="email" class="form-label">Email:</label>
              <input type="email" name="email" id="email" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="phone" class="form-label">Phone:</label>
              <input type="tel" 
                     name="phone" 
                     id="phone" 
                     class="form-control" 
                     required 
                     pattern="^(09|\+639)\d{9}$" 
                     placeholder="e.g., 09171234567 or +639171234567">
              <small class="form-text text-muted">Enter a valid Philippine mobile number (e.g., 09171234567 or +639171234567).</small>
          </div>
            <div class="mb-3">
              <label for="role" class="form-label">Role:</label>
              <select name="role" id="role" class="form-select" required>
              <option value="">Select Role</option>
              <option value="Staff">Staff</option>
              <option value="Admin">Admin</option>
              <?php if ($currentUserRole === 'SuperAdmin'): ?>
                <option value="SuperAdmin">SuperAdmin</option>
              <?php endif; ?>
            </select>
            </div>
          <button type="button" onclick="updateAccount()" class="btn btn-success">Update Account</button>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- Add Account Button -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
  Add New Account
</button>

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addAccountModalLabel">Add New Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Add Account Form -->
        <form id="addAccountForm">

          <!-- Barangay selector: only show if current logged-in user is SuperAdmin -->
          <?php if ($currentUserRole === 'SuperAdmin'): ?>
          <div class="mb-3">
            <label for="add_barangay" class="form-label">Select Barangay:</label>
            <select name="barangay_id" id="add_barangay" class="form-select" required>
              <?php foreach ($barangayOptions as $barangayIdOption): ?>
                <option value="<?= htmlspecialchars($barangayIdOption) ?>">
                  Barangay <?= htmlspecialchars($barangayIdOption) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>

            <div class="mb-3">
                <label for="add_adminName" class="form-label">Name:</label>
                <input type="text" name="adminName" id="add_adminName" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="add_adminUsername" class="form-label">Username:</label>
                <input type="text" name="adminUsername" id="add_adminUsername" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="add_email" class="form-label">Email:</label>
                <input type="email" name="email" id="add_email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="add_phone" class="form-label">Enter a valid Philippine mobile number:</label>
              <input type="tel" 
                     name="phone" 
                     id="add_phone" 
                     class="form-control" 
                     required 
                     pattern="^(09|\+639)\d{9}$" 
                     placeholder="e.g., 09171234567 or +639171234567">
          </div>
          <div class="mb-3">
            <label for="add_role" class="form-label">Role:</label>
            <select name="add_role" id="add_role" class="form-select" required>
              <option value="">Select Role</option>
              <option value="Staff">Staff</option>
              <option value="Admin">Admin</option>
              <?php if ($currentUserRole === 'SuperAdmin'): ?>
                <option value="SuperAdmin">SuperAdmin</option>
              <?php endif; ?>
            </select>
          </div>

            <div class="mb-3">
                <label for="add_password" class="form-label">Password:</label>
                <input type="password" name="password" id="add_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Add Account</button>
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
  <script src="crudManage/add_manage.js"></script>
  <script src="crudManage/fetch_manage.js"></script>
  <script src="crudManage/update_manage.js"></script>
  <script src="crudManage/delete_manage.js"></script>
  <script src="crudManage/sort_manage.js"></script>

</body>
</html>