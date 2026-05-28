<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/ReportModel.php';
require_once __DIR__ . '/../models/LogModel.php';

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
        writeLog('User #' . $id . ' deleted', $_SESSION['user_id']);
        echo "deleted";
        exit;
    }

    if ($action === "edit") {
        $role  = $_POST['role']  ?? null;
        $email = $_POST['email'] ?? null;
        if (!$role || !$email) { echo "Missing fields"; exit; }
        updateUser($id, $role, $email);
        writeLog('User #' . $id . ' role updated to ' . $role, $_SESSION['user_id']);
        echo "updated";
        exit;
    }

    if ($action === "toggle_status") {
        $conn = getDB();
        $stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res) {
            $newStatus = ($res['status'] === 'active') ? 'inactive' : 'active';
            $update = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            $update->bind_param("si", $newStatus, $id);
            $update->execute();

            writeLog('User #' . $id . ' status changed to ' . $newStatus, $_SESSION['user_id']);
            echo "status_toggled";
        }
        exit;
    }

    echo "unknown action";
}

function handleGenerateReport() {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo "Unauthorized";
        exit;
    }

    $conn     = getDB();
    $period   = $_GET['period']   ?? 'all';
    $category = $_GET['category'] ?? 'all';
    $format   = $_GET['format']   ?? 'pdf';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo   = $_GET['date_to']   ?? '';

    $where = [];
    $params = [];
    $types = "";

    if ($period === 'week') {
        $where[] = "r.created_at >= NOW() - INTERVAL 7 DAY";
    } elseif ($period === 'month') {
        $where[] = "r.created_at >= NOW() - INTERVAL 30 DAY";
    } elseif ($period === 'custom' && !empty($dateFrom) && !empty($dateTo)) {
        $where[] = "DATE(r.created_at) BETWEEN ? AND ?";
        $params[] = $dateFrom;
        $params[] = $dateTo;
        $types .= "ss";
    }

    if ($category !== 'all') {
        $where[] = "c.name = ?";
        $params[] = ucfirst($category);
        $types .= "s";
    }

    $sql = "SELECT r.id, c.name AS category, r.location, r.status, r.created_at
            FROM reports r
            JOIN categories c ON r.category_id = c.id";

    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " ORDER BY r.created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    $locationCount = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
        $loc = $row['location'];
        if (!isset($locationCount[$loc])) {
            $locationCount[$loc] = 0;
        }
        $locationCount[$loc]++;
    }

    $dirtiest = "N/A";
    $cleanest = "N/A";

    if (!empty($locationCount)) {
        arsort($locationCount);
        $dirtiest = array_key_first($locationCount);
        asort($locationCount);
        $cleanest = array_key_last($locationCount);
    }

    if ($format === 'csv') {
        if (ob_get_length()) { ob_clean(); }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="report.csv"');

        $output = fopen("php://output", "w");
        fputs($output, "\xEF\xBB\xBF");


        fputcsv($output, ["--- DATA EXPORT ---"], ",", "\"", "\\");
        fputcsv($output, ["ID", "Category", "Location", "Status", "Date"], ",", "\"", "\\");

        foreach ($rows as $row) {
            fputcsv($output, [
                $row['id'],
                $row['category'],
                $row['location'],
                $row['status'],
                $row['created_at']
            ], ",", "\"", "\\");
        }

        fclose($output);
        exit;
    }

    require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('CityWatch');
    $pdf->SetTitle('CityWatch Report');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 10);

    $html = '<h2>CityWatch Report</h2>
    <p><b>Dirtiest Area:</b> ' . htmlspecialchars($dirtiest) . '</p>
    <p><b>Cleanest Area:</b> ' . htmlspecialchars($cleanest) . '</p>
    <br>
    <table border="1" cellpadding="4">
        <thead>
            <tr bgcolor="#2e7d32" color="#ffffff">
                <th width="10%">ID</th>
                <th width="20%">Category</th>
                <th width="40%">Location</th>
                <th width="15%">Status</th>
                <th width="15%">Date</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($rows as $row) {
        $html .= '<tr>
            <td>' . $row['id'] . '</td>
            <td>' . htmlspecialchars($row['category']) . '</td>
            <td>' . htmlspecialchars($row['location']) . '</td>
            <td>' . ucfirst($row['status']) . '</td>
            <td>' . date("d M Y", strtotime($row['created_at'])) . '</td>
        </tr>';
    }

    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    if (ob_get_length()) { ob_end_clean(); }
    $pdf->Output('report.pdf', 'D');
    exit;
}
?>