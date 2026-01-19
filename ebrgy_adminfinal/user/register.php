<?php
// Connect to the database
$servername = "localhost";  // Change to your database server
$username = "root";         // Change to your database username
$password = "";             // Change to your database password
$dbname = "ebrgyph";          // Change to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch barangays for dropdown
$barangays = [];
$result = $conn->query("SELECT id, name FROM barangays");
while ($row = $result->fetch_assoc()) {
    $barangays[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $fullName = $_POST['full_name'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['sex'];
    $civilStatus = $_POST['civil_status'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $street = $_POST['street'];
    $barangay_id = $_POST['barangay_id'];
    $barangay = $_POST['barangay'];
    $municipality = $_POST['municipality'];
    $city = $_POST['city'];
    $region = $_POST['region'];
    $emergencyName = $_POST['emergency_name'];
    $emergencyAddress = $_POST['emergency_address'];
    $emergencyRelationship = $_POST['emergency_relationship'];
    $emergencyPhone = $_POST['emergency_phone'];

    // Prepare SQL statement to insert data
    $stmt = $conn->prepare("INSERT INTO barangay_registration (full_name, birthdate, gender, civil_status, email, phone, street, barangay, barangay_id, municipality, city, region, emergency_name, emergency_address, emergency_relationship, emergency_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssisssssss", $fullName, $birthdate, $gender, $civilStatus, $email, $phone, $street, $barangay, $barangay_id, $municipality, $city, $region, $emergencyName, $emergencyAddress, $emergencyRelationship, $emergencyPhone);

    // Execute the statement
    if ($stmt->execute()) {
        // Start session and store barangay_id and barangay name
        session_start();
        $_SESSION['barangay_id'] = $barangay_id;
        $_SESSION['barangay'] = $barangay;
        // Redirect to the next page (e.g., face verification)
        header("Location: validID.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Barangay Registration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="register.css" />
</head>

<body>
    <div class="form-container">
        <h2>Barangay Registration</h2>
        <p>Step 1 of 2: Please fill out your Personal Information.</p>

        <form action="register.php" method="POST">
            <!-- Full Name -->
            <input type="text" id="full_name" name="full_name" class="input-field" placeholder="Full Name (Last Name, First Name, Middle Name )" required />

            <!-- Date of Birth -->
            <input type="date" id="birthdate" name="birthdate" class="input-field bdate" required />

            <!-- Gender -->
            <select id="sex" name="sex" class="input-field" required>
                <option value="">Select Sex</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <!-- Civil Status -->
            <select id="civil_status" name="civil_status" class="input-field" required>
                <option value="">Select Civil Status</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Widowed">Widowed</option>
                <option value="Separated">Separated</option>
            </select>

            <!-- Email Address -->
            <input type="email" id="email" name="email" class="input-field" placeholder="Email Address" required />

            <!-- Phone Number -->
            <input type="tel" id="phone" name="phone" class="input-field" placeholder="Phone Number (+63)" required oninput="sanitizePhoneInput(this)" maxlength="10" />

            <!-- Home Address (Barangay selection) -->
            <input type="text" id="street" name="street" class="input-field" placeholder="Street" required />
            <select id="barangay_id" name="barangay_id" class="input-field" required onchange="updateBarangayName()">
                <option value="">Select Barangay</option>
                <?php foreach (
                    $barangays as $b): ?>
                    <option value="<?= $b['id'] ?>" data-name="<?= htmlspecialchars($b['name']) ?>"><?= htmlspecialchars($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" id="barangay" name="barangay" value="" />
            <input type="text" id="municipality" name="municipality" class="input-field" value="Pandacan" readonly required />
            <input type="text" id="city" name="city" class="input-field" value="Manila" readonly required />
            <input type="text" id="region" name="region" class="input-field" value="NCR" readonly required />

            <!-- Emergency Contact Information -->
            <h3>Emergency Contact Information</h3>

            <!-- Contact Person Name -->
            <input type="text" id="emergency_name" name="emergency_name" class="input-field" placeholder="Full Name (First, M.I, Last)" required />

            <!-- Address -->
            <input type="text" id="emergency_address" name="emergency_address" class="input-field" placeholder="Address" required />

            <!-- Relationship -->
            <input type="text" id="emergency_relationship" name="emergency_relationship" class="input-field" placeholder="Relationship (eg. Mother)" required />

            <!-- Emergency Contact Number -->
            <input type="tel" id="emergency_phone" name="emergency_phone" class="input-field" placeholder="Phone Number (+63)" required oninput="sanitizePhoneInput(this)" maxlength="10" pattern="\d{10}" title="Enter a valid 10-digit phone number" />

            <!-- Submit Button -->
            <button type="submit" class="btn">Continue</button>
        </form>

        <div class="register-link">
            <p>Already registered? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script>
        function sanitizePhoneInput(inputField) {
            // Remove all non-numeric characters
            inputField.value = inputField.value.replace(/[^0-9]/g, '');

            // Ensure the input length is restricted to 10 digits
            if (inputField.value.length > 10) {
                inputField.value = inputField.value.slice(0, 10);
            }
        }
        function updateBarangayName() {
            var select = document.getElementById('barangay_id');
            var selectedOption = select.options[select.selectedIndex];
            var barangayName = selectedOption.getAttribute('data-name');
            document.getElementById('barangay').value = barangayName || '';
        }
    </script>
</body>

</html>