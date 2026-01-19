<?php
session_start(); // Start the session

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "ebrgyph";

$conn = mysqli_connect($host, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$username = $_SESSION['username'];
$sql_users = "SELECT id FROM users WHERE username = ?";
$stmt_users = $conn->prepare($sql_users);
$stmt_users->bind_param("s", $username);
$stmt_users->execute();
$result_users = $stmt_users->get_result();
$user_data = $result_users->fetch_assoc();
if (!$user_data) {
    die("No user data found for username: " . htmlspecialchars($username));
}
$user_id = $user_data['id'];
$stmt_users->close();

$sql_registration = "SELECT profile_picture FROM barangay_registration WHERE id = ?";
$stmt_registration = $conn->prepare($sql_registration);
$stmt_registration->bind_param("i", $user_id);
$stmt_registration->execute();
$result_registration = $stmt_registration->get_result();
$registration_data = $result_registration->fetch_assoc();
$profile_picture = !empty($registration_data['profile_picture']) ? "uploads/" . $registration_data['profile_picture'] : "default.jpg";
$stmt_registration->close();

// Fetch barangay_id of the current user
$barangay_id = null;
$sql_user_barangay = "SELECT barangay_id FROM users WHERE id = ?";
$stmt_user_barangay = $conn->prepare($sql_user_barangay);
$stmt_user_barangay->bind_param("i", $user_id);
$stmt_user_barangay->execute();
$result_user_barangay = $stmt_user_barangay->get_result();
if ($row = $result_user_barangay->fetch_assoc()) {
    $barangay_id = $row['barangay_id'];
}
$stmt_user_barangay->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $document_type = $_POST['document_type'];
    $fullname = $_POST['fullname'];
    $age = $_POST['age'];
    $postal_address = $_POST['postal_address'];
    $requested_date = $_POST['requested_date'];
    $email = $_POST['email'];
    $non_residency_purpose = $_POST['non_residency_purpose'];
    $id_type = $_POST['id-type'];
    $id_photo_url = null;

    if (isset($_FILES['id_photo']) && $_FILES['id_photo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "crudNonResidency/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $id_photo_url = $target_dir . basename($_FILES['id_photo']['name']);
        move_uploaded_file($_FILES['id_photo']['tmp_name'], $id_photo_url);
    }

    $stmt = $conn->prepare("INSERT INTO certificate_of_non_residency (document_type, fullname, age, postal_address, requested_date, email, non_residency_purpose, id_type, id_photo_url, barangay_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissssssi", $document_type, $fullname, $age, $postal_address, $requested_date, $email, $non_residency_purpose, $id_type, $id_photo_url, $barangay_id);

    if ($stmt->execute()) {
        header("Location: requestsubmission.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document Request - Non-Residency</title>
    <link rel="stylesheet" href="request.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@800&display=swap" rel="stylesheet" />
</head>
<body>
    <div class="navbar">
        <div class="navbar-left">
            <a href="#ebrgy" id="ebrgy-link">e-brgyPH</a>
        </div>
        <div class="navbar-center">
            <a href="home.php" class="nav-link">Home</a>
            <a href="request_general.php" class="nav-link active">Document Request</a>
            <a href="report.php" class="nav-link">Incident Report</a>
            <a href="officials.php" class="nav-link">Barangay Officials</a>
            <a href="insights.php" class="nav-link">Comments/Insights</a>
        </div>
        <div class="navbar-right">
            <a href="profile.php" class="profile-circle">
                <img src="<?php echo $profile_picture; ?>" alt="User Profile" style="width:40px; height:40px; border-radius:50%;" />
            </a>
            <button id="logout-btn-large" class="logout-large">Logout</button>
        </div>
    </div>

    <section id="document-request-form">
        <h2>Non-Residency Document Request</h2>
        <form action="certificate_of_non_residency.php" method="POST" enctype="multipart/form-data">
            <label for="document_type">Document Type:</label>
            <input type="text" id="document_type" name="document_type" value="CERTIFICATE OF NON-RESIDENCY" readonly />

            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullname" placeholder="ex. Juan Dela Cruz" required />

            <label for="age">Age:</label>
            <input type="number" id="age" name="age" min="1" max="120" required placeholder="Enter your age" />

            <label for="postal_address">Postal Address:</label>
            <input type="text" id="postal_address" name="postal_address" placeholder="Enter your full address" required />

            <label for="requested_date">Requested Date:</label>
            <input type="date" id="requested_date" name="requested_date" required>

            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" placeholder="example@domain.com" required />

            <label for="non_residency_purpose">Purpose of Non-Residency:</label>
            <input type="text" id="non_residency_purpose" name="non_residency_purpose" placeholder="Specify purpose" required />

            <label for="id-type">Select ID Type:</label>
            <select id="id-type" name="id-type" required>
                <option value="" disabled selected>Select Valid ID</option>
                <option value="Barangay_ID">Barangay ID</option>
                <option value="National_ID">National ID</option>
                <option value="Passport">Passport</option>
                <option value="Driver's_License">Driver's License</option>
                <option value="SSS_ID">SSS ID</option>
                <option value="PRC_ID">PRC ID</option>
                <option value="Senior_Citizen_ID">Senior Citizen ID</option>
                <option value="School_ID">School ID</option>
                <option value="PhilHealth_ID">PhilHealth ID</option>
                <option value="PWD_ID">PWD ID</option>
            </select>

            <!-- Checkbox for subject same as requestor -->
            <div class="checkbox-container">
                <input type="checkbox" id="is_subject_same" name="is_subject_same" />
                <label for="is_subject_same">Subject is the same as the requestor</label>
            </div>
            <div id="subject-info">
                <label for="subject_fullname">Subject Full Name:</label>
                <input type="text" id="subject_fullname" name="subject_fullname" placeholder="Enter subject's full name" />

                <label for="subject_dob">Subject Date of Birth:</label>
                <input type="date" id="subject_dob" name="subject_dob" />

                <label for="subject_age">Subject Age:</label>
                <input type="number" id="subject_age" name="subject_age" min="0" max="120" placeholder="Enter subject age" />
            </div>

            <label for="id-photo">Attach Valid Photo:</label>
            <input type="file" id="id-photo" name="id_photo" accept="image/*" class="input-field" />

            <button type="submit" class="submitbut">Submit Request</button>
        </form>
    </section>

    <div class="back-button">
        <button onclick="window.location.href='request_general.php'">← Back to Request Page</button>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Subject details toggle logic
            const subjectCheckbox = document.getElementById('is_subject_same');
            const subjectInfo = document.getElementById('subject-info');
            const subjectAge = document.getElementById('subject_age');
            const subjectFullname = document.getElementById('subject_fullname');
            const subjectDOB = document.getElementById('subject_dob');

            function toggleSubjectFields() {
                if (subjectCheckbox.checked) {
                    subjectInfo.style.display = 'none';
                    subjectAge.required = false;
                    subjectFullname.required = false;
                    subjectDOB.required = false;
                } else {
                    subjectInfo.style.display = 'block';
                    subjectAge.required = true;
                    subjectFullname.required = true;
                    subjectDOB.required = true;
                }
            }
            // Initial state on page load
            toggleSubjectFields();
            // Listen for changes
            subjectCheckbox.addEventListener('change', toggleSubjectFields);

            // Burger menu logic (only if those elements exist)
            const burgerMenu = document.getElementById('burger-menu');
            const burgerDropdown = document.getElementById('burger-dropdown');
            if (burgerMenu && burgerDropdown) {
                burgerMenu.addEventListener('click', () => {
                    const isExpanded = burgerMenu.getAttribute('aria-expanded') === 'true';
                    burgerMenu.setAttribute('aria-expanded', !isExpanded);
                    burgerDropdown.setAttribute('aria-hidden', isExpanded);
                    burgerDropdown.style.display = isExpanded ? 'none' : 'flex';
                });
            }
        });
    </script>
</body>
</html>
