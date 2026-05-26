<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
$conn = getDB();
if (!$conn) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}
$result = $conn->query("
    SELECT r.*, c.name AS category_name, u.username 
    FROM reports r 
    JOIN categories c ON r.category_id = c.id 
    JOIN users u ON r.user_id = u.id
");
if (!$result) {
    echo json_encode(["error" => $conn->error]);
    exit;
}
$reports = [];
while ($row = $result->fetch_assoc()) { $reports[] = $row; }
echo json_encode($reports);
?>