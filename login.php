<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($username) || empty($email) || empty($password)) {  $error = "All fields are required."; } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = "Invalid email format."; } 
    elseif (!preg_match("/^[a-zA-Z0-9_]{3,30}$/", $username)) { $error = "Invalid username."; } 
    elseif (strlen($password) < 8) { $error = "Password must be at least 8 characters."; } 
    else {
        $conn = new mysqli("localhost", "root", "", "citywatch");
        if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ? AND username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                if ($user["role"] == "admin") { header("Location: admindashboard.php"); } 
                elseif ($user["role"] == "worker") { header("Location: workerdashboard.php"); } 
                else { header("Location: userdashboard.php"); }
                exit();
            } 
            else { $error = "Invalid credentials.";  }
        } 
        else { $error = "Invalid credentials."; }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityWatch Login</title>
    <link rel="stylesheet" href="public/main.css">
    <link rel="icon" type="image/x-icon" href="public/assets/logosmall.png">
</head>

<body class="login-page">
    <a href="index.php" class="back-home">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        <span>Home</span>
    </a>
    <div class="login-left">
        <img src="public/assets/logobig.png" class="login-logo" alt="logo">
        <h1>Help us make<br>the city clean.</h1>
    </div>
    <div class="login-right">
        <h2>Welcome back!</h2>
        <p>Login to access your account and start reporting waste-issues.</p>
        
        <?php if (!empty($error)): ?> <div class="error-box"> <?= $error ?>  </div> <?php endif; ?>
        
        <form class="login-form" method="POST" action="login.php">
            <label>Username</label>
            <div class="input-group">
                <img src="public/assets/iconuser.png" alt="user icon">
                <input type="text" name="username" required>
            </div>
            <label>Email</label>
            <div class="input-group">
                <img src="public/assets/iconemail.png" alt="email icon">
                <input type="email" name="email" required>
            </div>
            <label>Password</label>
            <div class="input-group">
                <img src="public/assets/iconpassword.png" alt="password icon">
                <input type="password" name="password" id="passwordInput" required>
                <img src="public/assets/iconshowpass.png" alt="toggle password" id="togglePassword" style="cursor: pointer;">
            </div>
            <button type="submit" class="login-button-submit">Login</button>
            <p class="link-to-signup">Don't have an account?<a href="register.php">Sign Up</a></p>
        </form>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var passwordInput = document.getElementById("passwordInput");
            var icon = document.getElementById("togglePassword");
            icon.addEventListener("click", function() {
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    icon.src = "public/assets/iconhidepass.png";
                } else {
                    passwordInput.type = "password";
                    icon.src = "public/assets/iconshowpass.png";
                }
            });
        });
    </script>
</body>
</html>