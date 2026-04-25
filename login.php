<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityWatch Login</title>
    <link rel="stylesheet" href="login.css">
    <!--    
    <link rel="stylesheet" href="main.css">
    -->
    <link rel="icon" type="image/x-icon" href="assets/logosmall.png">
</head>

<body class="login-page">
    <div class="login-left">
        <img src="assets/logobig.png" class="login-logo" alt="logo">
        <h1>Help us make<br>the city clean.</h1>
    </div>
    <div class="login-right">
        <h2>Welcome back!</h2>
        <p>Login to access your account and start reporting waste-issues.</p>
        <form class="login-form" method="POST" action="login.php">
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