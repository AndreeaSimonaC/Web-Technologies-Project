<?php
require_once 'config/db.php';
$conn = getDB();

for ($i = 14; $i <= 20; $i++) {
    $username = "user$i";
    $email    = "user$i@citywatch.com";
    $password = password_hash("user{$i}000", PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT IGNORE INTO users (username, email, password, role, status, created_at) VALUES (?, ?, ?, 'user', 'active', NOW())");
    $stmt->bind_param("sss", $username, $email, $password);
    $stmt->execute();
}

for ($i = 16; $i <= 20; $i++) {
    $username = "worker$i";
    $email    = "worker$i@citywatch.com";
    $password = password_hash("worker{$i}000", PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT IGNORE INTO users (username, email, password, role, status, created_at) VALUES (?, ?, ?, 'worker', 'active', NOW())");
    $stmt->bind_param("sss", $username, $email, $password);
    $stmt->execute();
}

echo "Users seeded successfully!";
?>