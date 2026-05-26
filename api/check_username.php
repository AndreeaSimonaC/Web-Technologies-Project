<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
$username = trim($_GET['username'] ?? '');

if (empty($username)) {
    echo json_encode(['status' => 'empty']);
    exit;
}

if (!preg_match("/^[a-zA-Z0-9_]{3,30}$/", $username)) {
    echo json_encode(['status' => 'invalid', 'message' => 'Username must be 3-30 characters, letters/numbers/underscore only.']);
    exit;
}

$conn = getDB();
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'taken', 'message' => '✗ Username already taken.']);
} else {
    echo json_encode(['status' => 'available', 'message' => '✓ Username available.']);
}
?>