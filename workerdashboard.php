<?php
/* === MOD FRONTEND: Mockup pentru Previzualizare Design === */
$username = "Operator CityWatch #14";
$pendingTasks = 3;
$myInProgress = 1;
$totalResolved = 12;
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Command Center - CityWatch</title>
    <link rel="stylesheet" href="main.css">
    <link rel="icon" type="image/x-icon" href="assets/logosmall.png">
    <style>
        /* --- CSS pentru SPA & Efectul de Fade (Identic cu Admin) --- */
        body {
            background-color: #0b3d1f; 
        }
        
        .dashboard-section {
            min-height: 100vh;
            padding-top: 150px;
            padding-bottom: 50px;
        }

        .worker-content-section {
            display: none;
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
            width: 100%;
            z-index: 2;
        }

        .worker-content-section.active {
            display: block;
            opacity: 1;
        }

        /* --- Stil Carduri Lucrător (Glassmorphism Pro) --- */
        .worker-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            margin-bottom: 30px;
            min-height: 500px;
        }

        .worker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        /* Card de misiune individuală */
        .mission-item {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            border-left: 8px solid #0b3d1f;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s;
        }

        .mission-item:hover {
            transform: translateY(-5px);
        }

        .mission-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .badge-status {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pending { background: #ff4d4d; color: white; }
        .status-progress { background: #f39c12; color: white; }

        .mission-info h3 {
            color: #0b3d1f;
            font-size: 20px;
            margin-bottom: 5px;
        }

        .mission-info p {
            font-size: 14px;
            color: #555;
            line-height: 1.5;
        }

        .action-button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background: #0b3d1f;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .action-button:hover {
            background: #1e7e34;
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo"><img src="assets/logosmall.png" alt="Logo"></div>
        <button class="hamburger" id="hamburger-menu">
            <span class="bar"></span><span class="bar"></span><span class="bar"></span>
        </button>
        <nav class="nav-links" id="nav-links">
            <a href="#" class="nav-tab active" data-target="overview">Overview</a>
            <a href="#" class="nav-tab" data-target="tasks">Active Missions</a>
            <a href="#" class="nav-tab" data-target="map">Area Map</a>
            <a href="logout.php" class="button logout-button">Logout</a>
        </nav>
    </header>

    <section class="dashboard-section">
        <div class="overlay"></div>
        <div class="dashboard-container" style="flex-direction: column;">

            <div id="overview" class="worker-content-section active">
                <h1 style="color:white; text-align:center; margin-bottom:10px;">Operator Command Center</h1>
                <p style="color:#b7c7a1; text-align:center; margin-bottom:40px;">Ready for cleanup, <?= $username ?>?</p>
                
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:25px;">
                    <div class="impact-card"><div class="card-header">New Assignments</div><h3 class="impact-number"><?= $pendingTasks ?></h3></div>
                    <div class="impact-card" style="background:#fff4e5;"><div class="card-header" style="background:#f39c12;">In Progress</div><h3 class="impact-number" style="color:#f39c12;"><?= $myInProgress ?></h3></div>
                    <div class="impact-card" style="background:#e6f4ea;"><div class="card-header" style="background:#28a745;">My Resolved</div><h3 class="impact-number" style="color:#28a745;"><?= $totalResolved ?></h3></div>
                </div>
            </div>

            <div id="tasks" class="worker-content-section">
                <div class="worker-card">
                    <h2 style="color:#0b3d1f; margin-bottom:20px; border-bottom: 2px solid #b7c7a1; padding-bottom:10px;">Intervention Tasks</h2>
                    <div class="worker-grid">
                        <div class="mission-item">
                            <div class="mission-header">
                                <span class="badge-status status-pending">Pending</span>
                                <small>Today, 08:30</small>
                            </div>
                            <div class="mission-info">
                                <h3>Plastic Waste</h3>
                                <p><strong>Location:</strong> Str. Palat nr. 1, Iasi</p>
                                <p><strong>Info:</strong> Large accumulation near recycling bins.</p>
                            </div>
                            <button class="action-button">Accept Mission</button>
                        </div>

                        <div class="mission-item" style="border-left-color: #f39c12;">
                            <div class="mission-header">
                                <span class="badge-status status-progress">In Progress</span>
                                <small>Yesterday</small>
                            </div>
                            <div class="mission-info">
                                <h3>Household Waste</h3>
                                <p><strong>Location:</strong> Bd. Independentei</p>
                                <p><strong>Info:</strong> Full bins after weekend event.</p>
                            </div>
                            <button class="action-button" style="background:#28a745;">Mark as Resolved</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="map" class="worker-content-section">
                <div class="worker-card" style="padding: 0; overflow: hidden; height: 600px;">
                    <div style="background: #0b3d1f; color: white; padding: 15px 25px;">
                        <h2 style="margin:0; font-size: 20px;">Live Intervention Map</h2>
                    </div>
                    <div id="map-placeholder" style="height: 100%; background: #eee; display: flex; align-items: center; justify-content: center;">
                        <p style="color: #666;">[ Leaflet Map Loading... ]</p>
                    </div>
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

            // LOGICA SPA (Exact ca la Admin)
            tabs.forEach(tab => {
                tab.addEventListener("click", function(e) {
                    if (this.getAttribute("href") === "logout.php") return;
                    e.preventDefault();

                    tabs.forEach(t => t.classList.remove("active"));
                    sections.forEach(s => s.classList.remove("active"));

                    this.classList.add("active");
                    const target = this.getAttribute("data-target");
                    document.getElementById(target).classList.add("active");

                    navLinks.classList.remove("active");
                    document.body.classList.remove("no-scroll");
                });
            });

            // Hamburger
            hamburger.addEventListener("click", function() {
                navLinks.classList.toggle("active");
                document.body.classList.toggle("no-scroll");
            });
        });
    </script>
</body>
</html>