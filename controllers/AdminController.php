<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/ReportModel.php';

function handleUserAction() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo "Unauthorized";
        exit;
    }
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
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo "Unauthorized";
        exit;
    }

    $conn     = getDB();
    $period   = $_GET['period']   ?? 'week';
    $category = $_GET['category'] ?? 'all';
    $status   = $_GET['status']   ?? 'all';
    $format   = $_GET['format']   ?? 'csv';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo   = $_GET['date_to']   ?? '';

    $where = "WHERE 1=1";

    if ($period === 'week') {
        $where .= " AND r.created_at >= NOW() - INTERVAL 7 DAY";
    } elseif ($period === 'month') {
        $where .= " AND r.created_at >= NOW() - INTERVAL 30 DAY";
    } elseif ($period === 'custom' && $dateFrom && $dateTo) {
        $where .= " AND DATE(r.created_at) BETWEEN '" . $conn->real_escape_string($dateFrom) . "' AND '" . $conn->real_escape_string($dateTo) . "'";
    }

    if ($category !== 'all') {
        $where .= " AND c.name = '" . $conn->real_escape_string($category) . "'";
    }
    if ($status !== 'all') {
        $where .= " AND r.status = '" . $conn->real_escape_string($status) . "'";
    }

    $result = $conn->query("
        SELECT r.id, c.name AS category, r.location, r.status, r.created_at
        FROM reports r
        JOIN categories c ON r.category_id = c.id
        $where
        ORDER BY r.created_at DESC
    ");

    $rows = [];
    while ($row = $result->fetch_assoc()) { $rows[] = $row; }

    $locationCount = [];
   $cartiere = [
    'Bucium', 'Copou', 'Tudor Vladimirescu', 'Tudor', 'Podu Roș', 'Podu Ros',
    'Alexandru cel Bun', 'Alexandru', 'Nicolina', 'Dacia', 'Păcurari', 'Pacurari',
    'CUG', 'Bularga', 'Cantemir', 'Aviației', 'Aviatiei', 'Tătărași', 'Tatarasi',
    'Moara de Vânt', 'Moara de Vant', 'Galata', 'Centru', 'Frumoasa', 'Manta Roșie',
    'Manta Rosie', 'Baza 3', 'Podu de Fier', 'Târgu Cucu', 'Targu Cucu', 'Țicău',
    'Ticau', 'Sărărie', 'Sararie', 'Canta', 'Zimbru', 'Dancu', 'Valea Lupului', 'Miroslava'
];

$normalizare = [
    'Podu Ros'      => 'Podu Roș',
    'Pacurari'      => 'Păcurari',
    'Aviatiei'      => 'Aviației',
    'Tatarasi'      => 'Tătărași',
    'Moara de Vant' => 'Moara de Vânt',
    'Manta Rosie'   => 'Manta Roșie',
    'Targu Cucu'    => 'Târgu Cucu',
    'Ticau'         => 'Țicău',
    'Sararie'       => 'Sărărie',
    'Tudor'         => 'Tudor Vladimirescu',
    'Alexandru'     => 'Alexandru cel Bun'
];

$locationCount = [];
foreach ($rows as $row) {
    $raw   = $row['location'];
    $found = false;
    foreach ($cartiere as $cartier) {
        if (stripos($raw, $cartier) !== false) {
            $nume = $normalizare[$cartier] ?? $cartier;
            $locationCount[$nume] = ($locationCount[$nume] ?? 0) + 1;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $parts   = explode(',', $raw);
        $scurt   = trim($parts[0]);
        if (strlen($scurt) > 25) { $scurt = substr($scurt, 0, 22) . '...'; }
        $locationCount[$scurt] = ($locationCount[$scurt] ?? 0) + 1;
    }
}
arsort($locationCount);
$dirtiest = array_key_first($locationCount) ?? 'N/A';
$cleanest = array_key_last($locationCount)  ?? 'N/A';

    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report.csv"');
        $output = fopen("php://output", "w");
        fputcsv($output, ["ID", "Category", "Location", "Status", "Date"]);
        fputcsv($output, []);
        fputcsv($output, ["Dirtiest Area", $dirtiest, "Most reports: " . ($locationCount[$dirtiest] ?? 0)]);
        fputcsv($output, ["Cleanest Area", $cleanest, "Least reports: " . ($locationCount[$cleanest] ?? 0)]);
        fputcsv($output, []);
        foreach ($rows as $row) { fputcsv($output, $row); }
        fclose($output);
        exit;
    }

    if ($format === 'html') {
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="report.html"');
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
        <title>CityWatch Report</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 30px; color: #333; }
            h1 { color: #2e7d32; }
            .highlight { display: flex; gap: 20px; margin-bottom: 20px; }
            .box { padding: 15px 20px; border-radius: 8px; flex: 1; }
            .dirty { background: #fdecea; border-left: 5px solid #c0392b; }
            .clean { background: #e8f5e9; border-left: 5px solid #2e7d32; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background: #2e7d32; color: white; padding: 10px; text-align: left; }
            td { padding: 8px 10px; border-bottom: 1px solid #ddd; }
            tr:nth-child(even) { background: #f9f9f9; }
        </style></head><body>';
        echo '<h1>CityWatch Report</h1>';
        echo '<p>Generated: ' . date("d M Y, H:i") . '</p>';
        echo '<div class="highlight">';
        echo '<div class="box dirty"><strong>Dirtiest Area:</strong> ' . htmlspecialchars($dirtiest) . ' (' . ($locationCount[$dirtiest] ?? 0) . ' reports)</div>';
        echo '<div class="box clean"><strong>Cleanest Area:</strong> ' . htmlspecialchars($cleanest) . ' (' . ($locationCount[$cleanest] ?? 0) . ' reports)</div>';
        echo '</div>';
        echo '<table><tr><th>ID</th><th>Category</th><th>Location</th><th>Status</th><th>Date</th></tr>';
        foreach ($rows as $row) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . htmlspecialchars($row['category']) . '</td>';
            echo '<td>' . htmlspecialchars($row['location']) . '</td>';
            echo '<td>' . htmlspecialchars($row['status']) . '</td>';
            echo '<td>' . $row['created_at'] . '</td>';
            echo '</tr>';
        }
        echo '</table></body></html>';
        exit;
    }
}
?>