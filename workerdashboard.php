<?php
    session_start();
    $conn = new mysqli("localhost", "root", "", "citywatch");
    if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
    if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit(); }
    if ($_SESSION["role"] !== "worker") { header("Location: userdashboard.php"); exit(); }

    $worker_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    $pendingQuery = $conn->query("SELECT COUNT(*) as total FROM reports WHERE status='pending'");
    $pendingTasks = $pendingQuery->fetch_assoc()['total'];

    $progressStmt = $conn->prepare("SELECT COUNT(*) as total FROM reports  WHERE status='progress' AND worker_id=?");
    $progressStmt->bind_param("i", $worker_id);
    $progressStmt->execute();
    $myInProgress = $progressStmt->get_result()->fetch_assoc()['total'];
    $progressStmt->close();

    $resolvedStmt = $conn->prepare("SELECT COUNT(*) as total  FROM reports  WHERE status IN ('resolved', 'archived')  AND worker_id=?");
    $resolvedStmt->bind_param("i", $worker_id);
    $resolvedStmt->execute();
    $totalResolved = $resolvedStmt->get_result()->fetch_assoc()['total'];
    $resolvedStmt->close();

    $reportsStmt = $conn->prepare("SELECT r.*, c.name AS category_name, u.username FROM reports r JOIN categories c ON r.category_id = c.id JOIN users u ON r.user_id = u.id
            ORDER BY CASE WHEN r.status = 'archived' THEN 1  ELSE 0 END, r.created_at DESC");
    $reportsStmt->execute();
    $reports = $reportsStmt->get_result();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Command Center - CityWatch</title>
    <link rel="stylesheet" href="public/main.css">
    <link rel="icon" type="image/x-icon" href="public/assets/logosmall.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body class="worker-page">
    <header class="navbar">
        <div class="logo"><img src="public/assets/logosmall.png" alt="Logo"></div>
        <button class="hamburger" id="hamburger-menu">
            <span class="bar"></span><span class="bar"></span><span class="bar"></span>
        </button>
        <nav class="nav-links" id="nav-links">
            <a href="#overview" class="nav-tab active" data-target="overview">Overview</a>
            <a href="#tasks" class="nav-tab" data-target="tasks">Active Missions</a>
            <a href="#map-section" class="nav-tab" data-target="map">Area Map</a>
            <a href="logout.php" class="button logout-button">Logout</a>
        </nav>
    </header>

    <section class="dashboard-section">
        <div class="overlay"></div>
        <div class="dashboard-container" style="flex-direction: column;">

            <div id="overview" class="worker-content-section active">
                <h1 class="worker-title">Operator Command Center</h1>
                <p class="worker-subtitle">Ready for cleanup, <?= $username ?>?</p>

                <div class="worker-stats-grid">
                    <div class="impact-card worker-card-pending">
                        <div class="card-header worker-header-pending">
                            <img src="public/assets/apPending.png" class="header-icon">
                            <span>Pending Assignments</span>
                        </div>
                        <div class="worker-stat-line"><h3 class="impact-number worker-text-pending"><?= $pendingTasks ?></h3></div>
                    </div>

                    <div class="impact-card worker-card-warning">
                        <div class="card-header worker-header-warning">
                            <img src="public/assets/apInProgress.png" class="header-icon">
                            <span>In Progress</span>
                        </div>
                        <div class="worker-stat-line"><h3 class="impact-number worker-text-warning"><?= $myInProgress ?></h3></div>
                    </div>

                    <div class="impact-card worker-card-success">
                        <div class="card-header worker-header-success">
                            <img src="public/assets/apResolved.png" class="header-icon">
                            <span>Resolved</span>
                        </div>
                        <div class="worker-stat-line"><h3 class="impact-number worker-text-success"><?= $totalResolved ?></h3></div>
                    </div>
                </div>
            </div>

            <div id="tasks" class="worker-content-section">
                <div class="worker-card">
                    <h2 class="worker-section-title">Intervention Tasks</h2>
                    <div class="worker-filters">
                        <div class="filters">
                            <p>Filter:</p>
                            <button class="active" data-status="all">All</button>
                            <button data-status="pending">Pending</button>
                            <button data-status="progress">In progress</button>
                            <button data-status="resolved">Resolved</button>
                            <button data-status="archived">Archived</button>
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
                    <div class="worker-grid">
                        <?php while ($row = $reports->fetch_assoc()): ?>
                            <?php
                            $icon = "";
                            switch ($row['category_id']) {
                                case 1:
                                    $icon = "public/assets/iconplastic.png";
                                    break;
                                case 2:
                                    $icon = "public/assets/iconpaper.png";
                                    break;
                                case 3:
                                    $icon = "public/assets/iconglass.png";
                                    break;
                                case 4:
                                    $icon = "public/assets/iconmetal.png";
                                    break;
                                case 5:
                                    $icon = "public/assets/iconhousehold.png";
                                    break;
                            }

                            $statusClass = "";
                            if ($row['status'] == "pending") $statusClass = "status-pending";
                            elseif ($row['status'] == "progress") $statusClass = "status-progress";

                            $categoryName = strtolower($row['category_name']);
                            ?>

                            <div class="mission-item 
                                <?= $row['status'] == 'progress' ? 'mission-progress' : '' ?>
                                <?= $row['status'] == 'archived' ? 'mission-archived' : '' ?>"
                                data-status="<?= $row['status'] ?>"
                                data-category="<?= $categoryName ?>">

                                <div class="mission-top">
                                    <div class="mission-left">
                                        <div class="mission-header">
                                            <span class="badge-status <?= $statusClass ?>"> <?= ucfirst($row['status']) ?> </span>
                                            <small> <?= date("M d, H:i", strtotime($row['created_at'])) ?> </small>
                                        </div>
                                        <div class="mission-info">
                                            <h3><?= htmlspecialchars($row['category_name']) ?> Waste</h3>
                                            <p><strong>Location:</strong> <?= htmlspecialchars($row['location']) ?> </p>
                                            <p><strong>Info:</strong> <?= htmlspecialchars($row['description']) ?> </p>
                                        </div>
                                    </div>
                                    <div class="mission-right"> <img src="<?= $icon ?>" class="mission-icon"> </div>
                                </div>

                                <?php if ($row['status'] == 'pending'): ?>
                                    <button class="action-button" data-id="<?= $row['id'] ?>" data-action="accept"> Accept Mission </button>
                                <?php elseif ($row['status'] == 'progress'): ?>
                                    <button class="action-button action-success" data-id="<?= $row['id'] ?>" data-action="resolve"> Mark as Resolved </button>
                                <?php elseif ($row['status'] == 'resolved'): ?>
                                    <button class="action-button" data-id="<?= $row['id'] ?>" data-action="archive"> Archive Report </button>
                                <?php elseif ($row['status'] == 'archived'): ?> <button class="action-button" disabled style="opacity:0.5; cursor:not-allowed;"> Archived </button>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <div id="map-section" class="worker-content-section">
                <div class="worker-card worker-map-card">
                    <div class="map-title">
                        <h2>Live Intervention Map</h2>
                    </div>
                    <div class="map-container">
                        <div id="map"></div>
                        <button id="locateButton" class="locate-button">Locate me</button>
                    </div>
                    <div class="map-footer">
                        <div class="legend">
                            <div class="legend-item"><img src="public/assets/pinpaper.png"> Paper</div>
                            <div class="legend-item"><img src="public/assets/pinplastic.png"> Plastic</div>
                            <div class="legend-item"><img src="public/assets/pinmetal.png"> Metal</div>
                            <div class="legend-item"><img src="public/assets/pinglass.png"> Glass</div>
                            <div class="legend-item"><img src="public/assets/pinhousehold.png"> Household</div>
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
                <div class="confirm-overlay" id="confirmOverlay" style="display:none;">
                    <div class="confirm-box">
                        <h3 id="confirmText">Are you sure?</h3>
                        <div class="confirm-buttons">
                            <button id="cancelBtn">Cancel</button>
                            <button id="confirmBtn">Yes</button>
                        </div>
                    </div>
                </div>
                <div class="success-overlay" id="successOverlay" style="display:none;">
                    <div class="success-box">
                        <h2>Action completed!</h2>
                        <p>Redirecting you to your dashboard.</p>
                    </div>
                </div>
            </div>
    </section>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tabs = document.querySelectorAll(".nav-tab");
            const sections = document.querySelectorAll(".worker-content-section");
            const hamburger = document.getElementById("hamburger-menu");
            const navLinks = document.getElementById("nav-links");
            hamburger.addEventListener("click", function() {
                navLinks.classList.toggle("active");
                document.body.classList.toggle("no-scroll");
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const statusButtons = document.querySelectorAll(".filters button");
            const categoryButtons = document.querySelectorAll(".category-filters button");
            const missions = document.querySelectorAll(".mission-item");

            let selectedStatus = "all";
            let selectedCategory = "all";

            statusButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    statusButtons.forEach(b => b.classList.remove("active"));
                    this.classList.add("active");
                    selectedStatus = this.dataset.status;
                    filterMissions();
                });
            });

            categoryButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    categoryButtons.forEach(b => b.classList.remove("active"));
                    this.classList.add("active");
                    selectedCategory = this.dataset.category;
                    filterMissions();
                });
            });

            function filterMissions() {
                missions.forEach(mission => {
                    const missionStatus = mission.dataset.status;
                    const missionCategory = mission.dataset.category;
                    const matchStatus = selectedStatus === "all" || missionStatus === selectedStatus;
                    const matchCategory = selectedCategory === "all" || missionCategory === selectedCategory;
                    if (matchStatus && matchCategory) { mission.style.display = "flex"; } 
                    else { mission.style.display = "none"; }
                });
            }
        });
        let currentReportId = null;
        let currentAction = null;

        document.querySelectorAll(".action-button").forEach(btn => {
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

        document.getElementById("cancelBtn").onclick = () => { document.getElementById("confirmOverlay").style.display = "none"; };
        document.getElementById("confirmBtn").onclick = () => {
            document.getElementById("confirmOverlay").style.display = "none";
            fetch("api/update_report.php", {
                    method: "POST",
                    headers: {"Content-Type": "application/x-www-form-urlencoded"},
                    body: `id=${currentReportId}&action=${currentAction}`
                })
                .then(res => res.text())
                .then(() => {
                    document.getElementById("successOverlay").style.display = "flex";
                    setTimeout(() => { location.reload(); }, 1500);
                });
        };
    </script>
    <script src="public/map.js"></script>
</body>

</html>