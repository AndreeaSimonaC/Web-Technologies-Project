<?php
$conn = new mysqli("localhost", "root", "", "citywatch");
$result = $conn->query("SELECT r.*, c.name AS category_name, u.username FROM reports r JOIN categories c ON r.category_id = c.id JOIN users u ON r.user_id = u.id");
$reports = [];
while ($row = $result->fetch_assoc()) { $reports[] = $row;}
echo json_encode($reports);
?>