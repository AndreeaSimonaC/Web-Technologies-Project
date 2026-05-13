<?php
    session_start();

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
        http_response_code(403);
        echo "Unauthorized";
        exit();
    }

    if (!isset($_POST['id'], $_POST['action'])) {
        http_response_code(400);
        echo "Missing data";
        exit();
    }

    $id = (int)$_POST['id'];
    $action = $_POST['action'];
    $worker_id = $_SESSION['user_id'];

    $conn = new mysqli("localhost", "root", "", "citywatch");
    if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

    if ($action === "accept") {
        $stmt = $conn->prepare("UPDATE reports SET status='progress', worker_id=? WHERE id=? AND status='pending'");
        $stmt->bind_param("ii", $worker_id, $id);
    } elseif ($action === "resolve") {
        $stmt = $conn->prepare("UPDATE reports SET status='resolved', resolved_at=NOW() WHERE id=? AND worker_id=?");
        $stmt->bind_param("ii", $id, $worker_id);
    } elseif ($action === "archive") {
        $stmt = $conn->prepare("UPDATE reports SET status='archived' WHERE id=? AND worker_id=?");
        $stmt->bind_param("ii", $id, $worker_id);
    } else {
        http_response_code(400);
        echo "Invalid action";
        exit();
    }

    $stmt->execute();
    if ($stmt->affected_rows > 0) { echo "success"; } 
    else { echo "no_change";}

    $stmt->close();
    $conn->close();
?>