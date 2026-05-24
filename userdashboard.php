<?php
    session_start();
    if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit(); }
    require_once 'config/db.php';
    require_once 'models/UserModel.php';
    require_once 'models/ReportModel.php';

    $username      = $_SESSION["username"];
    $user_id       = $_SESSION["user_id"];
    $reports       = getReportsByUser($user_id);
    $totalResolved = getResolvedCountByUser($user_id);
    $stars         = $totalResolved * 10;
    $timeText      = getMemberSince($user_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityWatch User Dashboard</title>
    <link rel="stylesheet" href="public/main.css">
    <link rel="icon" type="image/x-icon" href="public/assets/logosmall.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body>
   <header class="navbar">
        <div class="logo"><img src="public/assets/logosmall.png" alt="CityWatchLogo"></div>
        
        <button class="hamburger" id="hamburger-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        
        <nav class="nav-links" id="nav-links">
            <a href="userdashboard.php">Home</a>
            <a href="#map-section">Map</a>
            <a href="#reports-section">My Reports</a>
        </nav>
        <div class="nav-right">
            <a href="logout.php" class="button logout-button"><img src="public/assets/logouticon.png" alt="logout icon">Logout</a>
        </div>
    </header>

    <section class="topsection">
        <div class="overlay"></div>
        <div class="topsection-content">
            <h1>Welcome back, <?php echo htmlspecialchars($username); ?> <br>Keep your city clean.</h1>
            <a href="reportformuser.php" class="cta-button"> + Report Waste </a>
        </div>
    </section>
    <section class="midsection" id="map-section">
        <div class="map-card">
            <div class="map-title">
                <h2>See what is happening in your city</h2>
            </div>
            <div class="map-container">
                <div id="map"></div>
                <button id="locateButton" class="locate-button">Locate me</button>
            </div>
            <div class="map-footer">
                <div class="legend">
                    <div class="legend-item"><img src="public/assets/pinpaper.png" alt="paper pin"> Paper</div>
                    <div class="legend-item"><img src="public/assets/pinplastic.png" alt="plastic pin"> Plastic</div>
                    <div class="legend-item"><img src="public/assets/pinmetal.png" alt="metal pin"> Metal</div>
                    <div class="legend-item"><img src="public/assets/pinglass.png" alt="glass pin"> Glass</div>
                    <div class="legend-item"><img src="public/assets/pinhousehold.png" alt="household pin"> Household</div>
                </div>
            </div>
        </div>
    </section>
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
    <section class="dashboard-section" id="reports-section">
        <div class="overlay"></div>
        <div class="dashboard-container">
            <div class="reports-card">
                <div class="card-header">My Reports</div>
                <div class="reports-body">
                    <div class="filters">
                        <p>Filters:</p>
                        <button class="active">All</button>
                        <button>Pending</button>
                        <button>In progress</button>
                        <button>Resolved</button>
                    </div>
                    <div class="reports-list">
                        <?php while ($row = $reports->fetch_assoc()): ?>
                            <div class="report-item">
                                <div class="report-left">
                                    <?php
                                    $icon = "";
                                    switch ($row['category_id']) {
                                        case 1:  $icon = "public/assets/iconplastic.png"; break;
                                        case 2:  $icon = "public/assets/iconpaper.png"; break;
                                        case 3: $icon = "public/assets/iconglass.png"; break;
                                        case 4: $icon = "public/assets/iconmetal.png"; break;
                                        case 5: $icon = "public/assets/iconhousehold.png"; break;
                                    }
                                    ?>
                                    <img src="<?= $icon ?>" class="report-icon">
                                    <div>
                                        <div class="report-title"><?= htmlspecialchars($row['category_name']) ?> Waste</div>
                                        <div class="report-location"><?= htmlspecialchars($row['location']) ?></div>
                                    </div>
                                </div>

                                <span class="status 
                                <?= $row['status'] == 'pending' ? 'pending' : '' ?>
                                <?= $row['status'] == 'in progress' ? 'progress' : '' ?>
                                <?= $row['status'] == 'resolved' ? 'resolved' : '' ?>
                                ">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <div class="impact-card">
                <div class="card-header">Your Impact</div>
                <div class="impact-body">
                    <h3 class="impact-number"><?= $totalResolved ?> <?= $totalResolved == 1 ? 'area' : 'areas' ?></h3>
                    <p>in the last <strong><?= $timeText ?></strong></p>
                    <div class="impact-visual">
                        <img src="public/assets/broom.png" class="broom">
                        <div class="thankyou-box">Thank you!</div>
                    </div>
                </div>
                <div class="stars-box">
                    You have <strong><?= $stars ?></strong> <?= $stars == 1 ? 'star' : 'stars' ?> so far! ⭐
                </div>
            </div>
        </div>
    </section>

    <!--footer contact-->
    <footer>
        <p>CityWatch 2026</p>
        <p>Contact us at citywatch@cleaning.com</p>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const buttons = document.querySelectorAll(".filters button");
            const reports = document.querySelectorAll(".report-item");
            buttons.forEach(button => {
                button.addEventListener("click", function() {
                    buttons.forEach(btn => btn.classList.remove("active"));
                    this.classList.add("active");
                    const filter = this.textContent.toLowerCase();
                    reports.forEach(report => {
                        const statusEl = report.querySelector(".status");

                        if (filter === "all") { report.style.display = "flex"; } 
                        else if (filter === "pending" && statusEl.classList.contains("pending")) { report.style.display = "flex"; } 
                        else if (filter === "in progress" && statusEl.classList.contains("progress")) { report.style.display = "flex"; } 
                        else if (filter === "resolved" && statusEl.classList.contains("resolved")) { report.style.display = "flex"; } 
                        else { report.style.display = "none"; }
                    });
                });
            });
        });
    </script>
    <script src="public/map.js"></script>
</body>
</html>