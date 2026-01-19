<?php
session_start(); // Start session management

// Database connection
$host = 'localhost';
$dbname = 'ebrgyph';
$dbusername = 'root';
$dbpassword = '';
$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$username_error = $password_error = "";
$login_error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($email)) {
        $username_error = "Username is required!";
    }
    if (empty($password)) {
        $password_error = "Password is required!";
    }

    if (empty($username_error) && empty($password_error)) {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Use separate session variables for user
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                header("Location: home.php");
                exit();
            } else {
                $password_error = "Incorrect password.";
            }
        } else {
            $username_error = "No account found with that username.";
        }
    }
}


// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="page1_general_style.css">
    <style>
        .btn-container a {
            color: #8099E2;
            font-size: 1rem;
        }

        .register-link {
            text-align: center;
            margin-top: 10px;
            color: #1F3B8B;
            font-size: 1rem;
        }

        .register-link a {
            color: #8099E2;
            text-decoration: underline;
            font-size: 1.2rem;
        }

        img {
            height: 150px;
            width: 150px;
            margin-top: 0.5rem;
            margin-bottom: 2rem;
        }

        h2 {
            font-family: "Poppins", sans-serif;
            font-weight: 400;
            text-align: center;
            color: #1F3B8B;
        }

        .error-message {
            color: red;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <section class="form-container">
        <div>
            <h1>Welcome to <i>E-BRGY</i></h1>
            <p class="subtitle">Barangay 834 Zone 91</p>
            <img src="logo.png" alt="barangay_logo">
        </div>
        <div>
            <h2>Login</h2>

            <form action="login.php" method="POST">
                <!-- Username -->
                <div class="input-item">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="input-field"
                        placeholder="Enter your username"
                        required />
                </div>
                <?php if (!empty($username_error)) : ?>
                    <div class="error-message"><?php echo $username_error; ?></div>
                <?php endif; ?>

                <!-- Password -->
                <div class="input-item">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="input-field"
                        placeholder="Enter your password"
                        required />
                </div>
                <?php if (!empty($password_error)) : ?>
                    <div class="error-message"><?php echo $password_error; ?></div>
                <?php endif; ?>

                <!-- Login Error -->
                <?php if (!empty($login_error)) : ?>
                    <div class="error-message"><?php echo $login_error; ?></div>
                <?php endif; ?>

                <div class="btn-container">
                    <button type="submit" class="btn">Login</button>
                    <a href="reset_password_email.html">Forgot password?</a>
                </div>
            </form>
            <div class="register-link">
                <p>Don't have an account?</p>
                <a href="register.php">Register</a>
            </div>
        </div>
    </section>
</body>

</html>