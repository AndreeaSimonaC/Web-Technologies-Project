<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';
require_once '../models/ReportModel.php';

    $mode    = $_GET['mode'] ?? 'daily';
    $counts  = getReportCounts();
    $change  = getWeeklyChange();
    $reports = getAllReports();

    $categoryStats = getCategoryStats();
    $totalReports  = $counts['total'];
    $pendingReports = $counts['pending'];
    $progressReports = $counts['progress'];
    $resolvedReports = $counts['resolved'];
    $categoryPercentages = [];

    foreach ($categoryStats as $name => $count) {
        $categoryPercentages[$name] = $totalReports > 0 ? round(($count / $totalReports) * 100) : 0;
    }

    $colors = ["Paper" => "#106faa", "Plastic" => "#e7ee1b", "Metal" => "#eb3e31", "Glass" => "#1fc132", "Household" => "#633b28"];
    $start = 0; $gradientParts = [];
    foreach ($categoryPercentages as $name => $percent) {
        $end = $start + $percent;
        $gradientParts[] = ($colors[$name] ?? "#ccc") . " {$start}% {$end}%";
        $start = $end;
    }
    $gradient = implode(", ", $gradientParts);

    $chartResult = getReportsOverTime($mode);
    $chartData   = $chartResult['data'];
    $labels      = $chartResult['labels'];
    $max         = max($chartData) ?: 1;
    $width = 300; $height = 150;
    $stepX  = $width / max(count($chartData) - 1, 1);
    $points = [];
    foreach ($chartData as $i => $value) {
        $points[] = ($i * $stepX) . "," . ($height - ($value / $max * $height));
    }
    $pointsString = implode(" ", $points);
    $yLabels = [];
    for ($i = 5; $i >= 0; $i--) { $yLabels[] = round(($max / 5) * $i); }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Dashboard - CityWatch'; include '../views/layout/head.php'; ?>
</head>
<body>
   <?php $navActive = 'overview'; include '../views/layout/header.php'; ?>
    <section class="dashboard-section">
        <div class="overlay"></div>
        <div class="dashboard-container admin-layout">
            <div id="overview" class="admin-content-section active">
                <h1 class="admin-title">Command Center Overview</h1>
                <h2 class="admin-subtitle">Welcome back! Here's what's happening in the city.</h2>
                <div class="admin-overview-grid">
                    <div class="stat-card">
                        <div class="stat-header header-green">
                            <img src="../public/assets/adminReportsTotalIcon.png">
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
                            <img src="../public/assets/apPending.png">
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
                            <img src="../public/assets/apInProgress.png">
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
                            <img src="../public/assets/apResolved.png">
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
                                    <div class="legend-item"><img src="../public/assets/pinpaper.png"> Paper</div>
                                    <div class="legend-item"><img src="../public/assets/pinplastic.png"> Plastic</div>
                                    <div class="legend-item"><img src="../public/assets/pinmetal.png"> Metal</div>
                                    <div class="legend-item"><img src="../public/assets/pinglass.png"> Glass</div>
                                    <div class="legend-item"><img src="../public/assets/pinhousehold.png"> Household</div>
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
                                            case 1: $icon = "../public/assets/iconplastic.png"; break;
                                            case 2: $icon = "../public/assets/iconpaper.png"; break;
                                            case 3: $icon = "../public/assets/iconglass.png"; break;
                                            case 4: $icon = "../public/assets/iconmetal.png"; break;
                                            case 5: $icon = "../public/assets/iconhousehold.png"; break;
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
                    fetch("api/update_report.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: `id=${currentReportId}&action=${currentAction}` })
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
   <script src="../js/map.js?v=<?= time() ?>"></script>
   <?php include '../views/layout/footer.php'; ?>

</body>
</html>