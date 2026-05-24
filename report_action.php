<?php
require_once 'config/db.php';
$conn = getDB();
$id = $_POST['id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$id || !$action) {
    echo "Invalid request";
    exit;
}

if ($action === "delete") {

    $stmt = $conn->prepare("DELETE FROM reports WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "deleted";
} elseif ($action === "resolve") {

    $stmt = $conn->prepare("UPDATE reports SET status = 'resolved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "resolved";
} elseif ($action === "assign") {

    $worker_id = 1;

    $stmt = $conn->prepare("UPDATE reports SET worker_id = ?, status='progress' WHERE id = ?");
    $stmt->bind_param("ii", $worker_id, $id);
    $stmt->execute();
    echo "assigned";
} elseif ($action === "archive") {
    $stmt = $conn->prepare("UPDATE reports SET status = 'archived' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
} elseif ($action === "edit") {

    $category = $_POST['category'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("
        UPDATE reports 
        SET category_id = ?, location = ?, description = ?, status = ?
        WHERE id = ?
    ");
    $stmt->bind_param("isssi", $category, $location, $description, $status, $id);
    $stmt->execute();

    echo "updated";
} else {
    echo "unknown action";
}
?>