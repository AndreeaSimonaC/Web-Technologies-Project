<?php
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
            <a href="#overview" class="nav-tab active" data-target="overview">Overview</a>
            <a href="#reports" class="nav-tab" data-target="reports">Reports DB</a>
            <a href="#users" class="nav-tab" data-target="users">User Control</a>
            <a href="#statistics" class="nav-tab" data-target="statistics">Statistics</a>
            <a href="#settings" class="nav-tab" data-target="settings">System Logs</a>
            <a href="logout.php" class="button logout-button">Logout</a>
        </nav>
    </header>

    <section class="dashboard-section">
        <div class="overlay"></div>
        <div class="dashboard-container admin-layout">

            <div id="overview" class="admin-content-section active">
                <h1 class="admin-title">Command Center Overview</h1>

                <div class="admin-overview-grid">
                    <div class="impact-card">
                        <div class="card-header">Global Reports</div>
                        <h3 class="impact-number"><?= $totalReports ?></h3>
                    </div>

                    <div class="impact-card">
                        <div class="card-header">System Users</div>
                        <h3 class="impact-number"><?= $totalUsers ?></h3>
                    </div>

                    <div class="impact-card impact-success">
                        <div class="card-header header-success">Resolved Issues</div>
                        <h3 class="impact-number text-success"><?= $resolved ?></h3>
                    </div>
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
                                <td><span class="status-active">● Active</span></td>
                                <td><span class="badge role-admin">Admin</span></td>
                                <td>2 mins ago</td>
                                <td><button class="pro-btn btn-logs">View Logs</button></td>
                            </tr>
                            <tr>
                                <td><strong>Andreea Simona</strong><br><small>andreea@worker.com</small></td>
                                <td><span class="status-active">● Active</span></td>
                                <td><span class="badge role-worker">Worker</span></td>
                                <td>Today, 04:20 AM</td>
                                <td><button class="pro-btn btn-danger-soft">Revoke Access</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="reports" class="admin-content-section">
                <div class="admin-table-card">
                    <div class="table-header-tools">
                        <h2>Infrastructure Reports</h2>
                        <button class="pro-btn btn-primary">Export CSV</button>
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
                                <td><span class="urgency-high">HIGH</span></td>
                                <td><button class="pro-btn btn-blue">Assign Crew</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="settings" class="admin-content-section">
                <div class="admin-table-card logs-card">
                    <h2>> System Internal Logs</h2>
                    <p>[06:12:01] Database backup completed successfully.</p>
                    <p>[06:10:45] New admin login detected: IP 192.168.1.5</p>
                    <p>[05:55:12] Auto-report: 5 issues moved to "Overdue" status.</p>
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

            tabs.forEach(tab => {
                tab.addEventListener("click", function(e) {
                    if (this.getAttribute("href") === "logout.php") return;
                    e.preventDefault();

                    tabs.forEach(t => t.classList.remove("active"));
                    sections.forEach(s => s.classList.remove("active"));

                    this.classList.add("active");
                    const target = this.getAttribute("data-target");
                    const activeSection = document.getElementById(target);
                    
                    activeSection.classList.add("active");

                    navLinks.classList.remove("active");
                    document.body.classList.remove("no-scroll");
                });
            });

            hamburger.addEventListener("click", function() {
                navLinks.classList.toggle("active");
                document.body.classList.toggle("no-scroll");
            });
        });
    </script>
</body>
</html>