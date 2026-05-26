<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/ReportModel.php';

function handleReportAction() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo "Unauthorized";
        exit;
    }
    $conn   = getDB();
    $id     = $_POST['id'] ?? null;
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
        $stmt = $conn->prepare("UPDATE reports SET status = 'resolved', resolved_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo "resolved";

    } elseif ($action === "assign") {
        $worker_id = $_POST['worker_id'] ?? 1;
        $stmt = $conn->prepare("UPDATE reports SET worker_id = ?, status = 'progress' WHERE id = ?");
        $stmt->bind_param("ii", $worker_id, $id);
        $stmt->execute();
        echo "assigned";

    } elseif ($action === "archive") {
        $stmt = $conn->prepare("UPDATE reports SET status = 'archived' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo "archived";

    } elseif ($action === "edit") {
        $category    = $_POST['category']    ?? null;
        $location    = $_POST['location']    ?? null;
        $description = $_POST['description'] ?? null;
        $status      = $_POST['status']      ?? null;

        if (!$category || !$location || !$description || !$status) {
            echo "Missing fields";
            exit;
        }

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
}

function handleWorkerReportAction() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
        http_response_code(403);
        echo "Unauthorized";
        exit;
    }

    $conn      = getDB();
    $id        = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $action    = $_POST['action'] ?? null;
    $worker_id = $_SESSION['user_id'];

    if (!$id || !$action) {
        http_response_code(400);
        echo "Missing data";
        exit;
    }

    if ($action === "accept") {
        $stmt = $conn->prepare("UPDATE reports SET status = 'progress', worker_id = ? WHERE id = ? AND status = 'pending'");
        $stmt->bind_param("ii", $worker_id, $id);

    } elseif ($action === "resolve") {
        $stmt = $conn->prepare("UPDATE reports SET status = 'resolved', resolved_at = NOW() WHERE id = ? AND worker_id = ?");
        $stmt->bind_param("ii", $id, $worker_id);

    } elseif ($action === "archive") {
        $stmt = $conn->prepare("UPDATE reports SET status = 'archived' WHERE id = ? AND worker_id = ?");
        $stmt->bind_param("ii", $id, $worker_id);

    } else {
        http_response_code(400);
        echo "Invalid action";
        exit;
    }

    $stmt->execute();
    echo $stmt->affected_rows > 0 ? "success" : "no_change";
}
?>