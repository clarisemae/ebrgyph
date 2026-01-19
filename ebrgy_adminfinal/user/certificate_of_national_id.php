<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "ebrgyph";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the logged-in user's profile picture dynamically
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

// Fetch profile picture from barangay_registration table
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form inputs
    $document_type = $_POST['document_type'];
    $fullname = $_POST['fullname'];
    $age = $_POST['age'];
    $status = $_POST['status'];
    $citizen = $_POST['citizen'];
    $postal_address = $_POST['postal_address'];
    $requested_date = $_POST['requested_date'];
    $email = $_POST['email'];
    $national_id_purpose = isset($_POST['national_id_purpose']) ? implode(', ', $_POST['national_id_purpose']) : null;
    $other_purpose = !empty($_POST['other_purpose']) ? $_POST['other_purpose'] : null;
    $id_type = $_POST['id-type'];
    $id_photo_url = null;

    // Handle file upload
    if (isset($_FILES['id_photo']) && $_FILES['id_photo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "crudNationalID/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $id_photo_url = $target_dir . basename($_FILES['id_photo']['name']);
        move_uploaded_file($_FILES['id_photo']['tmp_name'], $id_photo_url);
    }

    $is_subject_same = isset($_POST['is_subject_same']) ? 1 : 0;
    $subject_fullname = $is_subject_same ? $fullname : ($_POST['subject_fullname'] ?? null);
    $subject_dob = $is_subject_same ? null : ($_POST['date_of_birth'] ?? null);
    $subject_age = $is_subject_same ? null : ($_POST['subject_age'] ?? null);

    // Insert into the database
    $stmt = $conn->prepare("INSERT INTO certificate_of_national_id (
        document_type, fullname, age, status, citizen, postal_address, requested_date,
        email, national_id_purpose, other_purpose, id_type, id_photo_url,
        is_subject_same, subject_fullname, subject_dob, subject_age, barangay_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssissssssssssssii",
        $document_type,
        $fullname,
        $age,
        $status,
        $citizen,
        $postal_address,
        $requested_date,
        $email,
        $national_id_purpose,
        $other_purpose,
        $id_type,
        $id_photo_url,
        $is_subject_same,
        $subject_fullname,
        $subject_dob,
        $subject_age,
        $barangay_id
    );

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
    <title>Document Request</title>
    <link rel="stylesheet" href="request.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@800&display=swap" rel="stylesheet" />
</head>

<body>
    <div class="navbar">
        <div class="navbar-left">
            <a href="#ebrgy" id="ebrgy-link">e-brgyPH</a>
        </div>

        <div class="navbar-center">
            <a href="home.php" class="nav-link active">Home</a>
            <a href="request_general.php" class="nav-link">Document Request</a>
            <a href="report.php" class="nav-link">Incident Report</a>
            <a href="officials.php" class="nav-link">Barangay Officials</a>
            <a href="insights.php" class="nav-link">Comments/Insights</a>
        </div>

        <div class="navbar-right">
            <a href="profile.php" class="profile-circle">
                <img src="<?php echo $profile_picture; ?>" alt="User Profile" />
            </a>
            <a href="logout.php" id="logout-btn-large" class="logout-large">Logout</a>
        </div>
    </div>

    <section id="document-request-form">
        <h2>Document Request</h2>
        <form action="certificate_of_national_id.php" method="POST" enctype="multipart/form-data">
            <label for="document_type">Document Type:</label>
            <input type="text" id="document_type" name="document_type" value="CERTIFICATE OF NATIONAL ID" readonly />

            <!-- Full Name -->
            <label for="fullname">Full Name</label>
            <input
                type="text"
                id="fullname"
                name="fullname"
                placeholder="ex.Juan Dela Cruz"
                required />

            <!-- Age -->
            <label for="age">Age</label>
            <input
                type="number"
                id="age"
                name="age"
                min="1"
                max="120"
                required
                placeholder="Enter your age" />

            <!-- Status -->
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="" disabled selected>Select Status</option>
                <option value="single">Single</option>
                <option value="married">Married</option>
                <option value="widowed">Widowed</option>
                <option value="separated">Separated</option>
                <option value="divorced">Divorced</option>
            </select>

            <!-- Citizen -->
            <label for="citizen">Citizen</label>
            <select id="citizen" name="citizen" required>
                <option value="" disabled selected>Select Citizenship</option>
                <option value="Filipino">Filipino</option>
                <option value="Non-Filipino">Non-Filipino</option>
            </select>

            <!-- Postal Address -->
            <label for="postal_address">Postal Address</label>
            <input
                type="text"
                id="postal_address"
                name="postal_address"
                placeholder="House/Unit Number, Street Name, City"
                title="Enter in the format: House/Unit Number, Street Name, City"
                required />

            <!-- Subject Informations -->
            <!-- Checkbox to indicate if subject is same as the requestor -->
            <div class="checkbox-container">
                <input type="checkbox" id="is_subject_same" name="is_subject_same" />
                <label for="is_subject_same">Subject is the same as the requestor</label>
            </div>
            <div id="subject-info">
                <label for="subject_fullname">Subject Full Name:</label>
                <input type="text" id="subject_fullname" name="subject_fullname" placeholder="Enter subject name" />

                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" />

                <label for="subject_age">Age:</label>
                <input type="number" id="subject_age" name="subject_age" min="0" max="120" placeholder="Enter subject age" />
            </div>

            <!-- Purpose of National ID Certificate -->
            <div class="input-item">
                <label>Purpose of National ID Certification:</label>
                <div class="checkbox-container">
                    <div class="checkbox-item">
                        <input type="checkbox" id="id_government" name="national_id_purpose[]" value="Identification ID/Government ID">
                        <label for="id_government">Identification ID/Government ID</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="school_admission" name="national_id_purpose[]" value="School Admission/Requirement">
                        <label for="school_admission">School Admission/Requirement</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="local_employment" name="national_id_purpose[]" value="Requirement for Local Employment">
                        <label for="local_employment">Requirement for Local Employment</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="barangay_clearance" name="national_id_purpose[]" value="Barangay Clearance/Proof of Residency">
                        <label for="barangay_clearance">Barangay Clearance/Proof of Residency</label>
                    </div>

                    <!-- Others Option -->
                    <div class="checkbox-item">
                        <input type="checkbox" id="national_id_others" name="national_id_purpose[]" value="Others">
                        <label for="national_id_others">Others:</label>
                        <input type="text" id="other_purpose" name="other_purpose" placeholder="Please specify..." style="display: none;">
                    </div>
                </div>
            </div>

            <!-- Email Address -->
            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="ex.juandelacruz@gmail.com"
                required />

            <!--Requested Date -->
            <label for="date">Requested Date:</label>
            <input
                type="date"
                id="requested_date"
                name="requested_date"
                required />
            <!-- Select ID Type -->
            <label for="id-type">Select ID Type</label>
            <select id="id-type" name="id-type" required>
                <option value="" disabled selected>Select Valid ID</option>
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


            <!-- Attach ID -->
            <div class="input-item">
                <label for="id-photo">Attach Valid Photo:</label>
                <input
                    type="file"
                    id="id-photo"
                    name="id_photo"
                    accept="image/*"
                    class="input-field" />
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submitbut">Submit Request</button>
        </form>
    </section>

    <div class="back-button">
        <button onclick="window.location.href='request_general.php'">← Back to Request Page</button>
    </div>

    <script>
        // JavaScript to toggle subject fields based on checkbox
        const subjectCheckbox = document.getElementById('is_subject_same');
        const subjectInfo = document.getElementById('subject-info');
        const subjectAge = document.getElementById('subject_age');
        const subjectFullname = document.getElementById('subject_fullname');
        const subjectDOB = document.getElementById('date_of_birth');

        subjectCheckbox.addEventListener('change', function() {
            if (this.checked) {
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
        });



        // Show/hide the 'Others' input field for Barangay Certificate
        document.getElementById('national_id_others').addEventListener('change', function() {
            const othersInput = document.getElementById('other_purpose');
            othersInput.style.display = this.checked ? 'block' : 'none';
        });

        // Select the burger menu and dropdown
        const burgerMenu = document.getElementById('burger-menu');
        const burgerDropdown = document.getElementById('burger-dropdown');

        // Toggle menu visibility on burger click
        burgerMenu.addEventListener('click', () => {
            const isExpanded = burgerMenu.getAttribute('aria-expanded') === 'true';
            burgerMenu.setAttribute('aria-expanded', !isExpanded);
            burgerDropdown.setAttribute('aria-hidden', isExpanded);
            burgerDropdown.style.display = isExpanded ? 'none' : 'flex';
        });
    </script>

</body>

</html>