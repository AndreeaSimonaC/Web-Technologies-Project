<?php
require_once __DIR__ . '/../config/db.php';

function writeLog($action, $user_id = null) {
    $conn = getDB();
    $stmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
}

function getAllLogs() {
    $conn = getDB();
    $result = $conn->query("
        SELECT l.id, l.action, l.created_at, u.username
        FROM logs l
        LEFT JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC
    ");
    $logs = [];
    while ($row = $result->fetch_assoc()) { $logs[] = $row; }
    return $logs;
}
?>