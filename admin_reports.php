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
            <div id="reports" class="admin-content-section active">
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
        </div>
    </section>
</body>
</html>