<?php

require_once '../config/db.php';
$conn = getDB();

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = "";

$status = $_GET['status'] ?? '';
$role = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

if (!empty($status)) {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($role)) {
    $sql .= " AND role = ?";
    $params[] = $role;
    $types .= "s";
}

if (!empty($search)) {

    $searchLike = "%" . $search . "%";
    $sql .= " AND ( username LIKE ? OR email LIKE ?)";

    $params[] = $searchLike;
    $params[] = $searchLike;

    $types .= "ss";
}

if (!empty($dateFrom) && !empty($dateTo)) {

    $sql .= " AND DATE(created_at) BETWEEN ? AND ?";

    $params[] = $dateFrom;
    $params[] = $dateTo;

    $types .= "ss";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) { $stmt->bind_param($types, ...$params);}
$stmt->execute();
$result = $stmt->get_result();

while ($user = $result->fetch_assoc()):
?>

<tr>
    <td>#<?= $user['id'] ?></td>
    <td>
        <strong><?= htmlspecialchars($user['username']) ?></strong>
    </td>
    <td>
        <span class="badge role-<?= strtolower($user['role']) ?>"><?= ucfirst($user['role']) ?></span>
    </td>
    <td><?= htmlspecialchars($user['email']) ?></td>

    <td>
        <?= date("d M Y", strtotime($user['created_at'])) ?><br>
        <small><?= date("H:i A", strtotime($user['created_at'])) ?></small>
    </td>

    <td>
        <?php if ($user['status'] === 'active'): ?>
            <span class="status-active">● Active</span>
        <?php else: ?>
            <span class="status-inactive">● Inactive</span>
        <?php endif; ?>
    </td>

    <td class="actions-cell">

        <button class="icon-btn view-btn"
            data-id="<?= $user['id'] ?>"
            data-name="<?= htmlspecialchars($user['username']) ?>"
            data-role="<?= $user['role'] ?>"
            data-email="<?= htmlspecialchars($user['email']) ?>"
            data-joined="<?= date("d M Y H:i", strtotime($user['created_at'])) ?>"
            data-status="<?= $user['status'] ?>">
            <img src="../public/assets/adminReportsDetails.png">
        </button>

        <button class="icon-btn edit-btn"
            data-id="<?= $user['id'] ?>">
            <img src="../public/assets/adminReportsEdit.png">
        </button>

        <button class="icon-btn danger delete-btn"
            data-id="<?= $user['id'] ?>">
            <img src="../public/assets/adminReportsDelete.png">
        </button>
    </td>
</tr>

<?php endwhile; ?>