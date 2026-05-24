<?php
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($username) || empty($email) || empty($password)) { $error = "All fields are required."; } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = "Invalid email format."; } 
    elseif (!preg_match("/^[a-zA-Z0-9_]{3,30}$/", $username)) { $error = "Invalid username.";} 
    elseif (strlen($password) < 8) { $error = "Password must be at least 8 characters."; } 
    else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $conn = new mysqli("localhost", "root", "", "citywatch");
        if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) { $error = "Email already registered.";} 
        else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($stmt->execute()) { header("Location: login.php"); exit();} 
            else { $error = "Something went wrong!"; }

            $stmt->close();
        }
        $check->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityWatch Register</title>
    <link rel="stylesheet" href="public/main.css">
    <link rel="icon" type="image/x-icon" href="assets/logosmall.png">
</head>

<body class="register-page">
    <a href="index.php" class="back-home">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        <span>Home</span>
    </a>
    <div class="register-left">
        <img src="assets/logobig.png" class="register-logo" alt="logo">
        <h1>Help us make<br>the city clean.</h1>
    </div>
    <div class="register-right">
        <h2>Welcome!</h2>
        <p>Sign up to access your account and start reporting waste-issues.</p>
        
        <?php if (!empty($error)): ?> <div class="error-box"> <?= $error ?> </div> <?php endif; ?>
        
        <form class="register-form" method="POST" action="register.php">
            <label>Username</label>
            <div class="input-group">
                <img src="assets/iconuser.png" alt="user icon">
                <input type="text" name="username" required>
            </div>
            <label>Email</label>
            <div class="input-group">
                <img src="assets/iconemail.png" alt="email icon">
                <input type="email" name="email" required>
            </div>
            <label>Password</label>
            <div class="input-group">
                <img src="assets/iconpassword.png" alt="password icon">
                <input type="password" name="password" id="passwordInput" required>
                <img src="assets/iconshowpass.png" alt="toggle password" id="togglePassword" style="cursor: pointer;">
            </div>
            <button type="submit" class="register-button">Sign Up</button>
            <p class="link-to-login">Already have an account?<a href="login.php">Login</a></p>
        </form>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var passwordInput = document.getElementById("passwordInput");
            var icon = document.getElementById("togglePassword");

            icon.addEventListener("click", function() {
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    icon.src = "assets/iconhidepass.png";
                } else {
                    passwordInput.type = "password";
                    icon.src = "assets/iconshowpass.png";
                }
            });
        });
    </script>
</body>
</html>