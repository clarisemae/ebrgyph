<?php
session_start();

require_once './includes/access_control.php';
include './includes/db_connection.php';

// Check for valid login and role in one go
if (!isset($_SESSION['admin_id']) || 
    !isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['Staff', 'Admin', 'SuperAdmin']) || 
    ($_SESSION['role'] !== 'SuperAdmin' && !isset($_SESSION['barangay_id']))) {
    header("Location: admin_login.php");
    exit();
}

// Handle barangay selector form submission for SuperAdmin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barangay_selector']) && $_SESSION['role'] === 'SuperAdmin') {
    $_SESSION['selected_barangay_id'] = $_POST['barangay_selector'];
    // Redirect to avoid form resubmission
    header("Location: admin_insights.php");
    exit();
}

// Determine which barangay ID to use for filtering:
// For SuperAdmin use selected_barangay_id if set, otherwise default to first allowed barangay
// For Admin/Staff use their assigned barangay_id
if ($_SESSION['role'] === 'SuperAdmin') {
    $barangayIds = $_SESSION['barangay_ids']; // Array of allowed barangays for SuperAdmin

    // Set selected barangay to the session one or default to first allowed barangay
    $selectedBarangay = $_SESSION['selected_barangay_id'] ?? $barangayIds[0];

    // Validate that selected barangay is allowed
    if (!in_array($selectedBarangay, $barangayIds)) {
        $selectedBarangay = $barangayIds[0];
        $_SESSION['selected_barangay_id'] = $selectedBarangay;
    }
} else {
    // Staff/Admin single barangay
    $selectedBarangay = $_SESSION['barangay_id'];
}

// Fetch insights filtered by the selected barangay
$sql = "SELECT
            id,
            name,
            email,
            date,
            type,
            comment,
            submitted_at,
            ROW_NUMBER() OVER (ORDER BY submitted_at DESC) AS row_number
        FROM insights
        WHERE barangay_id = ?
        ORDER BY submitted_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $selectedBarangay);
$stmt->execute();
$result = $stmt->get_result();

$insights = [];
while ($row = $result->fetch_assoc()) {
    $insights[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Resident Insights - Barangay Management System</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="includes/general.css" />
  <link rel="stylesheet" href="includes/header.css" />
  <link rel="stylesheet" href="includes/sidebar.css" />
  <link rel="stylesheet" href="css/insights.css" />
</head>
<body>
<?php include('includes/header.php'); ?>
<?php include('includes/sidebar.php'); ?>

<main class="main-container">
  <h1>Insights Records</h1>

  <?php if ($_SESSION['role'] === 'SuperAdmin'): ?>
    <!-- Barangay Selector Form -->
    <form method="POST" action="" class="mb-4 w-25">
      <label for="barangay_selector" class="form-label">Select Barangay:</label>
      <select name="barangay_selector" id="barangay_selector" class="form-select" onchange="this.form.submit()">
        <?php foreach ($barangayIds as $barangay_id): 
          // Replace this with real barangay names from your DB if available
          $barangayName = "Barangay " . $barangay_id;
          $selected = ($barangay_id == $selectedBarangay) ? "selected" : "";
        ?>
          <option value="<?= htmlspecialchars($barangay_id) ?>" <?= $selected ?>>
            <?= htmlspecialchars($barangayName) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  <?php endif; ?>

  <div class="insights-table-container">
    <div class="sort-options-container d-flex justify-content-between align-items-center mb-3">
      <div>
        <label for="sort-by" class="sort-label">Sort:</label>
        <select id="sort-by" class="form-control">
          <option value="default">Sort by Default (Number)</option>
          <option value="name">Sort by Name</option>
          <option value="date">Sort by Date</option>
          <option value="type">Sort by Type</option>
        </select>
      </div>
      <div class="search-icon-container">
        <i class="material-icons-outlined">search</i>
        <input type="text" id="search-insights" class="form-control search-input" placeholder="Search..." aria-label="Search" />
        <span class="search-icon"></span>
      </div>
    </div>

    <table class="insights-table table table-bordered">
      <thead>
        <tr>
          <th>No.</th>
          <th>Name</th>
          <th>Email</th>
          <th>Date</th>
          <th>Type</th>
          <th>Comment</th>
          <th>Time</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody id="insights-table-body">
        <?php if (!empty($insights)): ?>
          <?php foreach ($insights as $insight): ?>
            <tr data-id="<?= htmlspecialchars($insight['id']) ?>">
              <td><?= htmlspecialchars($insight['row_number']) ?></td>
              <td><?= htmlspecialchars($insight['name']) ?></td>
              <td><?= htmlspecialchars($insight['email']) ?></td>
              <td><?= htmlspecialchars($insight['date']) ?></td>
              <td><?= htmlspecialchars($insight['type']) ?></td>
              <td><?= htmlspecialchars($insight['comment']) ?></td>
              <td><?= htmlspecialchars($insight['submitted_at']) ?></td>
              <td>
                <button class="btn btn-info" onclick="replyToInsight(<?= htmlspecialchars($insight['id']) ?>)">Reply</button>
                <button class="btn btn-danger" onclick="deleteInsight(<?= htmlspecialchars($insight['id']) ?>)">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="text-center">No Insights found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Reply Insights Modal -->
  <div class="modal fade" id="replyInsightModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="replyModalLabel">Reply to Insight</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="reply_id" />
          <div class="mb-3">
            <label for="reply_comment" class="form-label">Your Reply</label>
            <textarea id="reply_comment" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" onclick="submitReply()">Send Reply</button>
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
<script src="crudInsights/fetch_insights.js"></script>
<script src="crudInsights/reply_insights.js"></script>
<script src="crudInsights/delete_insights.js"></script>
<script src="crudInsights/sort_insights.js"></script>

</body>
</html>
