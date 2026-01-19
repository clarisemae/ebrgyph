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

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the user's profile picture dynamically
$username = $_SESSION['username'];
$sql_users = "SELECT id FROM users WHERE username = ?";
$stmt_users = $conn->prepare($sql_users);
if (!$stmt_users) {
    die("Query preparation failed: " . $conn->error);
}
$stmt_users->bind_param("s", $username);
$stmt_users->execute();
$result_users = $stmt_users->get_result();
$user_data = $result_users->fetch_assoc();
if (!$user_data) {
    die("No user data found for username: " . htmlspecialchars($username));
}
$user_id = $user_data['id']; // Get user ID
$stmt_users->close();

// Fetch profile picture from barangay_registration table
$sql_registration = "SELECT profile_picture FROM barangay_registration WHERE id = ?";
$stmt_registration = $conn->prepare($sql_registration);
if (!$stmt_registration) {
    die("Query preparation failed: " . $conn->error);
}
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

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form inputs
    $document_type = $_POST['document_type'];
    $fullname = $_POST['fullname'];
    $age = $_POST['age'];
    $status = $_POST['status'];
    $citizen = $_POST['citizen'];
    $address = $_POST['address'];
    $requested_date = $_POST['requested_date'];
    $email = $_POST['email'];

    // Process checkbox inputs (implode multiple checkbox values)
    $barangay_certificate_purpose = isset($_POST['barangay_certificate_purpose'])
        ? implode(', ', $_POST['barangay_certificate_purpose'])
        : null;

    // Retrieve the 'Others' input
    $barangay_other_details = !empty($_POST['other_purpose']) ? $_POST['other_purpose'] : null;

    // Handle the ID type and photo
    $id_type = $_POST['id-type'];
    $id_photo_url = null;

    // Handle file upload if provided
    if (isset($_FILES['id_photo']) && $_FILES['id_photo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "crudBrgyCertificate/uploads/";
        $id_photo_url = $target_dir . basename($_FILES['id_photo']['name']);
        move_uploaded_file($_FILES['id_photo']['tmp_name'], $id_photo_url);
    }

    // SQL query to insert data
    $sql = "INSERT INTO barangay_certificate (
                document_type, 
                fullname, 
                age, 
                status, 
                citizen, 
                address, 
                requested_date, 
                email, 
                barangay_certificate_purpose, 
                barangay_other_details, 
                id_type, 
                id_photo_url,
                barangay_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = mysqli_prepare($conn, $sql);

    // Bind parameters to the statement
    mysqli_stmt_bind_param(
        $stmt,
        "ssisssssssssi",
        $document_type,
        $fullname,
        $age,
        $status,
        $citizen,
        $address,
        $requested_date,
        $email,
        $barangay_certificate_purpose,
        $barangay_other_details,
        $id_type,
        $id_photo_url,
        $barangay_id
    );

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        header("Location: requestsubmission.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    // Close the statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document Request</title>
    <link rel="stylesheet" href="request.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@800&display=swap"
        rel="stylesheet" />
</head>

<body>
    <div class="navbar">
        <!-- Left Section -->
        <div class="navbar-left">
            <a href="#ebrgy" id="ebrgy-link">e-brgyPH</a>
        </div>
        <!-- Center Navbar Links -->
        <div class="navbar-center">
            <a href="home.php" class="nav-link">Home</a>
            <a href="request_general.php" class="nav-link active">Document Request</a>
            <a href="report.php" class="nav-link">Incident Report</a>
            <a href="officials.php" class="nav-link">Barangay Officials</a>
            <a href="insights.php" class="nav-link">Comments/Insights</a>
        </div>

        <!-- Burger Menu -->
        <div class="burger-menu" id="burger-menu" aria-label="Toggle menu" aria-expanded="false">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>

        <!-- Large Screen Logout Button -->
        <div class="navbar-right">
            <!-- Profile Circle -->
            <a href="profile.php" class="profile-circle">
                <img src="<?php echo $profile_picture; ?>" alt="User Profile" style="width:40px; height:40px; border-radius:50%;" />
            </a>
            <!-- Large Screen Logout Button -->
            <button id="logout-btn-large" class="logout-large">Logout</button>
        </div>
    </div>

    <section id="document-request-form">
        <h2>Document Request</h2>
        <form action="barangay_certificate.php" method="POST" enctype="multipart/form-data">
            <form action="barangay_certificate.php" method="POST" enctype="multipart/form-data">
                <label for="remarks">Document Type:</label>
                <input type="text" id="document_type" name="document_type" value="BARANGAY CERTIFICATE" readonly />

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

                <!-- Address -->
                <label for="address">Address</label>
                <input
                    type="text"
                    id="address"
                    name="address"
                    placeholder="House/Unit Number, Street Name, Barangay, City"
                    title="Enter in the format: House/Unit Number, Street Name, Barangay, City"
                    required />

                <!-- Date -->
                <label for="date">Requested Date:</label>
                <input
                    type="date"
                    id="requested_date"
                    name="requested_date"
                    required />

                <!-- Email Address -->
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="ex.juandelacruz@gmail.com"
                    required />

                <div class="input-item">
                    <label>Purpose of Barangay Certificate:</label>
                    <div class="checkbox-container">
                        <div class="checkbox-item">
                            <input type="checkbox" id="id_government" name="barangay_certificate_purpose[]" value="Identification ID/Government ID">
                            <label for="id_government">Identification ID/Government ID</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="school_admission" name="barangay_certificate_purpose[]" value="School Admission/Requirement">
                            <label for="school_admission">School Admission/Requirement</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="local_employment" name="barangay_certificate_purpose[]" value="Requirement for Local Employment">
                            <label for="local_employment">Requirement for Local Employment</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="barangay_clearance" name="barangay_certificate_purpose[]" value="Barangay Clearance/Proof of Residency">
                            <label for="barangay_clearance">Barangay Clearance/Proof of Residency</label>
                        </div>

                        <!-- Others Option -->
                        <div class="checkbox-item">
                            <input type="checkbox" id="barangay_others" name="barangay_certificate_purpose[]" value="Others">
                            <label for="barangay_others">Others:</label>
                            <input type="text" id="other_purpose" name="other_purpose" placeholder="Please specify..." style="display: none;">
                        </div>
                    </div>
                </div>



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
                    <label for="id-photo">Attach Valid Photo (if any):</label>
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
        // Show/hide the 'Others' input field for Barangay Certificate
        document.getElementById('barangay_others').addEventListener('change', function() {
            const othersInput = document.getElementById('other_purpose');
            othersInput.style.display = this.checked ? 'inline-block' : 'none';
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