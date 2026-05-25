<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

require_once 'config/db.php';
require_once 'models/UserModel.php';
require_once 'models/ReportModel.php';

function handleUserAction() {
    $id     = $_POST['id']     ?? null;
    $action = $_POST['action'] ?? null;

    if (!$id || !$action) {
        echo "Missing data";
        exit;
    }

    if ($action === "delete") {
        deleteUser($id);
        echo "deleted";
        exit;
    }

    if ($action === "edit") {
        $role  = $_POST['role']  ?? null;
        $email = $_POST['email'] ?? null;
        if (!$role || !$email) { echo "Missing fields"; exit; }
        updateUser($id, $role, $email);
        echo "updated";
        exit;
    }

    if ($action === "deactivate") {
        deactivateUser($id);
        echo "deactivated";
        exit;
    }

    echo "unknown action";
}

function handleGenerateReport() {
    $conn     = getDB();
    $period   = $_GET['period']   ?? 'week';
    $category = $_GET['category'] ?? 'all';
    $status   = $_GET['status']   ?? 'all';

    $query = "
        SELECT r.id, c.name AS category, r.location, r.status, r.created_at 
        FROM reports r 
        JOIN categories c ON r.category_id = c.id 
        WHERE 1=1
    ";

    if ($period === 'week') {
        $query .= " AND r.created_at >= NOW() - INTERVAL 7 DAY";
    } elseif ($period === 'month') {
        $query .= " AND r.created_at >= NOW() - INTERVAL 30 DAY";
    }

    if ($category !== 'all') {
        $query .= " AND c.name = '" . $conn->real_escape_string($category) . "'";
    }
    if ($status !== 'all') {
        $query .= " AND r.status = '" . $conn->real_escape_string($status) . "'";
    }

    $result = $conn->query($query);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="report.csv"');

    $output = fopen("php://output", "w");
    fputcsv($output, ["ID", "Category", "Location", "Status", "Date"]);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}
?>