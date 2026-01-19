<?php
session_start();
$barangay_id = isset($_SESSION['barangay_id']) ? $_SESSION['barangay_id'] : null;
$barangay = isset($_SESSION['barangay']) ? $_SESSION['barangay'] : null;
// Initialize error message variables
$username_error = $password_error = $confirm_password_error = $general_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Connect to the database
  $host = 'localhost';
  $dbname = 'ebrgyph';
  $dbusername = 'root';
  $dbpassword = '';

  $conn = new mysqli($host, $dbusername, $dbpassword, $dbname);

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  // Get form data
  $user = $_POST['username'];
  $pass = $_POST['password'];
  $confirm_pass = $_POST['confirm_password'];

  // Validate username
  if (empty($user)) {
    $username_error = "Username is required!";
  }

  // Validate password
  if (empty($pass)) {
    $password_error = "Password is required!";
  } elseif (strlen($pass) < 8) {
    $password_error = "Password must be at least 8 characters!";
  }

  // Confirm password match
  if (empty($confirm_pass)) {
    $confirm_password_error = "Please confirm your password!";
  } elseif ($pass !== $confirm_pass) {
    $confirm_password_error = "Passwords do not match!";
  }

  // If no errors, proceed to insert data
  if (empty($username_error) && empty($password_error) && empty($confirm_password_error)) {
    // Hash the password
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    // Prepare and bind SQL statement
    $stmt = $conn->prepare("INSERT INTO users (username, password, barangay_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $user, $hashed_password, $barangay_id);

    // Execute and check for success
    if ($stmt->execute()) {
      // // Redirect to home.html after successful account creation
      header("Location: successfulReg.html");
      exit(); // Make sure to call exit() after header() to stop further script execution
    } else {
      $general_error = "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto+Serif:ital,opsz,wght@0,8..144,100..900;1,8..144,100..900&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="page1_general_style.css" />
</head>

<body>
  <div class="form-container">
    <h2>Create Account</h2>

    <?php if ($general_error): ?>
      <div class="error-message"><?php echo $general_error; ?></div>
    <?php endif; ?>

    <form action="create_account.php" method="POST">
      <!-- Username -->
      <div class="input-item">
        <label for="username">Username</label>
        <input
          type="text"
          id="username"
          name="username"
          class="input-field"
          placeholder="Create your username"
          value="<?php echo isset($user) ? $user : ''; ?>"
          required />
        <span class="error"><?php echo $username_error; ?></span>
      </div>

      <!-- Password -->
      <div class="input-item">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          class="input-field"
          placeholder="Create your password"
          required />
        <span class="error"><?php echo $password_error; ?></span>
      </div>

      <!-- Confirm Password -->
      <div class="input-item">
        <label for="confirm_password">Confirm Password</label>
        <input
          type="password"
          id="confirm_password"
          name="confirm_password"
          class="input-field"
          placeholder="Confirm your password"
          required />
        <span class="error"><?php echo $confirm_password_error; ?></span>
      </div>

      <!-- Submit Button -->
      <button type="submit" class="btn">Create Account</button>
    </form>

    <div class="login-link">
      <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
  </div>
</body>

</html>