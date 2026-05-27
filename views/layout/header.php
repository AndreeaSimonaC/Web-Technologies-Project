<?php if (!isset($navActive)) $navActive = ''; ?>
<header class="navbar">
    <div class="logo"><img src="../public/assets/logosmall.png" alt="Logo"></div>
    <button class="hamburger" id="hamburger-menu">
        <span class="bar"></span><span class="bar"></span><span class="bar"></span>
    </button>
    <nav class="nav-links" id="nav-links">
        <a href="admindashboard.php" class="nav-tab <?= $navActive === 'overview' ? 'active' : '' ?>">Overview</a>
        <a href="admin_reports.php" class="nav-tab <?= $navActive === 'reports' ? 'active' : '' ?>">Reports DB</a>
        <a href="admin_users.php" class="nav-tab <?= $navActive === 'users' ? 'active' : '' ?>">User Control</a>
        <a href="admin_statistics.php" class="nav-tab <?= $navActive === 'statistics' ? 'active' : '' ?>">Statistics</a>
        <a href="admin_systemlogs.php" class="nav-tab <?= $navActive === 'logs' ? 'active' : '' ?>">System Logs</a>
        <a href="logout.php" class="button logout-button">Logout</a>
    </nav>
</header>