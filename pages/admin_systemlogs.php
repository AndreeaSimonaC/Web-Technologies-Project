<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';
require_once '../models/LogModel.php';
$logs = getAllLogs();
require_once '../models/ReportModel.php';
    $conn = getDB();
?>
<!DOCTYPE html>
<html lang="en">

<?php $pageTitle = 'System Logs - CityWatch'; include '../views/layout/head.php'; ?>

<body>
    <?php $navActive = 'logs'; include '../views/layout/header.php'; ?>
    <section class="dashboard-section">
        <div class="overlay"></div>
        <div class="dashboard-container admin-layout">
            <div id="settings" class="admin-content-section active">
                <div class="admin-table-card logs-card">
                    <h2>> System Internal Logs</h2>
                    <?php if (empty($logs)): ?>
                       <p>No logs recorded yet.</p>
                            <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                         <p>
                               [<?= date("H:i:s", strtotime($log['created_at'])) ?>] 
                              <strong><?= htmlspecialchars($log['username'] ?? 'System') ?></strong> 
                             - <?= htmlspecialchars($log['action']) ?>
                      </p>
                              <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </section>
    <?php include '../views/layout/footer.php'; ?>

</body>

</html>