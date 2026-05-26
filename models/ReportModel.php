<?php
require_once __DIR__ . '/../config/db.php';

function getAllReports() {
    $conn = getDB();
    $stmt = $conn->prepare("
        SELECT r.*, c.name AS category_name, u.username 
        FROM reports r 
        JOIN categories c ON r.category_id = c.id 
        JOIN users u ON r.user_id = u.id 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute();
    return $stmt->get_result();
}

function getReportsByUser($user_id) {
    $conn = getDB();
    $stmt = $conn->prepare("
        SELECT r.id, r.description, r.location, r.status, r.created_at, r.category_id, 
               c.name AS category_name 
        FROM reports r 
        JOIN categories c ON r.category_id = c.id 
        WHERE r.user_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getReportCounts() {
    $conn = getDB();
    $counts = [];

    $counts['total']    = $conn->query("SELECT COUNT(*) as t FROM reports")->fetch_assoc()['t'];
    $counts['pending']  = $conn->query("SELECT COUNT(*) as t FROM reports WHERE status='pending'")->fetch_assoc()['t'];
    $counts['progress'] = $conn->query("SELECT COUNT(*) as t FROM reports WHERE status='progress'")->fetch_assoc()['t'];
    $counts['resolved'] = $conn->query("SELECT COUNT(*) as t FROM reports WHERE status='resolved' OR status='archived'")->fetch_assoc()['t'];

    return $counts;
}

function getWeeklyChange() {
    $conn = getDB();
    $thisWeek = $conn->query("SELECT COUNT(*) as t FROM reports WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetch_assoc()['t'];
    $lastWeek = $conn->query("SELECT COUNT(*) as t FROM reports WHERE created_at BETWEEN NOW() - INTERVAL 14 DAY AND NOW() - INTERVAL 7 DAY")->fetch_assoc()['t'];
    return $lastWeek > 0 ? round((($thisWeek - $lastWeek) / $lastWeek) * 100) : 0;
}

function getCategoryStats() {
    $conn = getDB();
    $stats = [];
    $result = $conn->query("
        SELECT c.name, COUNT(*) as total 
        FROM reports r 
        JOIN categories c ON r.category_id = c.id 
        GROUP BY c.name
    ");
    while ($row = $result->fetch_assoc()) {
        $stats[ucfirst($row['name'])] = $row['total'];
    }
    return $stats;
}

function getReportsOverTime($mode = 'daily') {
    $conn = getDB();
    $chartData = [];
    $labels = [];

    if ($mode === 'weekly') {
        $result = $conn->query("
            SELECT YEARWEEK(created_at, 1) as week, COUNT(*) as total 
            FROM reports 
            WHERE created_at >= NOW() - INTERVAL 8 WEEK 
            GROUP BY week
        ");
        $weeklyReports = [];
        while ($row = $result->fetch_assoc()) { $weeklyReports[$row['week']] = $row['total']; }
        for ($i = 7; $i >= 0; $i--) {
            $week = date("oW", strtotime("-$i week"));
            $labels[]    = "W" . substr($week, -2);
            $chartData[] = $weeklyReports[$week] ?? 0;
        }
    } else {
        $result = $conn->query("
            SELECT DATE(created_at) as day, COUNT(*) as total 
            FROM reports 
            WHERE created_at >= NOW() - INTERVAL 7 DAY 
            GROUP BY DATE(created_at)
        ");
        $dailyReports = [];
        while ($row = $result->fetch_assoc()) { $dailyReports[$row['day']] = $row['total']; }
        for ($i = 6; $i >= 0; $i--) {
            $date        = date("Y-m-d", strtotime("-$i days"));
            $labels[]    = date("D", strtotime($date));
            $chartData[] = $dailyReports[$date] ?? 0;
        }
    }

    return ['data' => $chartData, 'labels' => $labels];
}

function getLocationStats($limit = 5) {
    $conn = getDB();
    $stats = [];
    $stmt = $conn->prepare("
        SELECT location, COUNT(*) as total 
        FROM reports 
        GROUP BY location 
        ORDER BY total DESC 
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $shortName = explode(",", $row['location'])[0];
        $stats[$shortName] = $row['total'];
    }
    return $stats;
}

function getStatusStats() {
    $conn = getDB();
    $stats = [];
    $result = $conn->query("SELECT status, COUNT(*) as total FROM reports GROUP BY status");
    while ($row = $result->fetch_assoc()) { $stats[$row['status']] = $row['total']; }
    return $stats;
}

function getResolvedCountByUser($user_id) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reports WHERE user_id = ? AND status = 'resolved'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}
?>