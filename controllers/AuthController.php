<?php
require_once 'config/db.php';
require_once 'models/UserModel.php';

function handleLogin() {
    $error = "";

    if ($_SERVER["REQUEST_METHOD"] !== "POST") { return $error; }

    $username = trim($_POST["username"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($username) || empty($email) || empty($password)) {
        return "All fields are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format.";
    }
    if (!preg_match("/^[a-zA-Z0-9_]{3,30}$/", $username)) {
        return "Invalid username.";
    }
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters.";
    }

    $conn = getDB();
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ? AND username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"]  = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"]     = $user["role"];

            if ($user["role"] === "admin")  { header("Location: admindashboard.php"); exit(); }
            if ($user["role"] === "worker") { header("Location: workerdashboard.php"); exit(); }
            header("Location: userdashboard.php");
            exit();
        }
    }

    return "Invalid credentials.";
}

function handleRegister() {
    $error = "";

    if ($_SERVER["REQUEST_METHOD"] !== "POST") { return $error; }

    $username = trim($_POST["username"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($username) || empty($email) || empty($password)) {
        return "All fields are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format.";
    }
    if (!preg_match("/^[a-zA-Z0-9_]{3,30}$/", $username)) {
        return "Invalid username.";
    }
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters.";
    }
    if (isEmailTaken($email)) {
        return "Email already registered.";
    }
    if (isUsernameTaken($username)) {
        return "Username already taken.";
    }

    if (createUser($username, $email, $password)) {
        header("Location: login.php");
        exit();
    }

    return "Something went wrong. Please try again.";
}
?>