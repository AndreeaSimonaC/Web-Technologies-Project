<?php
session_start();
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {
    $conn = getDB();
    $stmt = $conn->prepare(" SELECT id, username, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_COOKIE['remember_user']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        $_SESSION['role'] = $user['role'];
    }
}
?>