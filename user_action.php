<?php
    require_once 'config/db.php';
    require_once 'models/UserModel.php';
    $conn = getDB();
    if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

    $id = $_POST['id'] ?? null;
    $action = $_POST['action'] ?? null;

    if (!$id || !$action) { die("Missing data"); }

    error_log("Action: $action | ID: $id");

    if ($action === "delete") {
    deleteUser($id);
    echo "deleted";
    exit;
}

if ($action === "edit") {
    $role  = $_POST['role'];
    $email = $_POST['email'];
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
?>