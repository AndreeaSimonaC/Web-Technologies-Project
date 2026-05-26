<?php
require_once __DIR__ . '/../config/db.php';

function getAllUsers() {
    $conn = getDB();
    $result = $conn->query("
        SELECT id, username, email, role, status, created_at 
        FROM users 
        ORDER BY created_at DESC
    ");
    return $result;
}

function getUserById($user_id) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUserCounts() {
    $conn = getDB();
    $counts = [];
    $counts['total']   = $conn->query("SELECT COUNT(*) as t FROM users")->fetch_assoc()['t'];
    $counts['users']   = $conn->query("SELECT COUNT(*) as t FROM users WHERE role='user'")->fetch_assoc()['t'];
    $counts['workers'] = $conn->query("SELECT COUNT(*) as t FROM users WHERE role='worker'")->fetch_assoc()['t'];
    $counts['admins']  = $conn->query("SELECT COUNT(*) as t FROM users WHERE role='admin'")->fetch_assoc()['t'];
    return $counts;
}

function isUsernameTaken($username) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

function isEmailTaken($email) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

function createUser($username, $email, $password) {
    $conn = getDB();
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("
        INSERT INTO users (username, email, password, role, status, created_at) 
        VALUES (?, ?, ?, 'user', 'active', NOW())
    ");
    $stmt->bind_param("sss", $username, $email, $hashed);
    return $stmt->execute();
}

function deleteUser($user_id) {
    $conn = getDB();
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

function updateUser($user_id, $role, $email) {
    $conn = getDB();
    $stmt = $conn->prepare("UPDATE users SET role = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $role, $email, $user_id);
    return $stmt->execute();
}

function deactivateUser($user_id) {
    $conn = getDB();
    $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

function getMemberSince($user_id) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $data      = $stmt->get_result()->fetch_assoc();
    $createdAt = new DateTime($data['created_at']);
    $diff      = $createdAt->diff(new DateTime());
    if ($diff->y > 0)     { return $diff->y . " " . ($diff->y == 1 ? "year" : "years"); }
    elseif ($diff->m > 0) { return $diff->m . " " . ($diff->m == 1 ? "month" : "months"); }
    else                  { return $diff->d . " " . ($diff->d == 1 ? "day" : "days"); }
}
?>