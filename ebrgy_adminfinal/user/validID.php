<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Database connection (replace with your credentials)
    $host = 'localhost';
    $db = 'ebrgyph';
    $user = 'root';
    $pass = '';

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data
    $idType = $_POST['idType'];
    $imageData = $_POST['imageData']; // Base64 image data

    // Prepare the SQL query
    $stmt = $conn->prepare("INSERT INTO id_scans (id_type, captured_image) VALUES (?, ?)");
    $stmt->bind_param("ss", $idType, $imageData);

    // Execute the query
    if ($stmt->execute()) {
        echo "Image saved successfully!";
        // Redirect or take any action after saving
        header("Location: create_account.php"); // Adjust the redirect URL
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the database connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Barangay Registration - Step 1</title>
    <link rel="stylesheet" href="register.css" />
</head>

<body>
    <div class="form-container">
        <h2>Barangay Registration</h2>
        <p>Step 2 of 2: Select the type of ID you want to scan, then point your camera at it.</p>

        <form method="POST" id="registrationForm">
            <!-- ID Type Selection -->
            <label for="idType">Choose ID Type:</label>
            <select id="idType" name="idType" class="input-field" required>
                <option value="">Select ID Type</option>
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

            <!-- Scan ID Button -->
            <button id="scanButton" class="btn" style="display: none">Scan ID</button>

            <!-- Video stream to show live camera feed (Hidden initially) -->
            <video id="video" width="100%" height="auto" autoplay style="display: none"></video>

            <!-- Canvas for processing the image -->
            <canvas id="canvas" style="display: none"></canvas>
            <img id="capturedImage" src="" alt="Captured ID" style="display: none; width: 100%; margin-top: 20px" />

            <!-- Capture button to capture the ID when visible -->
            <button id="captureButton" class="btn" style="display: none">Capture</button>

            <div>
                <button id="nextButton" class="btn" style="display: none" type="submit">Next</button>
            </div>

            <!-- Hidden input to store base64 image data -->
            <input type="hidden" id="imageData" name="imageData">

            <div class="register-link">
                <p>Already registered? <a href="login.php">Login here</a></p>
            </div>
        </form>

    </div>

    <script>
        let video = document.getElementById("video");
        let canvas = document.getElementById("canvas");
        let context = canvas.getContext("2d");
        let scanButton = document.getElementById("scanButton");
        let nextButton = document.getElementById("nextButton");
        let idTypeSelect = document.getElementById("idType");
        let capturedImage = document.getElementById("capturedImage");
        let captureButton = document.getElementById("captureButton");
        let registrationForm = document.getElementById("registrationForm");

        // Enable the scan button when an ID type is selected
        idTypeSelect.addEventListener("change", () => {
            if (idTypeSelect.value) {
                scanButton.style.display = "block"; // Show scan button when ID type is selected
            } else {
                scanButton.style.display = "none"; // Hide scan button if no ID type is selected
            }
        });

        // Request camera access when Scan ID button is clicked
        scanButton.addEventListener("click", (event) => {
            event.preventDefault(); // Prevent form submission

            navigator.mediaDevices
                .getUserMedia({
                    video: true
                })
                .then((stream) => {
                    video.srcObject = stream;
                    video.style.display = "block"; // Show the video once the camera is activated
                    scanButton.style.display = "none"; // Hide the scan button after it's clicked
                    captureButton.style.display = "block"; // Show the capture button
                    nextButton.style.display = "none"; // Hide the next button initially

                    // Set canvas size to match video dimensions
                    video.onloadedmetadata = () => {
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                    };
                })
                .catch((err) => {
                    alert("Could not access the camera.");
                    console.error(err);
                });
        });

        // Capture button action: Capture the ID image when clicked
        captureButton.addEventListener("click", () => {
            // Capture the frame from the video
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert the canvas to an image (base64)
            let dataUrl = canvas.toDataURL("image/png");

            // Display the captured image
            capturedImage.src = dataUrl;
            capturedImage.style.display = "block"; // Show the captured image

            // Hide the video and capture button
            video.style.display = "none";
            captureButton.style.display = "none";

            // Show the Next button
            nextButton.style.display = "block";

            // Set the image data in the hidden input field
            document.getElementById("imageData").value = dataUrl; // Store the base64 image in the hidden input
        });
    </script>
</body>

</html>