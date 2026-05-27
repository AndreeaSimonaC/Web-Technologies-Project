<?php
require_once '../config/db.php';

$conn = getDB();

$status = $_GET['status'] ?? '';

$sql = "SELECT  r.*,  c.name AS category_name, u.username AS reported_by FROM reports r LEFT JOIN categories c ON r.category_id = c.id LEFT JOIN users u ON r.user_id = u.id WHERE 1=1";

$params = [];
$types = "";

if (!empty($status)) {
    $sql .= " AND r.status = ?";
    $params[] = $status;
    $types .= "s";
}
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

if (!empty($category)) {
    $sql .= " AND r.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

if (!empty($search)) {
    $searchLike = "%" . $search . "%";
    $sql .= " AND (c.name LIKE ? OR r.location LIKE ? OR u.username LIKE ?)";
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;

    $types .= "sss";
}

if (!empty($dateFrom) && !empty($dateTo)) {

    $sql .= " AND DATE(r.created_at) BETWEEN ? AND ?";
    $params[] = $dateFrom;
    $params[] = $dateTo;

    $types .= "ss";
}
$sql .= " ORDER BY r.created_at DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) { $stmt->bind_param($types, ...$params); }

$stmt->execute();
$result = $stmt->get_result();
while ($report = $result->fetch_assoc()):
?>

<tr>
    <td>#<?= $report['id'] ?></td>
    <td>
        <span class="badge category-<?= $report['category_id'] ?>"><?= htmlspecialchars($report['category_name']) ?></span>
    </td>
    <td><?= htmlspecialchars($report['location']) ?></td>
    <td>
        <?= date("d M Y", strtotime($report['created_at'])) ?><br>
        <small><?= date("H:i A", strtotime($report['created_at'])) ?></small>
    </td>
    <td>
        <strong><?= htmlspecialchars($report['reported_by'] ?? 'Unknown') ?></strong>
    </td>
    <td>
        <span class="badge status-<?= strtolower($report['status']) ?>"> <?= ucfirst(str_replace('_', ' ', $report['status'])) ?></span>
    </td>

    <td class="actions-cell">

        <button class="icon-btn view-btn"
            data-id="<?= $report['id'] ?>"
            data-category="<?= htmlspecialchars($report['category_name']) ?>"
            data-location="<?= htmlspecialchars($report['location']) ?>"
            data-date="<?= date("d M Y H:i", strtotime($report['created_at'])) ?>"
            data-user="<?= htmlspecialchars($report['reported_by'] ?? 'Unknown') ?>"
            data-status="<?= $report['status'] ?>"
            data-description="<?= htmlspecialchars($report['description']) ?>">
            <img src="../public/assets/adminReportsDetails.png">
        </button>

        <button class="icon-btn edit-btn"
            data-id="<?= $report['id'] ?>">
            <img src="../public/assets/adminReportsEdit.png">
        </button>

        <button class="icon-btn danger delete-btn"
            data-id="<?= $report['id'] ?>">
            <img src="../public/assets/adminReportsDelete.png">
        </button>
    </td>
</tr>

<?php endwhile; ?>