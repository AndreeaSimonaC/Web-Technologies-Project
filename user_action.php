<?php
    require_once 'config/db.php';
    $conn = getDB();
    if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

    $id = $_POST['id'] ?? null;
    $action = $_POST['action'] ?? null;

    if (!$id || !$action) { die("Missing data"); }

    error_log("Action: $action | ID: $id");

    if ($action === "delete") {

        $stmt1 = $conn->prepare("DELETE FROM reports WHERE user_id = ?");
        $stmt1->bind_param("i", $id);
        $stmt1->execute();

        $stmt2 = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();

        echo "deleted";
        exit;
    }
if ($action === "edit") {
    $role = $_POST['role'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE users SET role=?, email=? WHERE id=?");
    $stmt->bind_param("ssi", $role, $email, $id);
    $stmt->execute();
}
    if ($action === "deactivate") {

        $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo "deactivated";
        exit;
    }

    echo "unknown action";
?>