<?php
$conn = new mysqli("localhost", "root", "", "citywatch");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT COUNT(*) as total FROM users");
$row = $result->fetch_assoc();
$totalUsers = $row['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='user'");
$row = $result->fetch_assoc();
$totalCitizens = $row['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='worker'");
$row = $result->fetch_assoc();
$totalWorkers = $row['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='admin'");
$row = $result->fetch_assoc();
$totalAdmins = $row['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE created_at >= NOW() - INTERVAL 7 DAY");
$row = $result->fetch_assoc();
$thisWeekUsers = $row['total'];

$result = $conn->query("SELECT COUNT(*) as total  FROM users  WHERE created_at BETWEEN NOW() - INTERVAL 14 DAY AND NOW() - INTERVAL 7 DAY");
$row = $result->fetch_assoc();
$lastWeekUsers = $row['total'];

$userChange = 0;

if ($lastWeekUsers > 0) {
    $difference = $thisWeekUsers - $lastWeekUsers;
    $userChange = ($difference / $lastWeekUsers) * 100;
}
$limit = 5;
$page = max(1, (int)($_GET['page'] ?? 1));

$offset = ($page - 1) * $limit;

$where = [];
$params = [];
$types = "";

if (!empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $where[] = "(u.username LIKE ? OR u.email LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $types .= "ss";
}
if (!empty($_GET['role'])) {
    $where[] = "u.role = ?";
    $params[] = $_GET['role'];
    $types .= "s";
}
if (!empty($_GET['status'])) {
    $where[] = "u.status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}
if (!empty($_GET['date_from']) && !empty($_GET['date_to'])) {
    $where[] = "DATE(u.created_at) BETWEEN ? AND ?";
    $params[] = $_GET['date_from'];
    $params[] = $_GET['date_to'];
    $types .= "ss";
}

$countSql = "SELECT COUNT(*) as total FROM users u";

if (!empty($where)) {
    $countSql .= " WHERE " . implode(" AND ", $where);
}

$countStmt = $conn->prepare($countSql);

if (!empty($params)) {
    $countTypes = $types;
    $countStmt->bind_param($countTypes, ...$params);
}

$countStmt->execute();
$totalUsers = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalUsers / $limit);

$sql = "SELECT u.*, COUNT(r.id) as reports_count FROM users u LEFT JOIN reports r ON r.user_id = u.id";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " GROUP BY u.id";
$sql .= " LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
                <h1 class="admin-title">Accounts Management</h1>
                <h2 class="admin-subtitle">View, search and manage all users in the system.</h2>

                <div class="admin-overview-grid">
                    <div class="stat-card">
                        <div class="stat-header header-green">
                            <img src="assets/adminUsersTotalIcon.png">
                            <span>Total Users</span>
                        </div>
                        <div class="stat-body">
                            <h3><?= $totalUsers ?></h3>
                            <span class="stat-change <?= $userChange >= 0 ? 'positive' : 'negative' ?>"><?= $userChange >= 0 ? '↑' : '↓' ?> <?= abs(round($userChange)) ?>%</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-red">
                            <img src="assets/adminUsersCitizens.png">
                            <span>Citizens</span>
                        </div>
                        <div class="stat-body">
                            <h3><?= $totalCitizens ?></h3>
                            <span class="stat-change <?= $userChange >= 0 ? 'positive' : 'negative' ?>"><?= $userChange >= 0 ? '↑' : '↓' ?> <?= abs(round($userChange)) ?>%</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-orange">
                            <img src="assets/adminUsersAuthorized.png">
                            <span>Authorized</span>
                        </div>
                        <div class="stat-body">
                            <h3><?= $totalWorkers ?></h3>
                            <span class="stat-change <?= $userChange >= 0 ? 'positive' : 'negative' ?>"><?= $userChange >= 0 ? '↑' : '↓' ?> <?= abs(round($userChange)) ?>%</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-green-soft">
                            <img src="assets/adminUsersAdmins.png">
                            <span>Admins</span>
                        </div>
                        <div class="stat-body">
                            <h3><?= $totalAdmins ?></h3>
                            <span class="stat-change <?= $userChange >= 0 ? 'positive' : 'negative' ?>"><?= $userChange >= 0 ? '↑' : '↓' ?> <?= abs(round($userChange)) ?>%</span>
                        </div>
                    </div>
                </div>

                <form method="GET">
                    <div class="admin-filters-bar">
                        <div class="filter-group search-group">
                            <label>Search</label>
                            <div class="input-with-icon">
                                <input type="text" name="search" placeholder="By username, email, name" value="<?= $_GET['search'] ?? '' ?>">
                                <span class="search-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label>Role</label>
                            <div class="select-wrapper">
                                <select name="role">
                                    <option value="">All roles</option>
                                    <option value="admin">Admin</option>
                                    <option value="worker">Worker</option>
                                    <option value="user">User</option>
                                </select>
                                <span class="dropdown-icon">↓</span>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label>Status</label>
                            <div class="select-wrapper">
                                <select name="status">
                                    <option value="">All</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <span class="dropdown-icon">↓</span>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label>Date Joined</label>
                            <div class="input-with-icon date-range">
                                <input type="date" name="date_from">
                                <input type="date" name="date_to">
                            </div>
                        </div>
                        <div class="filter-group">
                            <button type="submit" class="filter-btn">Apply</button>
                        </div>
                        <a href="admin_users.php" class="filter-reset">Reset</a>
                    </div>
                </form>
                <div class="users-layout">
                    <div class="users-table">
                        <div class="admin-table-card">
                            <div class="table-header-tools">
                                <h2>Accounts Management</h2>
                            </div>
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Date Joined</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?= $user['id'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($user['username']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge role-<?= strtolower($user['role']) ?>">
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <?= date("d M Y", strtotime($user['created_at'])) ?><br>
                                                <small><?= date("H:i A", strtotime($user['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($user['status'] === 'active'): ?><span class="status-active">● Active</span>
                                                <?php else: ?><span class="status-inactive">● Inactive</span><?php endif; ?>
                                            </td>
                                            <td class="actions-cell">
                                                <button class="icon-btn view-btn"
                                                    data-id="<?= $user['id'] ?>"
                                                    data-name="<?= htmlspecialchars($user['username']) ?>"
                                                    data-role="<?= $user['role'] ?>"
                                                    data-email="<?= htmlspecialchars($user['email']) ?>"
                                                    data-joined="<?= date("d M Y H:i", strtotime($user['created_at'])) ?>"
                                                    data-status="<?= $user['status'] ?>"
                                                    data-reports="<?= $user['reports_count'] ?>">
                                                    <img src="assets/adminReportsDetails.png">
                                                </button>
                                                <button class="icon-btn edit-btn"
                                                    data-id="<?= $user['id'] ?>"
                                                    data-name="<?= htmlspecialchars($user['username']) ?>"
                                                    data-role="<?= $user['role'] ?>"
                                                    data-email="<?= htmlspecialchars($user['email']) ?>">
                                                    <img src="assets/adminReportsEdit.png">
                                                </button>
                                                <button class="icon-btn danger delete-btn"
                                                    data-id="<?= $user['id'] ?>">
                                                    <img src="assets/adminReportsDelete.png">
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <div class="pagination-bar">
                                <span class="pagination-info">Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $totalUsers) ?> of <?= $totalUsers ?> users</span>
                            </div>
                            <div class="pagination-controls">
                                <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>" class="page-arrow">←</a><?php endif; ?>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?> <a href="?page=<?= $i ?>" class="page-btn <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a> <?php endfor; ?>
                                <?php if ($page < $totalPages): ?><a href="?page=<?= $page + 1 ?>" class="page-arrow">→</a><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="user-details-panel" id="userDetails">
                        <div class="details-header">
                            <h3>User Details</h3>
                            <span class="close-btn" onclick="closeDetails()">✕</span>
                        </div>

                        <div class="details-body">
                            <img src="assets/iconuser.png" class="avatar">
                            <h2 id="detailName">Select a user</h2>

                            <div class="details-info">
                                <p>
                                    <strong>Role:</strong>
                                    <span id="detailRole"></span>
                                    <select id="editRole" class="edit-field" style="display:none;">
                                        <option value="admin">Admin</option>
                                        <option value="worker">Worker</option>
                                        <option value="user">User</option>
                                    </select>
                                </p>
                                <p>
                                    <strong>Email:</strong>
                                    <span id="detailEmail"></span>
                                    <input id="editEmail" class="edit-field" style="display:none;">
                                </p>
                                <p><strong>Joined:</strong> <span id="detailJoined"></span></p>
                                <p><strong>Reports:</strong> <span id="detailReports"></span></p>
                                <p><strong>Status:</strong> <span id="detailStatus"></span></p>
                            </div>

                            <div class="details-actions">
                                <button class="btn-edit" id="editUserBtn">Edit User</button>
                                <button class="btn-save" id="saveUserBtn">Save Changes</button> 
                                <button class="btn-danger" id="deactivateBtn">Deactivate User</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div id="deleteOverlay" class="overlay-confirm" style="display:none;">
        <div class="overlay-box">
            <p id="confirmText">Are you sure?</p>
            <div class="overlay-actions">
                <button id="confirmDelete" class="btn-danger">Yes, Delete</button>
                <button id="cancelDelete" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
    <script>
        const panel = document.getElementById("userDetails");
        document.querySelectorAll(".view-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                document.getElementById("detailName").innerText = btn.dataset.name;
                document.getElementById("detailRole").innerText = btn.dataset.role;
                document.getElementById("detailEmail").innerText = btn.dataset.email;
                document.getElementById("detailJoined").innerText = btn.dataset.joined;
                document.getElementById("detailReports").innerText = btn.dataset.reports;
                document.getElementById("detailStatus").innerText = btn.dataset.status;
                document.getElementById("deactivateBtn").dataset.id = btn.closest("tr").querySelector(".delete-btn").dataset.id;
                document.getElementById("editRole").value = btn.dataset.role;
                document.getElementById("editEmail").value = btn.dataset.email;
                panel.style.display = "block";
            });
        });
        document.querySelectorAll(".edit-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                document.getElementById("detailName").innerText = btn.dataset.name;
                document.getElementById("editRole").value = btn.dataset.role;
                document.getElementById("editEmail").value = btn.dataset.email;
                selectedUserId = btn.dataset.id;
                enableEditMode();
                panel.style.display = "block";
            });
        });
        document.getElementById("editUserBtn").addEventListener("click", () => {
            enableEditMode();
        });

        function enableEditMode() {
            document.querySelectorAll(".edit-field").forEach(el => {
                el.style.display = "block";
            });
            document.querySelectorAll("#detailRole, #detailEmail").forEach(el => {
                el.style.display = "none";
            });
            document.getElementById("saveUserBtn").style.display = "block";
        }
        document.getElementById("saveUserBtn").addEventListener("click", () => {
            const role = document.getElementById("editRole").value;
            const email = document.getElementById("editEmail").value;
            fetch("user_action.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `id=${selectedUserId}&action=edit&role=${role}&email=${email}`
                })
                .then(() => location.reload());
        });

        function closeDetails() {
            panel.style.display = "none";
        }

        let selectedUserId = null;
        let currentAction = null;

        document.querySelectorAll(".delete-btn").forEach(btn => {
            btn.addEventListener("click", function() {
                selectedUserId = this.dataset.id;
                currentAction = "delete";

                document.getElementById("confirmText").textContent = "Are you sure you want to delete this user?";
                document.getElementById("deleteOverlay").style.display = "flex";
            });
        });
        document.getElementById("deactivateBtn").addEventListener("click", function() {
            selectedUserId = this.dataset.id;
            currentAction = "deactivate";
            document.getElementById("confirmText").textContent = "Deactivate this user?";
            document.getElementById("deleteOverlay").style.display = "flex";
        });

        document.getElementById("cancelDelete").onclick = () => {
            document.getElementById("deleteOverlay").style.display = "none";
        };
        document.getElementById("confirmDelete").onclick = () => {
            fetch("user_action.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `id=${selectedUserId}&action=${currentAction}`
                })
                .then(() => location.reload());
        };
    </script>
</body>

</html>