<?php
/* === MOD FRONTEND: Mockup pentru Previzualizare Design === */
$totalReports = 142;
$totalUsers = 854;
$resolved = 120;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Command Center - CityWatch</title>
    <link rel="stylesheet" href="main.css">
    <link rel="icon" type="image/x-icon" href="assets/logosmall.png">
    <style>
       /* --- CSS pentru SPA & Efectul de Fade --- */
        
        /* 1. REZOLVAREA ECRANULUI ALB (FULL SCREEN) */
        body {
            background-color: #0b3d1f; /* Previne orice flash alb la scroll */
        }
        
        .dashboard-section {
            min-height: 100vh; /* Obligă fundalul să ocupe mereu minim tot ecranul */
            padding-top: 150px; /* Lasă spațiu perfect pentru navbar-ul lipit sus */
            padding-bottom: 50px;
        }

        /* 2. LOGICA DE SPA (Fade In/Out) */
        .admin-content-section {
            display: none; /* Ascunse implicit */
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
            width: 100%;
            z-index: 2;
        }

        .admin-content-section.active {
            display: block; /* Doar cea activă se vede */
            opacity: 1;
        }

        /* 3. DESIGN-UL TABELELOR (Tip Control Panel) */
        .admin-table-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            margin-bottom: 30px;
            min-height: 550px; /* <-- Secretul: face cardul masiv, ca un panou de control adevarat */
            display: flex;
            flex-direction: column;
        }

        .table-header-tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #b7c7a1;
            padding-bottom: 15px;
        }

        .table-header-tools h2 {
            font-family: 'Poppins', sans-serif;
            color: #0b3d1f;
            font-size: 28px;
            margin: 0;
        }

        .search-bar-admin {
            padding: 10px 15px;
            border-radius: 10px;
            border: 2px solid #b7c7a1;
            width: 300px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color 0.3s ease;
        }
        
        .search-bar-admin:focus {
            border-color: #0b3d1f;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Poppins', sans-serif;
        }

        .admin-table th {
            text-align: left;
            padding: 15px;
            border-bottom: 2px solid #0b3d1f;
            color: #0b3d1f;
            font-size: 14px;
            text-transform: uppercase;
        }

        .admin-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .admin-table tr:hover {
            background: #f4f7f1; /* Efect fin de hover pe rânduri */
        }

        /* 4. BADGE-URI ȘI BUTOANE PRO */
        .badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            display: inline-block;
        }
        .role-admin { background: #0b3d1f; color: white; }
        .role-worker { background: #b7c7a1; color: #0b3d1f; }

        .pro-btn {
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.2s;
            font-family: 'Poppins', sans-serif;
        }
        .pro-btn:hover {
            transform: translateY(-2px); /* Butoanele se ridică puțin la hover */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn-logs { background: #333; color: white; }
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
            <a href="#" class="nav-tab" data-target="users">User Control</a>
            <a href="#" class="nav-tab" data-target="reports">Reports DB</a>
            <a href="#" class="nav-tab" data-target="settings">System Logs</a>
            <a href="logout.php" class="button logout-button">Logout</a>
        </nav>
    </header>

    <section class="dashboard-section">
        <div class="overlay"></div>
        <div class="dashboard-container" style="flex-direction: column;">

            <div id="overview" class="admin-content-section active">
                <h1 style="color:white; text-align:center; margin-bottom:40px;">Command Center Overview</h1>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:25px;">
                    <div class="impact-card"><div class="card-header">Global Reports</div><h3 class="impact-number"><?= $totalReports ?></h3></div>
                    <div class="impact-card"><div class="card-header">System Users</div><h3 class="impact-number"><?= $totalUsers ?></h3></div>
                    <div class="impact-card" style="background:#e6f4ea;"><div class="card-header" style="background:#28a745;">Resolved Issues</div><h3 class="impact-number" style="color:#28a745;"><?= $resolved ?></h3></div>
                </div>
            </div>

            <div id="users" class="admin-content-section">
                <div class="admin-table-card">
                    <div class="table-header-tools">
                        <h2>Accounts Management</h2>
                        <input type="text" class="search-bar-admin" placeholder="Search user by email...">
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th>Status</th>
                                <th>Role</th>
                                <th>Last Login</th>
                                <th>Control</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Botezatu George</strong><br><small>george@admin.com</small></td>
                                <td><span style="color:green">● Active</span></td>
                                <td><span class="badge role-admin">Admin</span></td>
                                <td>2 mins ago</td>
                                <td><button class="pro-btn btn-logs">View Logs</button></td>
                            </tr>
                            <tr>
                                <td><strong>Andreea Simona</strong><br><small>andreea@worker.com</small></td>
                                <td><span style="color:green">● Active</span></td>
                                <td><span class="badge role-worker">Worker</span></td>
                                <td>Today, 04:20 AM</td>
                                <td><button class="pro-btn" style="background:#ffb3b3;">Revoke Access</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="reports" class="admin-content-section">
                <div class="admin-table-card">
                    <div class="table-header-tools">
                        <h2>Infrastructure Reports</h2>
                        <button class="pro-btn" style="background:#0b3d1f; color:white;">Export CSV</button>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Urgency</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#4491</td>
                                <td><strong>Metal Waste</strong></td>
                                <td>Iasi, Copou</td>
                                <td><span style="color:red; font-weight:800;">HIGH</span></td>
                                <td><button class="pro-btn" style="background:#2a6edb; color:white;">Assign Crew</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="settings" class="admin-content-section">
                <div class="admin-table-card" style="background:#1a1a1a; color:#00ff00; font-family: monospace;">
                    <h2>> System Internal Logs</h2>
                    <p style="margin-bottom:10px;">[06:12:01] Database backup completed successfully.</p>
                    <p style="margin-bottom:10px;">[06:10:45] New admin login detected: IP 192.168.1.5</p>
                    <p style="margin-bottom:10px;">[05:55:12] Auto-report: 5 issues moved to "Overdue" status.</p>
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

            // LOGICA DE SCHIMBARE SECȚIUNI (SPA)
            tabs.forEach(tab => {
                tab.addEventListener("click", function(e) {
                    if (this.getAttribute("href") === "logout.php") return;
                    e.preventDefault();

                    // Scoatem starea activă de peste tot
                    tabs.forEach(t => t.classList.remove("active"));
                    sections.forEach(s => s.classList.remove("active"));

                    // Activăm tab-ul și secțiunea curentă
                    this.classList.add("active");
                    const target = this.getAttribute("data-target");
                    const activeSection = document.getElementById(target);
                    
                    activeSection.classList.add("active");

                    // Închidem meniul pe mobil dacă e deschis
                    navLinks.classList.remove("active");
                    document.body.classList.remove("no-scroll");
                });
            });

            // Hamburger Menu Toggle
            hamburger.addEventListener("click", function() {
                navLinks.classList.toggle("active");
                document.body.classList.toggle("no-scroll");
            });
        });
    </script>
</body>
</html>