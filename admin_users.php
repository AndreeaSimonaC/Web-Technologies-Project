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
            <div id="users" class="admin-content-section active">
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
        </div>
    </section>
</body>

</html>