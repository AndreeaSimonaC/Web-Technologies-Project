<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';
require_once '../models/ReportModel.php';
    $conn = getDB();

    $result = $conn->query("SELECT COUNT(*) as total FROM reports");
    $row = $result->fetch_assoc();
    $totalReports = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM reports WHERE status='pending'");
    $row = $result->fetch_assoc();
    $totalPending = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM reports WHERE status='iprogress'");
    $row = $result->fetch_assoc();
    $totalInProgress = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM reports WHERE status='resolved'");
    $row = $result->fetch_assoc();
    $totalResolved = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM reports WHERE status='pending' AND created_at >= NOW() - INTERVAL 7 DAY");
    $row = $result->fetch_assoc();
    $thisWeekPending = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM reports WHERE status='progress' AND created_at >= NOW() - INTERVAL 7 DAY");
    $row = $result->fetch_assoc();
    $thisWeekInProgress = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM reports WHERE status='resolved' AND created_at >= NOW() - INTERVAL 7 DAY");
    $row = $result->fetch_assoc();
    $thisWeekResolved = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM reports WHERE created_at >= NOW() - INTERVAL 7 DAY");
    $row = $result->fetch_assoc();
    $thisWeekReports = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM reports WHERE status='pending' AND created_at BETWEEN NOW() - INTERVAL 14 DAY AND NOW() - INTERVAL 7 DAY");
    $row = $result->fetch_assoc();
    $lastWeekPending = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM reports WHERE status='progress' AND created_at BETWEEN NOW() - INTERVAL 14 DAY AND NOW() - INTERVAL 7 DAY");
    $row = $result->fetch_assoc();
    $lastWeekInProgress = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM reports WHERE status='resolved' AND created_at BETWEEN NOW() - INTERVAL 14 DAY AND NOW() - INTERVAL 7 DAY");
    $row = $result->fetch_assoc();
    $lastWeekResolved = $row['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM reports WHERE created_at BETWEEN NOW() - INTERVAL 14 DAY AND NOW() - INTERVAL 7 DAY");
    $row = $result->fetch_assoc();
    $lastWeekReports = $row['total'];

    function calcChange(int $thisWeek, int $lastWeek): float {
        if ($lastWeek === 0) { return $thisWeek > 0 ? 100 : 0; }
        return (($thisWeek - $lastWeek) / $lastWeek) * 100;
    }

    $pendingChange = calcChange($thisWeekPending, $lastWeekPending);
    $inProgressChange = calcChange($thisWeekInProgress, $lastWeekInProgress);
    $resolvedChange = calcChange($thisWeekResolved, $lastWeekResolved);
    $reportChange = calcChange($thisWeekReports, $lastWeekReports);

    $limit = 5;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $limit;

    $where = [];
    $params = [];
    $types = "";

    if (!empty($_GET['search'])) {
        $search = "%" . $_GET['search'] . "%";
        $where[] = "(c.name LIKE ? OR r.location LIKE ? OR u.username LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "sss";
    }

    if (!empty($_GET['status'])) {
        $where[] = "r.status = ?";
        $params[] = $_GET['status'];
        $types .= "s";
    }

    if (!empty($_GET['category'])) {
        $where[] = "r.category_id = ?";
        $params[] = $_GET['category'];
        $types .= "i";
    }

    if (!empty($_GET['date_from']) && !empty($_GET['date_to'])) {
        $where[] = "DATE(r.created_at) BETWEEN ? AND ?";
        $params[] = $_GET['date_from'];
        $params[] = $_GET['date_to'];
        $types .= "ss";
    }

    $countSql = "SELECT COUNT(*) as total FROM reports r LEFT JOIN categories c ON r.category_id = c.id LEFT JOIN users u ON r.user_id = u.id";

    if (!empty($where)) { $countSql .= " WHERE " . implode(" AND ", $where); }

    $countStmt = $conn->prepare($countSql);
    if (!empty($params)) { $countStmt->bind_param($types, ...$params);}

    $countStmt->execute();
    $totalReports = $countStmt->get_result()->fetch_assoc()['total'];

    $totalPages = max(1, ceil($totalReports / $limit));

    $sql = "SELECT  r.*, c.name AS category_name, u.username AS reported_by FROM reports r LEFT JOIN categories c ON r.category_id = c.id LEFT JOIN users u ON r.user_id = u.id";

    if (!empty($where)) { $sql .= " WHERE " . implode(" AND ", $where); }

    $sql .= " ORDER BY r.created_at DESC";
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

<?php $pageTitle = 'Reports DB - CityWatch'; include '../views/layout/head.php'; ?>

<body>
    <?php $navActive = 'reports'; include '../views/layout/header.php'; ?>
    <section class="dashboard-section">
        <div class="overlay"></div>
        <div class="dashboard-container admin-layout">
            <div id="users" class="admin-content-section active">
                <h1 class="admin-title">Reports Management</h1>
                <h2 class="admin-subtitle">View, search and manage all reports in the system.</h2>
                <div class="admin-overview-grid">
                    <div class="stat-card">
                        <div class="stat-header header-green">
                            <img src="../public/assets/adminReportsTotalIcon.png">
                            <span>Total Reports</span>
                        </div>
                        <div class="stat-body">
                            <h3><?= $totalReports ?></h3>
                            <span class="stat-change <?= $reportChange >= 0 ? 'positive' : 'negative' ?>">
                                <?= $reportChange >= 0 ? '↑' : '↓' ?> <?= abs(round($reportChange)) ?>%
                            </span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-red">
                            <img src="../public/assets/apPending.png">
                            <span>Pending</span>
                        </div>
                        <div class="stat-body">
                            <h3><?= $totalPending ?></h3>
                            <span class="stat-change <?= $pendingChange >= 0 ? 'positive' : 'negative' ?>">
                                <?= $pendingChange >= 0 ? '↑' : '↓' ?> <?= round(abs($pendingChange)) ?>%
                            </span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-orange">
                            <img src="../public/assets/apInProgress.png">
                            <span>In Progress</span>
                        </div>
                        <div class="stat-body">
                            <h3><?= $totalInProgress ?></h3>
                            <span class="stat-change <?= $inProgressChange >= 0 ? 'positive' : 'negative' ?>">
                                <?= $inProgressChange >= 0 ? '↑' : '↓' ?> <?= round(abs($inProgressChange)) ?>%
                            </span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-green-soft">
                            <img src="../public/assets/apResolved.png">
                            <span>Resolved</span>
                        </div>
                        <div class="stat-body">
                            <h3><?= $totalResolved ?></h3>
                            <span class="stat-change <?= $resolvedChange >= 0 ? 'positive' : 'negative' ?>">
                                <?= $resolvedChange >= 0 ? '↑' : '↓' ?> <?= round(abs($resolvedChange)) ?>%
                            </span>
                        </div>
                    </div>
                </div>

                <form method="GET">
                    <div class="admin-filters-bar">
                        <div class="filter-group search-group">
                            <label>Search</label>
                            <div class="input-with-icon">
                                <input type="text" name="search" placeholder="By category or location" value="<?= $_GET['search'] ?? '' ?>">
                                <span class="search-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label>Category</label>
                            <div class="select-wrapper">
                                <select name="category">
                                    <option value="">All categories</option>
                                    <option value="1">Plastic</option>
                                    <option value="2">Paper</option>
                                    <option value="3">Glass</option>
                                    <option value="4">Metal</option>
                                    <option value="5">Household</option>
                                </select>
                                <span class="dropdown-icon">↓</span>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label>Status</label>
                            <div class="select-wrapper">
                                <select name="status">
                                    <option value="">All</option>
                                    <option value="pending">Pending</option>
                                    <option value="progress">In Progress</option>
                                    <option value="resolved">Resolved</option>
                                </select>
                                <span class="dropdown-icon">↓</span>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label>Report Date</label>
                            <div class="input-with-icon date-range">
                                <input type="date" name="date_from">
                                <input type="date" name="date_to">
                            </div>
                        </div>
                        <div class="filter-group">
                            <button type="submit" class="filter-btn">Apply</button>
                        </div>
                        <a href="admin_reports.php" class="filter-reset" id="resetReportsFiltersBtn">Reset</a>
                    </div>
                </form>
                <div class="users-layout">
                    <div class="users-table">
                        <div class="admin-table-card">
                            <div class="table-header-tools"><h2>Reports Management</h2></div>
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Date</th>
                                        <th>Reported By</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="reportsTableBody">
                                    <?php while ($report = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?= $report['id'] ?></td>
                                            <td><span class="badge category-<?= $report['category_id'] ?>">
                                                    <?= htmlspecialchars($report['category_name']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($report['location']) ?></td>
                                            <td>
                                                <?= date("d M Y", strtotime($report['created_at'])) ?><br>
                                                <small><?= date("H:i A", strtotime($report['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($report['reported_by'] ?? 'Unknown') ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge status-<?= strtolower($report['status']) ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $report['status'])) ?>
                                                </span>
                                            </td>

                                            <td class="actions-cell">
                                                <button class="icon-btn view-btn"
                                                    data-id="<?= $report['id'] ?>"
                                                    data-category="<?= htmlspecialchars($report['category_name']) ?>"
                                                    data-location="<?= htmlspecialchars($report['location']) ?>"
                                                    data-date="<?= date("d M Y H:i", strtotime($report['created_at'])) ?>"
                                                    data-user="<?= htmlspecialchars($report['reported_by'] ?? 'Unknown') ?>"
                                                    data-status="<?= $report['status'] ?>"
                                                    data-description="<?= htmlspecialchars($report['description']) ?>">
                                                    <img src="../public/assets/adminReportsDetails.png">
                                                </button>
                                                <button class="icon-btn edit-btn"
                                                    data-id="<?= $report['id'] ?>">
                                                    <img src="../public/assets/adminReportsEdit.png">
                                                </button>
                                                <button class="icon-btn danger delete-btn"
                                                    data-id="<?= $report['id'] ?>">
                                                    <img src="../public/assets/adminReportsDelete.png">
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <div class="pagination-bar"><span class="pagination-info">Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $totalReports) ?> of <?= $totalReports ?> reports</span></div>
                            <?php $queryParams = $_GET; ?>
                            <div class="pagination-controls">
                                <?php if ($page > 1): ?>
                                    <?php $queryParams['page'] = $page - 1; ?>
                                    <a href="?<?= http_build_query($queryParams) ?>" class="page-arrow">←</a>
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
                    <div class="user-details-panel" id="reportDetails" style="display:none;">
                        <div class="details-header">
                            <h3>Report Details</h3>
                            <span class="close-btn" onclick="closeDetails()">✕</span>
                        </div>

                        <div class="details-body">
                            <img src="../public/assets/iconhousehold.png" class="avatar" id="detailIcon">
                            <h2 id="detailName">Select a report</h2>

                            <div class="details-info">
                                <p>
                                    <strong>Category:</strong>
                                    <span id="detailCategory"></span>
                                    <select id="editCategory" class="edit-field" style="display:none;">
                                        <option value="1">Plastic</option>
                                        <option value="2">Paper</option>
                                        <option value="3">Glass</option>
                                        <option value="4">Metal</option>
                                        <option value="5">Household</option>
                                    </select>
                                </p>
                                <p><strong>Location:</strong><span id="detailLocation"></span><input id="editLocation" class="edit-field" style="display:none;"></p>
                                <p><strong>Date:</strong> <span id="detailDate"></span></p>
                                <p><strong>Reported By:</strong> <span id="detailReportedBy"></span></p>
                                <p>
                                    <strong>Status:</strong>
                                    <span id="detailStatus"></span>
                                    <select id="editStatus" class="edit-field" style="display:none;">
                                        <option value="pending">Pending</option>
                                        <option value="progress">In Progress</option>
                                        <option value="resolved">Resolved</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </p>
                                <p><strong>Description:</strong><span id="detailDescription"></span><textarea id="editDescription" class="edit-field" style="display:none;"></textarea></p>
                            </div>
                            <div class="details-actions">
                                <button class="btn-edit">Edit Report</button>
                                <button class="btn-role" id="assignBtn">Send Team</button>
                                <button class="btn-danger" id="resolveBtn">Mark as Resolved</button>
                                <button id="saveBtn" class="btn-save">Save Changes</button> 
                                <button class="btn-archive" id="archiveBtn">Archive Report</button>
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
                <button id="confirmDelete" class="btn-danger">Yes</button>
                <button id="cancelDelete" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
    <script>
        const panel = document.getElementById("reportDetails");

        let selectedId = null;
        let currentAction = null;
        let currentStatus = null;

        function updateSaveButtonState() {
            const saveBtn = document.getElementById("saveBtn");

            if (currentStatus === "resolved" || currentStatus === "archived") {  saveBtn.disabled = true; } 
            else { saveBtn.disabled = false; }
        }
        document.querySelectorAll(".view-btn").forEach(btn => {
            btn.addEventListener("click", () => {

                selectedId = btn.dataset.id;
                currentStatus = btn.dataset.status;
                updateSaveButtonState();

                const icon = document.getElementById("detailIcon");
                const categoryMap = {
                    "plastic": "../public/assets/iconplastic.png",
                    "paper": "../public/assets/iconpaper.png",
                    "glass": "../public/assets/iconglass.png",
                    "metal": "../public/assets/iconmetal.png",
                    "household": "../public/assets/iconhousehold.png"
                };

                icon.src = categoryMap[btn.dataset.category.toLowerCase()] || "../public/assets/iconhousehold.png";

                document.getElementById("detailName").innerText = btn.dataset.category;
                document.getElementById("detailCategory").innerText = btn.dataset.category;
                document.getElementById("detailLocation").innerText = btn.dataset.location;
                document.getElementById("detailStatus").innerText = btn.dataset.status;
                document.getElementById("detailDescription").innerText = btn.dataset.description;
                document.getElementById("detailDate").innerText = btn.dataset.date;
                document.getElementById("detailReportedBy").innerText = btn.dataset.user;

                document.getElementById("editLocation").value = btn.dataset.location;
                document.getElementById("editDescription").value = btn.dataset.description;
                document.getElementById("editStatus").value = btn.dataset.status;

                const categoryIdMap = { "Plastic": "1", "Paper": "2", "Glass": "3", "Metal": "4", "Household": "5" };
                document.getElementById("editCategory").value = categoryIdMap[btn.dataset.category] || "1";
                document.querySelectorAll(".edit-field").forEach(el => el.style.display = "none");
                document.querySelectorAll("#detailCategory, #detailLocation, #detailStatus, #detailDescription").forEach(el => el.style.display = "inline");
                document.getElementById("saveBtn").style.display = "none";

                const editBtn = document.querySelector(".btn-edit");
                const sendBtn = document.getElementById("assignBtn");
                const resolveBtn = document.getElementById("resolveBtn");
                const archiveBtn = document.getElementById("archiveBtn");

                editBtn.disabled = false;
                sendBtn.style.display = "none";
                resolveBtn.style.display = "none";
                archiveBtn.style.display = "none";
                if (currentStatus === "resolved") { archiveBtn.style.display = "block"; }
                if (currentStatus === "resolved") { editBtn.disabled = true; }
                if (currentStatus === "archived") { editBtn.disabled = true; }
                if (currentStatus === "pending") { sendBtn.style.display = "block"; }
                if (currentStatus === "progress") { resolveBtn.style.display = "block"; }

                panel.style.display = "block";
            });
        });


        document.querySelector(".btn-edit").addEventListener("click", () => {
            enableEditMode();
            updateSaveButtonState();
        });

        document.querySelectorAll(".edit-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                const viewBtn = btn.closest("tr").querySelector(".view-btn");
                viewBtn.click();
                enableEditMode();
            });
        });
        document.getElementById("archiveBtn").addEventListener("click", () => {
            if (!selectedId) return;
            currentAction = "archive";
            document.getElementById("confirmText").textContent = "Archive this report?";
            document.getElementById("deleteOverlay").style.display = "flex";
        });

        function enableEditMode() {
            if (currentStatus === "resolved" || currentStatus === "archived") { return; }
            document.querySelectorAll(".edit-field").forEach(el => el.style.display = "block");
            document.querySelectorAll("#detailCategory, #detailLocation, #detailStatus, #detailDescription").forEach(el => el.style.display = "none");
            document.getElementById("saveBtn").style.display = "block";
        }

        document.getElementById("saveBtn").addEventListener("click", () => {
            const category = document.getElementById("editCategory").value;
            const reportLocation = document.getElementById("editLocation").value;
            const description = document.getElementById("editDescription").value;
            const status = document.getElementById("editStatus").value;

            fetch("../api/report_action.php", {
                    method: "POST",
                    headers: {"Content-Type": "application/x-www-form-urlencoded" },
                    body: `id=${selectedId}&action=edit&category=${category}&location=${reportLocation}&description=${description}&status=${status}`
                }).then(res => res.text()).then(() => location.reload());
        });

        document.querySelectorAll(".delete-btn").forEach(btn => {
            btn.addEventListener("click", function() {
                selectedId = this.dataset.id;
                currentAction = "delete";
                document.getElementById("confirmText").textContent = "Delete this report?";
                document.getElementById("deleteOverlay").style.display = "flex";
            });
        });

        document.getElementById("resolveBtn").addEventListener("click", () => {
            if (!selectedId) return;
            currentAction = "resolve";
            document.getElementById("confirmText").textContent = "Mark as resolved?";
            document.getElementById("deleteOverlay").style.display = "flex";
        });

        document.getElementById("assignBtn").addEventListener("click", () => {
            if (!selectedId) return;
            currentAction = "assign";
            document.getElementById("confirmText").textContent = "Send team?";
            document.getElementById("deleteOverlay").style.display = "flex";
        });

        document.getElementById("confirmDelete").onclick = () => {
            fetch("../api/report_action.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `id=${selectedId}&action=${currentAction}`
                }).then(() => location.reload());
        };

        document.getElementById("cancelDelete").onclick = () => { document.getElementById("deleteOverlay").style.display = "none"; };

        function closeDetails() {
            panel.style.display = "none";
            selectedId = null;
        }
        function applyFiltersAjax() {
            const status = document.querySelector("select[name='status']").value;
            const category = document.querySelector("select[name='category']").value;
            const search = document.querySelector("input[name='search']").value;
            const dateFrom = document.querySelector("input[name='date_from']").value;
            const dateTo = document.querySelector("input[name='date_to']").value;
            const params = new URLSearchParams({ status: status, category: category, search: search, date_from: dateFrom, date_to: dateTo });
            fetch("../api/filter_reports.php?" + params.toString())
                .then(res => res.text())
                .then(html => {
                    document.getElementById("reportsTableBody").innerHTML = html;
                });
        }
        document.querySelector("select[name='status']").addEventListener("change", applyFiltersAjax);
        document.querySelector("select[name='category']").addEventListener("change", applyFiltersAjax);
        document.querySelector("input[name='search']").addEventListener("keyup", applyFiltersAjax);
        document.querySelector("input[name='date_from']").addEventListener("change", applyFiltersAjax);
        document.querySelector("input[name='date_to']").addEventListener("change", applyFiltersAjax);
        document.getElementById("resetReportsFiltersBtn").addEventListener("click", function() {
            document.querySelector("input[name='search']").value = "";
            document.querySelector("select[name='category']").value = "";
            document.querySelector("select[name='status']").value = "";
            document.querySelector("input[name='date_from']").value = "";
            document.querySelector("input[name='date_to']").value = "";
            applyFiltersAjax();
    });
    </script>
    <?php include '../views/layout/footer.php'; ?>

</body>
</html>