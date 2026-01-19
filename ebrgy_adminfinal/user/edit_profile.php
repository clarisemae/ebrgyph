<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ebrgyph";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM barangay_registration WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if (!$user_data) {
    die("No user data found for user ID: " . htmlspecialchars($user_id));
}

// Sanitize and format data
function sanitize_data($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $civil_status = $_POST['civil_status'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $street = $_POST['street'];
    $barangay = $_POST['barangay'];
    $municipality = $_POST['municipality'];
    $city = $_POST['city'];
    $region = $_POST['region'];
    $emergency_name = $_POST['emergency_name'];
    $emergency_address = $_POST['emergency_address'];
    $emergency_relationship = $_POST['emergency_relationship'];
    $emergency_phone = $_POST['emergency_phone'];

    // Handle profile picture upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate file type
        $valid_extensions = ["jpg", "jpeg", "png", "gif"];
        if (in_array($imageFileType, $valid_extensions)) {
            // Move uploaded file
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = basename($_FILES["profile_picture"]["name"]);
            } else {
                echo "Error uploading profile picture.";
                $profile_picture = $user_data['profile_picture']; // Retain existing picture if upload fails
            }
        } else {
            echo "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
            $profile_picture = $user_data['profile_picture'];
        }
    } else {
        $profile_picture = $user_data['profile_picture']; // Retain existing picture if no new file is uploaded
    }

    // Update query
    $update_sql = "UPDATE barangay_registration 
                   SET full_name = ?, birthdate = ?, gender = ?, civil_status = ?, email = ?, phone = ?, street = ?, barangay = ?, municipality = ?, city = ?, region = ?, emergency_name = ?, emergency_address = ?, emergency_relationship = ?, emergency_phone = ?, profile_picture = ? 
                   WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    if (!$update_stmt) {
        die("Update query preparation failed: " . $conn->error);
    }
    $update_stmt->bind_param(
        "ssssssssssssssssi",
        $full_name,
        $birthdate,
        $gender,
        $civil_status,
        $email,
        $phone,
        $street,
        $barangay,
        $municipality,
        $city,
        $region,
        $emergency_name,
        $emergency_address,
        $emergency_relationship,
        $emergency_phone,
        $profile_picture,
        $user_id
    );

    if ($update_stmt->execute()) {
        $_SESSION['message'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } else {
        echo "Error updating record: " . $update_stmt->error;
    }

    $update_stmt->close();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="edit_profile.css">
</head>

<body>
    <div class="profile-container">
        <h2>Edit Profile</h2>
        <!-- Add enctype for file uploads -->
        <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
            <!-- Profile Picture Upload -->
            <div class="profile-picture">
                <img src="uploads/<?php echo sanitize_data($user_data['profile_picture'] ?? 'default.jpg'); ?>" alt="Profile Picture" width="150" height="150" style="border-radius: 50%;">
            </div>
            <label for="profile_picture">Upload New Profile Picture:</label>
            <input type="file" id="profile_picture" name="profile_picture">

            <!-- Rest of the form fields -->
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo sanitize_data($user_data['full_name']); ?>" required>

            <label for="birthdate">Birthdate:</label>
            <input type="date" id="birthdate" name="birthdate" value="<?php echo sanitize_data($user_data['birthdate']); ?>" required>

            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="Male" <?php echo $user_data['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo $user_data['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
            </select>

            <label for="civil_status">Civil Status:</label>
            <select id="civil_status" name="civil_status" required>
                <option value="Single" <?php echo $user_data['civil_status'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                <option value="Married" <?php echo $user_data['civil_status'] === 'Married' ? 'selected' : ''; ?>>Married</option>
                <option value="Widowed" <?php echo $user_data['civil_status'] === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                <option value="Separated" <?php echo $user_data['civil_status'] === 'Separated' ? 'selected' : ''; ?>>Separated</option>
            </select>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo sanitize_data($user_data['email']); ?>" required>

            <label for="phone">Phone:</label>
            <input type="tel" id="phone" name="phone" value="<?php echo sanitize_data($user_data['phone']); ?>" required>

            <label for="street">Street:</label>
            <input type="text" id="street" name="street" value="<?php echo sanitize_data($user_data['street']); ?>" required>

            <label for="barangay">Barangay:</label>
            <input type="text" id="barangay" name="barangay" value="<?php echo sanitize_data($user_data['barangay']); ?>" required>

            <label for="municipality">Municipality:</label>
            <input type="text" id="municipality" name="municipality" value="<?php echo sanitize_data($user_data['municipality']); ?>" required>

            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?php echo sanitize_data($user_data['city']); ?>" required>

            <label for="region">Region:</label>
            <input type="text" id="region" name="region" value="<?php echo sanitize_data($user_data['region']); ?>" required>

            <h3>Emergency Contact Information</h3>

            <label for="emergency_name">Contact Person:</label>
            <input type="text" id="emergency_name" name="emergency_name" value="<?php echo sanitize_data($user_data['emergency_name']); ?>" required>

            <label for="emergency_address">Address:</label>
            <input type="text" id="emergency_address" name="emergency_address" value="<?php echo sanitize_data($user_data['emergency_address']); ?>" required>

            <label for="emergency_relationship">Relationship:</label>
            <input type="text" id="emergency_relationship" name="emergency_relationship" value="<?php echo sanitize_data($user_data['emergency_relationship']); ?>" required>

            <label for="emergency_phone">Phone:</label>
            <input type="tel" id="emergency_phone" name="emergency_phone" value="<?php echo sanitize_data($user_data['emergency_phone']); ?>" required>

            <button type="submit" class="btn">Update Profile</button>
        </form>
    </div>
    <div class="back-button">
        <button onclick="window.location.href='profile.php'">← Back to Profile Page</button>
    </div>
</body>

</html>