<?php
    require_once 'config/db.php';
    $conn = getDB();
?>
<!DOCTYPE html>
<html lang="en">

<?php $pageTitle = 'System Logs - CityWatch'; include 'views/layout/head.php'; ?>

<body>
    <?php $navActive = 'logs'; include 'views/layout/header.php'; ?>
    <section class="dashboard-section">
        <div class="overlay"></div>
        <div class="dashboard-container admin-layout">
            <div id="settings" class="admin-content-section active">
                <div class="admin-table-card logs-card">
                    <h2>> System Internal Logs</h2>
                    <p>[06:12:01] Database backup completed successfully.</p>
                    <p>[06:10:45] New admin login detected: IP 192.168.1.5</p>
                    <p>[05:55:12] Auto-report: 5 issues moved to "Overdue" status.</p>
                </div>
            </div>

        </div>
    </section>
    <?php include 'views/layout/footer.php'; ?>

</body>

</html>