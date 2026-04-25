<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityWatch Register</title>
    <link rel="stylesheet" href="main.css">
    <link rel="icon" type="image/x-icon" href="assets/logosmall.png">
</head>

<body class="register-page">
    <div class="register-left">
        <img src="assets/logobig.png" class="register-logo" alt="logo">
        <h1>Help us make<br>the city clean.</h1>
    </div>
    <div class="register-right">
        <h2>Welcome!</h2>
        <p>Sign up to access your account and start reporting waste-issues.</p>
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
                <img
                    src="assets/iconshowpass.png"
                    alt="toggle password"
                    id="togglePassword"
                    style="cursor: pointer;">
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