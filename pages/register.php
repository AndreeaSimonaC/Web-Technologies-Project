<?php
require_once '../controllers/AuthController.php';
$error = handleRegister();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityWatch Register</title>
    <link rel="stylesheet" href="../public/main.css">
    <link rel="icon" type="image/x-icon" href="../public/assets/logosmall.png">
</head>

<body class="register-page">
    <a href="../pages/index.php" class="back-home">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        <span>Home</span>
    </a>
    <div class="register-left">
        <img src="../public/assets/logobig.png" class="register-logo" alt="logo">
        <h1>Help us make<br>the city clean.</h1>
    </div>
    <div class="register-right">
        <h2>Welcome!</h2>
        <p>Sign up to access your account and start reporting waste-issues.</p>
        
        <?php if (!empty($error)): ?> <div class="error-box"> <?= $error ?> </div> <?php endif; ?>
        
        <form class="register-form" method="POST" action="../pages/register.php">
            <label>Username</label>
            <div class="input-group">
                <img src="../public/assets/iconuser.png" alt="user icon">
                <input type="text" name="username" required>
            </div>
            <label>Email</label>
            <div class="input-group">
                <img src="../public/assets/iconemail.png" alt="email icon">
                <input type="email" name="email" required>
            </div>
            <label>Password</label>
            <div class="input-group">
                <img src="../public/assets/iconpassword.png" alt="password icon">
                <input type="password" name="password" id="passwordInput" required>
                <img src="../public/assets/iconshowpass.png" alt="toggle password" id="togglePassword" style="cursor: pointer;">
            </div>
            <button type="submit" class="register-button">Sign Up</button>
            <p class="link-to-login">Already have an account?<a href="../pages/login.php">Login</a></p>
        </form>
    </div>
    <script>
    const usernameInput = document.querySelector('input[name="username"]');
    const usernameMsg   = document.createElement('small');
    usernameMsg.style.marginTop = "4px";
    usernameMsg.style.display   = "block";
    usernameMsg.style.fontWeight = "500";
    usernameInput.parentElement.appendChild(usernameMsg);

    let debounceTimer;
    usernameInput.addEventListener("input", function () {
        const val = this.value.trim();
        usernameMsg.textContent = "";

        if (val.length < 3) return;

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetch("api/check_username.php?username=" + encodeURIComponent(val))
                .then(res => res.json())
                .then(data => {
                    if (data.status === "available") {
                        usernameMsg.style.color = "#2e7d32";
                    } else if (data.status === "taken" || data.status === "invalid") {
                        usernameMsg.style.color = "#c0392b";
                    }
                    usernameMsg.textContent = data.message ?? "";
                })
                .catch(() => { usernameMsg.textContent = ""; });
        }, 400);
    });
</script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var passwordInput = document.getElementById("passwordInput");
            var icon = document.getElementById("togglePassword");

            icon.addEventListener("click", function() {
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    icon.src = "../public/assets/iconhidepass.png";
                } else {
                    passwordInput.type = "password";
                    icon.src = "../public/assets/iconshowpass.png";
                }
            });
        });
    </script>
</body>
</html>