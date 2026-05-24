<?php
    $conn = new mysqli("localhost", "root", "", "citywatch");
    if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

    $mode = $_GET['mode'] ?? 'daily';
    $reportsStmt = $conn->prepare("SELECT r.*, c.name AS category_name, u.username FROM reports r JOIN categories c ON r.category_id = c.id JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC");

    $reportsStmt->execute();
    $reports = $reportsStmt->get_result();

    $result = $conn->query("SELECT COUNT(*) as total FROM reports");
    $row = $result->fetch_assoc();
    $totalReports = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM reports WHERE status='pending'");
    $row = $result->fetch_assoc();
    $pendingReports = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM reports WHERE status='progress'");
    $row = $result->fetch_assoc();
    $progressReports = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total  FROM reports  WHERE status='resolved' OR status='archived'");
    $row = $result->fetch_assoc();
    $resolvedReports = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total  FROM reports  WHERE created_at >= NOW() - INTERVAL 7 DAY");
    $row = $result->fetch_assoc();
    $thisWeek = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total  FROM reports  WHERE created_at BETWEEN NOW() - INTERVAL 14 DAY AND NOW() - INTERVAL 7 DAY");
    $row = $result->fetch_assoc();
    $lastWeek = $row['total'];

    $change = 0;

    if ($lastWeek > 0) {
        $difference = $thisWeek - $lastWeek;
        $change = ($difference / $lastWeek) * 100;
    }

    $categoryStats = [];
    $categoryQuery = $conn->query("SELECT c.name, COUNT(*) as total FROM reports r JOIN categories c ON r.category_id = c.id GROUP BY c.name");
    while ($row = $categoryQuery->fetch_assoc()) {
        $name = ucfirst($row['name']); 
        $categoryStats[$name] = $row['total'];
    }

    $categoryPercentages = [];
    foreach ($categoryStats as $name => $count) { $categoryPercentages[$name] = $totalReports > 0 ? round(($count / $totalReports) * 100) : 0;}

    $colors = [ "Paper" => "#106faa", "Plastic" => "#e7ee1b", "Metal" => "#eb3e31", "Glass" => "#1fc132", "Household" => "#633b28"];

    $start = 0;
    $gradientParts = [];

    foreach ($categoryPercentages as $name => $percent) {
        $end = $start + $percent;
        $color = $colors[$name] ?? "#ccc";
        $gradientParts[] = "$color {$start}% {$end}%";
        $start = $end;
    }
    $gradient = implode(", ", $gradientParts);

    $dailyReports = [];

    $query = $conn->query("SELECT DATE(created_at) as day, COUNT(*) as total FROM reports WHERE created_at >= NOW() - INTERVAL 7 DAY GROUP BY DATE(created_at)");

    while ($row = $query->fetch_assoc()) { $dailyReports[$row['day']] = $row['total'];}

    $chartData = [];
    $labels = [];

    if ($mode === 'weekly') {
        $weeklyReports = [];
        $query = $conn->query("SELECT YEARWEEK(created_at, 1) as week, COUNT(*) as total FROM reports WHERE created_at >= NOW() - INTERVAL 8 WEEK GROUP BY week");
        while ($row = $query->fetch_assoc()) { $weeklyReports[$row['week']] = $row['total']; }
        for ($i = 7; $i >= 0; $i--) {
            $week = date("oW", strtotime("-$i week"));
            $labels[] = "W" . substr($week, -2);
            $chartData[] = $weeklyReports[$week] ?? 0;
        }
    } else {
        $dailyReports = [];
        $query = $conn->query("SELECT DATE(created_at) as day, COUNT(*) as total FROM reports WHERE created_at >= NOW() - INTERVAL 7 DAY GROUP BY DATE(created_at)");
        while ($row = $query->fetch_assoc()) { $dailyReports[$row['day']] = $row['total']; }
        for ($i = 6; $i >= 0; $i--) {
            $date = date("Y-m-d", strtotime("-$i days"));
            $labels[] = date("D", strtotime($date));
            $chartData[] = $dailyReports[$date] ?? 0;
        }
    }

    $max = max($chartData) ?: 1;
    $points = [];

    $width = 300;
    $height = 150;
    $stepX = $width / (count($chartData) - 1);

    foreach ($chartData as $i => $value) {
        $x = $i * $stepX;
        $y = $height - ($value / $max * $height);
        $points[] = "$x,$y";
    }
    $pointsString = implode(" ", $points);

    $ySteps = 5;
    $yLabels = [];

    for ($i = $ySteps; $i >= 0; $i--) { $yLabels[] = round(($max / $ySteps) * $i); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Command Center - CityWatch</title>
    <link rel="stylesheet" href="public/main.css">
    <link rel="icon" type="image/x-icon" href="assets/logosmall.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body>
    <header class="navbar">
        <div class="logo"><img src="assets/logosmall.png" alt="Logo"></div>
        <button class="hamburger" id="hamburger-menu">
            <span class="bar"></span><span class="bar"></span><span class="bar"></span>
        </button>
        <nav class="nav-links" id="nav-links">
            <a href="admindashboard.php" class="nav-tab active">Overview</a>
            <a href="admin_reports.php" class="nav-tab">Reports DB</a>
            <a href="admin_users.php" class="nav-tab">User Control</a>
            <a href="admin_statistics.php" class="nav-tab">Statistics</a>
            <a href="admin_systemlogs.php" class="nav-tab">System Logs</a>
            <a href="logout.php" class="button logout-button">Logout</a>
        </nav>
    </header>
    <section class="dashboard-section">
        <div class="overlay"></div>
        <div class="dashboard-container admin-layout">
            <div id="overview" class="admin-content-section active">
                <h1 class="admin-title">Command Center Overview</h1>
                <h2 class="admin-subtitle">Welcome back! Here's what's happening in the city.</h2>
                <div class="admin-overview-grid">
                    <div class="stat-card">
                        <div class="stat-header header-green">
                            <img src="assets/adminReportsTotalIcon.png">
                            <span>Total Reports</span>
                        </div>
                        <div class="stat-body">
                            <h3><?php echo $totalReports; ?></h3>
                            <span class="stat-change <?= $change >= 0 ? 'positive' : 'negative' ?>">
                                <?= $change >= 0 ? '↑' : '↓' ?> <?= abs(round($change)) ?>% from last week
                            </span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-red">
                            <img src="assets/apPending.png">
                            <span>Pending Reports</span>
                        </div>
                        <div class="stat-body">
                            <h3><?php echo $pendingReports; ?></h3>
                            <span class="stat-change <?= $change >= 0 ? 'positive' : 'negative' ?>">
                                <?= $change >= 0 ? '↑' : '↓' ?> <?= abs(round($change)) ?>% from last week
                            </span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-orange">
                            <img src="assets/apInProgress.png">
                            <span>In Progress</span>
                        </div>
                        <div class="stat-body">
                            <h3><?php echo $progressReports; ?></h3>
                            <span class="stat-change <?= $change >= 0 ? 'positive' : 'negative' ?>">
                                <?= $change >= 0 ? '↑' : '↓' ?> <?= abs(round($change)) ?>% from last week
                            </span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-green-soft">
                            <img src="assets/apResolved.png">
                            <span>Resolved Reports</span>
                        </div>
                        <div class="stat-body">
                            <h3><?php echo $resolvedReports; ?></h3>
                            <span class="stat-change <?= $change >= 0 ? 'positive' : 'negative' ?>">
                                <?= $change >= 0 ? '↑' : '↓' ?> <?= abs(round($change)) ?>% from last week
                            </span>
                        </div>
                    </div>
                </div>
                <div class="admin-map-section">
                    <h2 class="admin-section-title">Reports Map Overview</h2>
                    <div class="admin-map-grid">
                        <div class="worker-card worker-map-card">
                            <div class="map-container">
                                <div id="map"></div>
                                <button id="locateButton" class="locate-button">Locate me</button>
                            </div>
                            <div class="map-footer">
                                <div class="legend">
                                    <div class="legend-item"><img src="assets/pinpaper.png"> Paper</div>
                                    <div class="legend-item"><img src="assets/pinplastic.png"> Plastic</div>
                                    <div class="legend-item"><img src="assets/pinmetal.png"> Metal</div>
                                    <div class="legend-item"><img src="assets/pinglass.png"> Glass</div>
                                    <div class="legend-item"><img src="assets/pinhousehold.png"> Household</div>
                                </div>
                            </div>
                        </div>
                        <div class="admin-right-panel">
                            <div class="worker-filters">
                                <div class="filters">
                                    <p>Filter:</p>
                                    <button class="active" data-status="all">All</button>
                                    <button data-status="pending">Pending</button>
                                    <button data-status="progress">In progress</button>
                                    <button data-status="resolved">Resolved</button>
                                </div>
                                <div class="category-filters">
                                    <p>Categories:</p>
                                    <button class="active" data-category="all">All</button>
                                    <button data-category="plastic">Plastic</button>
                                    <button data-category="glass">Glass</button>
                                    <button data-category="paper">Paper</button>
                                    <button data-category="metal">Metal</button>
                                    <button data-category="household">Household</button>
                                </div>
                            </div>
                            <div class="admin-reports-card">
                                <div class="admin-reports-header">
                                    <h3>Recent Reports</h3>
                                    <a href="#" id="toggleReports">View All Reports →</a>
                                </div>
                                <div class="admin-reports-list">
                                    <?php
                                    $reports->data_seek(0);
                                    $index = 0;
                                    while ($row = $reports->fetch_assoc()):
                                        $index++;
                                        $icon = "";
                                        switch ($row['category_id']) { 
                                            case 1: $icon = "assets/iconplastic.png"; break;
                                            case 2: $icon = "assets/iconpaper.png"; break;
                                            case 3: $icon = "assets/iconglass.png"; break;
                                            case 4: $icon = "assets/iconmetal.png"; break;
                                            case 5: $icon = "assets/iconhousehold.png"; break;
                                        }
                                        $statusClass = "";
                                        if ($row['status'] == "pending") $statusClass = "status-pending";
                                        elseif ($row['status'] == "progress") $statusClass = "status-progress";
                                        elseif ($row['status'] == "resolved" || $row['status'] == "archived") $statusClass = "status-resolved";
                                    ?>
                                        <div class="admin-report-item"
                                            data-index="<?= $index ?>"
                                            data-status="<?= $row['status'] ?>"
                                            data-category="<?= strtolower($row['category_name']) ?>">
                                            <div class="report-left"><img src="<?= $icon ?>" class="report-icon"></div>
                                            <div class="report-center">
                                                <strong><?= htmlspecialchars($row['category_name']) ?> Waste</strong>
                                                <span><?= htmlspecialchars($row['location']) ?></span>
                                            </div>
                                            <div class="report-right">
                                                <span class="badge-status <?= $statusClass ?>"><?= ucfirst($row['status']) ?></span>
                                                <small><?= date("H:i", strtotime($row['created_at'])) ?></small>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                        <div class="report-overlay" id="reportOverlay" style="display:none;">
                            <div class="report-overlay-box">
                                <div class="close-btn" onclick="closeOverlay()">✕</div>
                                <p><strong>Waste category:</strong> <span id="ovCategory"></span></p>
                                <p><strong>Location:</strong> <span id="ovLocation"></span></p>
                                <p><strong>Submitted by:</strong> <span id="ovUser"></span></p>
                                <p><strong>Date:</strong> <span id="ovDate"></span></p>
                                <p><strong>Status:</strong> <span id="ovStatus"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tabs = document.querySelectorAll(".nav-tab");
            const sections = document.querySelectorAll(".admin-content-section");
            const hamburger = document.getElementById("hamburger-menu");
            const navLinks = document.getElementById("nav-links");

            hamburger.addEventListener("click", function() {
                navLinks.classList.toggle("active");
                document.body.classList.toggle("no-scroll");
            });
            const statusButtons = document.querySelectorAll(".filters button");
            const categoryButtons = document.querySelectorAll(".category-filters button");
            const reports = document.querySelectorAll(".admin-report-item");

            let selectedStatus = "all";
            let selectedCategory = "all";

            statusButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    statusButtons.forEach(b => b.classList.remove("active"));
                    this.classList.add("active");
                    selectedStatus = this.dataset.status;
                    filterReports();
                });
            });

            categoryButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    categoryButtons.forEach(b => b.classList.remove("active"));
                    this.classList.add("active");
                    selectedCategory = this.dataset.category;
                    filterReports();
                });
            });

            function filterReports() {
                reports.forEach(report => {
                    const reportStatus = report.dataset.status;
                    const reportCategory = report.dataset.category;
                    const matchStatus = selectedStatus === "all" || reportStatus === selectedStatus;
                    const matchCategory = selectedCategory === "all" || reportCategory === selectedCategory;
                    report.style.display = (matchStatus && matchCategory) ? "flex" : "none";
                });
            }
            const actionButtons = document.querySelectorAll(".action-button");
            let currentReportId = null;
            let currentAction = null;

            actionButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    currentReportId = this.dataset.id;
                    currentAction = this.dataset.action;
                    let text = "";
                    if (currentAction === "accept") text = "Send team to clean up?";
                    if (currentAction === "resolve") text = "Mark this mission as resolved?";
                    if (currentAction === "archive") text = "Archive this report?";
                    document.getElementById("confirmText").textContent = text;
                    document.getElementById("confirmOverlay").style.display = "flex";
                });
            });

            const cancelBtn = document.getElementById("cancelBtn");
            const confirmBtn = document.getElementById("confirmBtn");

            if (cancelBtn) { cancelBtn.onclick = () => { document.getElementById("confirmOverlay").style.display = "none"; }; }
            if (confirmBtn) {  confirmBtn.onclick = () => { document.getElementById("confirmOverlay").style.display = "none";
                    fetch("update_report.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: `id=${currentReportId}&action=${currentAction}` })
                        .then(res => res.text())
                        .then(() => { document.getElementById("successOverlay").style.display = "flex"; setTimeout(() => location.reload(), 1500); });
                };
            }
        });

        const toggleBtn = document.getElementById("toggleReports");
        let expanded = false;
        function applyLimit() {
            const reports = document.querySelectorAll(".admin-report-item");
            reports.forEach(report => {
                const index = parseInt(report.dataset.index);
                if (!expanded && index > 3) { report.style.display = "none";
                } else { report.style.display = "flex"; }
            });
        }
        applyLimit();
        toggleBtn.addEventListener("click", function(e) {
            e.preventDefault();
            expanded = !expanded;
            applyLimit();
            toggleBtn.textContent = expanded ? "Show Less Reports ↑" : "View All Reports →";
        });

        document.addEventListener("DOMContentLoaded", function() {
            const tooltip = document.getElementById("chartTooltip");
            const dots = document.querySelectorAll(".chart-dot");
            dots.forEach(dot => { dot.addEventListener("mousemove", (e) => {
                    const value = dot.dataset.value;
                    const label = dot.dataset.label;
                    tooltip.innerHTML = `${label}: ${value} reports`;
                    tooltip.style.display = "block";
                    tooltip.style.left = e.clientX + 10 + "px";
                    tooltip.style.top = e.clientY - 30 + "px";
                    tooltip.style.opacity = 1;
                });
                dot.addEventListener("mouseleave", () => { tooltip.style.opacity = 0;  tooltip.style.display = "none";  });
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            const modeSelect = document.getElementById("chartMode");
            if (modeSelect) { modeSelect.addEventListener("change", function () { const mode = this.value;  window.location.href = "?mode=" + mode; }); }
        });
    </script>
    <script src="map.js"></script>
</body>
</html>