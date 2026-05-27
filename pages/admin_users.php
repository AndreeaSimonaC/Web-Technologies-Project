<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once '../config/db.php';
require_once '../models/UserModel.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$conn = getDB();

$userCounts    = getUserCounts();
$totalCitizens = $userCounts['users'];
$totalWorkers  = $userCounts['workers'];
$totalAdmins   = $userCounts['admins'];

$thisWeekUsers = $conn->query("SELECT COUNT(*) as t FROM users WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetch_assoc()['t'];
$lastWeekUsers = $conn->query("SELECT COUNT(*) as t FROM users WHERE created_at BETWEEN NOW() - INTERVAL 14 DAY AND NOW() - INTERVAL 7 DAY")->fetch_assoc()['t'];
$userChange    = $lastWeekUsers > 0 ? round((($thisWeekUsers - $lastWeekUsers) / $lastWeekUsers) * 100) : 0;

$limit  = 5;
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$where  = [];
$params = [];
$types  = "";

if (!empty($_GET['search'])) {
    $search    = "%" . $_GET['search'] . "%";
    $where[]   = "(u.username LIKE ? OR u.email LIKE ?)";
    $params[]  = $search;
    $params[]  = $search;
    $types    .= "ss";
}
if (!empty($_GET['role'])) {
    $where[]  = "u.role = ?";
    $params[] = $_GET['role'];
    $types   .= "s";
}
if (!empty($_GET['status'])) {
    $where[]  = "u.status = ?";
    $params[] = $_GET['status'];
    $types   .= "s";
}
if (!empty($_GET['date_from']) && !empty($_GET['date_to'])) {
    $where[]  = "DATE(u.created_at) BETWEEN ? AND ?";
    $params[] = $_GET['date_from'];
    $params[] = $_GET['date_to'];
    $types   .= "ss";
}

$whereClause = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM users u" . $whereClause);
if (!empty($params)) { $countStmt->bind_param($types, ...$params); }
$countStmt->execute();
$totalUsers  = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages  = ceil($totalUsers / $limit);

$sql    = "SELECT u.*, COUNT(r.id) as reports_count FROM users u LEFT JOIN reports r ON r.user_id = u.id" . $whereClause . " GROUP BY u.id LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types   .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<?php $pageTitle = 'User Control - CityWatch'; include '../views/layout/head.php'; ?>

<body>
   <?php $navActive = 'users'; include '../views/layout/header.php'; ?>
    <section class="dashboard-section">
        <div class="overlay"></div>
        <div class="dashboard-container admin-layout">
            <div id="users" class="admin-content-section active">
                <h1 class="admin-title">Accounts Management</h1>
                <h2 class="admin-subtitle">View, search and manage all users in the system.</h2>

                <div class="admin-overview-grid">
                    <div class="stat-card">
                        <div class="stat-header header-green">
                            <img src="../public/assets/adminUsersTotalIcon.png">
                            <span>Total Users</span>
                        </div>
                        <div class="stat-body">
                            <h3><?= $totalUsers ?></h3>
                            <span class="stat-change <?= $userChange >= 0 ? 'positive' : 'negative' ?>"><?= $userChange >= 0 ? '↑' : '↓' ?> <?= abs(round($userChange)) ?>%</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-red">
                            <img src="../public/assets/adminUsersCitizens.png">
                            <span>Citizens</span>
                        </div>
                        <div class="stat-body">
                            <h3><?= $totalCitizens ?></h3>
                            <span class="stat-change <?= $userChange >= 0 ? 'positive' : 'negative' ?>"><?= $userChange >= 0 ? '↑' : '↓' ?> <?= abs(round($userChange)) ?>%</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-orange">
                            <img src="../public/assets/adminUsersAuthorized.png">
                            <span>Authorized</span>
                        </div>
                        <div class="stat-body">
                            <h3><?= $totalWorkers ?></h3>
                            <span class="stat-change <?= $userChange >= 0 ? 'positive' : 'negative' ?>"><?= $userChange >= 0 ? '↑' : '↓' ?> <?= abs(round($userChange)) ?>%</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-green-soft">
                            <img src="../public/assets/adminUsersAdmins.png">
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
                                                    <img src="../public/assets/adminReportsDetails.png">
                                                </button>
                                                <button class="icon-btn edit-btn"
                                                    data-id="<?= $user['id'] ?>"
                                                    data-name="<?= htmlspecialchars($user['username']) ?>"
                                                    data-role="<?= $user['role'] ?>"
                                                    data-email="<?= htmlspecialchars($user['email']) ?>">
                                                    <img src="../public/assets/adminReportsEdit.png">
                                                </button>
                                                <button class="icon-btn danger delete-btn"
                                                    data-id="<?= $user['id'] ?>">
                                                    <img src="../public/assets/adminReportsDelete.png">
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <div class="pagination-bar">
                                <span class="pagination-info">Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $totalUsers) ?> of <?= $totalUsers ?> users</span>
                            </div>
                            <?php $queryParams = $_GET; ?>
                            <div class="pagination-controls">
                                <?php if ($page > 1): ?>
                                    <?php $queryParams['page'] = $page - 1; ?> <a href="?<?= http_build_query($queryParams) ?>" class="page-arrow">←</a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php $queryParams['page'] = $i; ?>
                                    <a href="?<?= http_build_query($queryParams) ?>" class="page-btn <?= $i == $page ? 'active' : '' ?>"> <?= $i ?> </a>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <?php $queryParams['page'] = $page + 1; ?>
                                    <a href="?<?= http_build_query($queryParams) ?>" class="page-arrow">→</a>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                    <div class="user-details-panel" id="userDetails">
                        <div class="details-header">
                            <h3>User Details</h3>
                            <span class="close-btn" onclick="closeDetails()">✕</span>
                        </div>

                        <div class="details-body">
                            <img src="../public/assets/iconuser.png" class="avatar">
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
                                <button class="btn-save" id="saveUserBtn"style="display:none;">Save Changes</button> 
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
                selectedUserId = btn.dataset.id;
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
                document.getElementById("editUserBtn").style.display = "block";
                document.getElementById("saveUserBtn").style.display = "none";
                document.querySelectorAll(".edit-field").forEach(el => {
                    el.style.display = "none";
                });

                document.querySelectorAll("#detailRole, #detailEmail").forEach(el => {
                    el.style.display = "inline";
                });
                            });
        });
        document.querySelectorAll(".edit-btn").forEach(btn => {
            btn.addEventListener("click", () => {

                const viewBtn = btn.closest("tr").querySelector(".view-btn");

                viewBtn.click();

                enableEditMode();
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
            document.getElementById("editUserBtn").style.display = "none";
        }
        document.getElementById("saveUserBtn").addEventListener("click", () => {
            const role = document.getElementById("editRole").value;
            const email = document.getElementById("editEmail").value;
            fetch("../api/user_action.php", {
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
                document.getElementById("confirmDelete").textContent = "Yes, Delete";
                document.getElementById("confirmText").textContent = "Are you sure you want to delete this user?";
                document.getElementById("deleteOverlay").style.display = "flex";
            });
        });
        document.getElementById("deactivateBtn").addEventListener("click", function() {
            if (!selectedUserId) return;
            currentAction = "delete";
            document.getElementById("confirmDelete").textContent = "Yes";
            document.getElementById("confirmText").textContent ="Delete this user permanently?";
            document.getElementById("deleteOverlay").style.display = "flex";
        });

        document.getElementById("cancelDelete").onclick = () => {
            document.getElementById("deleteOverlay").style.display = "none";
        };
        document.getElementById("confirmDelete").onclick = () => {
            fetch("../api/user_action.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `id=${selectedUserId}&action=${currentAction}`
                })
                .then(() => location.reload());
        };
    </script>
    <?php include '../views/layout/footer.php'; ?>

</body>

</html>