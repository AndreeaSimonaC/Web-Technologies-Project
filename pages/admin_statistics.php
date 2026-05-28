<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';
require_once '../models/ReportModel.php';
$conn = getDB();

    $mode = $_GET['mode'] ?? 'daily';
    $counts = getReportCounts();
    $change = getWeeklyChange();
    $totalReports = $counts['total'];
    $pendingReports = $counts['pending'];
    $progressReports = $counts['progress'];
    $resolvedReports = $counts['resolved'];

    $categoryStats = getCategoryStats();
    $categoryPercentages = [];
    foreach ($categoryStats as $name => $count) {
        $categoryPercentages[$name] = $totalReports > 0 ? round(($count / $totalReports) * 100) : 0;
    }

    $colors = ["Paper" => "#3b82f6", "Plastic" => "#f59e0b", "Metal" => "#ef4444", "Glass" => "#10b981", "Household" => "#633b28"];
    $start = 0; $gradientParts = [];
    foreach ($categoryPercentages as $name => $percent) {
        $end = $start + $percent;
        $gradientParts[] = ($colors[$name] ?? "#ccc") . " {$start}% {$end}%";
        $start = $end;
    }
    $gradient = implode(", ", $gradientParts);

    $statusStats = getStatusStats();
    $statusColors = ["pending" => "#eb3e31", "progress" => "#e7a01b", "resolved" => "#1fc132", "archived" => "#888"];
    $statusStart = 0; $statusGradientParts = [];
    foreach ($statusStats as $status => $count) {
        $pct = $totalReports > 0 ? round(($count / $totalReports) * 100) : 0;
        $statusEnd = $statusStart + $pct;
        $statusGradientParts[] = ($statusColors[$status] ?? "#ccc") . " {$statusStart}% {$statusEnd}%";
        $statusStart = $statusEnd;
    }
    $statusGradient = implode(", ", $statusGradientParts);

    $chartResult = getReportsOverTime($mode);
    $chartData = $chartResult['data'];
    $labels = $chartResult['labels'];
    $max  = max($chartData) ?: 1;
    $width = 300; $height = 150;
    $stepX  = $width / max(count($chartData) - 1, 1);
    $points = [];
    foreach ($chartData as $i => $value) {
        $points[] = ($i * $stepX) . "," . ($height - ($value / $max * $height));
    }
    $pointsString = implode(" ", $points);
    $yLabels = [];
    for ($i = 5; $i >= 0; $i--) { $yLabels[] = round(($max / 5) * $i); }

    $locationStats = getLocationStats(5);
        $categoryColors = [
        "Paper"     => "#3b82f6",
        "Plastic"   => "#f59e0b",
        "Metal"     => "#ef4444",
        "Glass"     => "#10b981",
        "Household" => "#633b28"
    ];

$categoryLines = [];
$catNames = array_keys($categoryStats);

foreach ($catNames as $catName) {
    $catData = [];
    if ($mode === 'weekly') {
        $q = $conn->prepare("
            SELECT YEARWEEK(r.created_at, 1) as week, COUNT(*) as total
            FROM reports r
            JOIN categories c ON r.category_id = c.id
            WHERE c.name = ? AND r.created_at >= NOW() - INTERVAL 8 WEEK
            GROUP BY week
        ");
        $q->bind_param("s", $catName);
        $q->execute();
        $rows = $q->get_result();
        $map = [];
        while ($row = $rows->fetch_assoc()) { $map[$row['week']] = $row['total']; }
        for ($i = 7; $i >= 0; $i--) {
            $week = date("oW", strtotime("-$i week"));
            $catData[] = $map[$week] ?? 0;
        }
    } else {
        $q = $conn->prepare("
            SELECT DATE(r.created_at) as day, COUNT(*) as total
            FROM reports r
            JOIN categories c ON r.category_id = c.id
            WHERE c.name = ? AND r.created_at >= NOW() - INTERVAL 7 DAY
            GROUP BY DATE(r.created_at)
        ");
        $q->bind_param("s", $catName);
        $q->execute();
        $rows = $q->get_result();
        $map = [];
        while ($row = $rows->fetch_assoc()) { $map[$row['day']] = $row['total']; }
        for ($i = 6; $i >= 0; $i--) {
            $date = date("Y-m-d", strtotime("-$i days"));
            $catData[] = $map[$date] ?? 0;
        }
    }
    $categoryLines[$catName] = $catData;
}

$catMax = 1;
foreach ($categoryLines as $line) {
    $catMax = max($catMax, max($line));
}
    $locationMax= max(array_values($locationStats) ?: [1]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Statistics - CityWatch'; include '../views/layout/head.php'; ?>
</head>

<body>
<?php $navActive = 'statistics'; include '../views/layout/header.php'; ?>

<section class="dashboard-section">
    <div class="overlay"></div>
    <div class="dashboard-container admin-layout">
        <div class="admin-content-section active">

            <h1 class="admin-title">Statistics &amp; Analysis</h1>
            <h2 class="admin-subtitle">Explore detailed insights about waste reports and system activity.</h2>

            <div class="stats-summary-block" style="margin-bottom: 30px;">
                <div style="font-size: 18px; color: white; margin-bottom: 20px; font-family: 'Poppins', sans-serif;">
    Summary Reports for <span style="background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; border-radius: 12px; padding: 5px 15px; font-size: 14px; font-weight: 600; margin-left: 10px; display: inline-block;">This Week</span>
</div>
                <div class="admin-overview-grid">
                    <div class="stat-card">
                        <div class="stat-header header-green"><img src="../public/assets/adminReportsTotalIcon.png"><span>Total Reports</span></div>
                        <div class="stat-body">
                            <h3><?= $totalReports ?></h3>
                            <span class="stat-change <?= $change >= 0 ? 'positive' : 'negative' ?>">
                                <?= $change >= 0 ? '↑' : '↓' ?> <?= abs($change) ?>% from last week
                            </span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-red"><img src="../public/assets/apPending.png"><span>Pending</span></div>
                        <div class="stat-body"><h3><?= $pendingReports ?></h3><span class="stat-change positive">↑ 10% from last week</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-orange"><img src="../public/assets/apInProgress.png"><span>In progress</span></div>
                        <div class="stat-body"><h3><?= $progressReports ?></h3><span class="stat-change negative">↓ 10% from last week</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header header-green-soft"><img src="../public/assets/apResolved.png"><span>Resolved</span></div>
                        <div class="stat-body"><h3><?= $resolvedReports ?></h3><span class="stat-change positive">↑ 10% from last week</span></div>
                    </div>
                </div>
            </div>

            <div class="admin-stats-grid">
                <div class="admin-card">
                    <div class="card-header-row"><h3>Reports by category</h3></div>
                    <div class="category-chart-wrapper">
                        <div class="donut-chart" style="background: conic-gradient(<?= $gradient ?>)">
                            <div class="donut-center"><strong><?= $totalReports ?></strong><span>total</span></div>
                        </div>
                        <div class="category-legend">
                            <?php foreach ($categoryStats as $name => $count): ?>
                                <div class="legend-item">
                                    <img src="../public/assets/pin<?= strtolower($name) ?>.png">
                                    <span><?= $name ?></span>
                                    <span class="legend-value"><?= $categoryPercentages[$name] ?>% (<?= $count ?> reports)</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="card-header-row">
                        <h3>Reports Over Time</h3>
                        <select id="chartMode" class="small-select">
                            <option value="daily" <?= $mode === 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="weekly" <?= $mode === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                        </select>
                    </div>
                    <div class="line-chart-wrapper">
                        <div class="chart-axes">
                            <div class="y-axis">
                                <?php foreach ($yLabels as $y): ?><span><?= $y ?></span><?php endforeach; ?>
                            </div>
                            <div class="graph-area">
                                <svg viewBox="0 0 300 150" class="line-svg">
                                    <polyline fill="none" stroke="#1b641b" stroke-width="3" points="<?= $pointsString ?>"/>
                                    <?php foreach ($chartData as $i => $value):
                                        $cx = $i * $stepX;
                                        $cy = 150 - ($value / $max * 150);
                                    ?>
                                        <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="4" class="chart-dot"
                                            data-value="<?= $value ?>" data-label="<?= $labels[$i] ?>"/>
                                    <?php endforeach; ?>
                                </svg>
                                <div id="chartTooltip" class="chart-tooltip"></div>
                            </div>
                        </div>
                        <div class="x-axis">
                            <?php foreach ($labels as $label): ?><span><?= $label ?></span><?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="card-header-row"><h3>Reports by Status</h3></div>
                    <div class="category-chart-wrapper">
                        <div class="donut-chart" style="background: conic-gradient(<?= $statusGradient ?>)">
                            <div class="donut-center"><strong><?= $totalReports ?></strong><span>total</span></div>
                        </div>
                        <div class="category-legend">
                            <?php foreach ($statusStats as $status => $count): ?>
                                <div class="legend-item">
                                    <span class="status-dot" style="background:<?= $statusColors[$status] ?? '#ccc' ?>"></span>
                                    <span><?= ucfirst($status) ?></span>
                                    <span class="legend-value"><?= $totalReports > 0 ? round(($count/$totalReports)*100) : 0 ?>% (<?= $count ?> reports)</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                     <div class="card-header-row">
                           <h3>Category Trends</h3>
                          <select id="trendMode" class="small-select">
                        <option value="daily" <?= $mode === 'daily' ? 'selected' : '' ?>>Daily</option>
                        <option value="weekly" <?= $mode === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                      </select>
                 </div>
                  <div class="line-chart-wrapper">
                      <div class="chart-axes">
                             <div class="y-axis">
                                   <?php for ($i = $catMax; $i >= 0; $i -= max(1, round($catMax / 5))): ?>
                                      <span><?= $i ?></span>
                                      <?php endfor; ?>
                                   </div>
                           <div class="graph-area">
                            <svg viewBox="0 0 300 150" class="line-svg">
                           <?php foreach ($categoryLines as $catName => $catData):
                               $catPoints = [];
                               $catStepX = 300 / max(count($catData) - 1, 1);
                               foreach ($catData as $i => $val) {
                                $catPoints[] = ($i * $catStepX) . "," . (150 - ($val / $catMax * 150));
                           }
                               $catPointsStr = implode(" ", $catPoints);
                               $catColor = $categoryColors[$catName] ?? "#999";
                           ?>
                                <polyline fill="none" stroke="<?= $catColor ?>" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="<?= $catPointsStr ?>"/> 
                                 <?php endforeach; ?>
                              </svg>
                         </div>
                      </div>
                            <div class="x-axis">
                          <?php foreach ($labels as $label): ?><span><?= $label ?></span><?php endforeach; ?>
                               </div>
                         </div>
                        </div>
                 </div>

            <div class="admin-card stats-full-card" style="margin-bottom: 30px;">
                <div class="card-header-row"><h3>Reports By Location</h3></div>
                <div class="location-bar-chart">
                    <?php foreach ($locationStats as $name => $count): ?>
                        <div class="location-bar-row">
                            <span class="location-label"><?= htmlspecialchars($name) ?></span>
                            <div class="location-bar-track">
                                <div class="location-bar-fill" style="width: <?= round(($count / $locationMax) * 100) ?>%"></div>
                            </div>
                            <span class="location-count"><?= $count ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <form method="GET" action="../api/generate_report.php">
    <div class="admin-card admin-generate-card">
        <div class="generate-left">
            <h3>Generate Report</h3>
            <p>Export data and analysis</p>
            <div class="form-group">
                <label>Time Period</label>
                <select name="period">
                    <option value="all">All time</option>
                    <option value="week">This week</option>
                    <option value="month">This month</option>
                    <option value="custom">Custom range</option>
                </select>
            </div>
           <div class="form-group" id="customRangeGroup" style="display: none;">
                <label>From</label>
                <input type="date" name="date_from" class="date-picker" placeholder="Selectează data..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px;">
                <label style="margin-top: 8px;">To</label>
                <input type="date" name="date_to" class="date-picker" placeholder="Selectează data..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px;">
            </div>
            <div class="form-group">
                <label>Format</label>
                <div class="format-buttons">
                    <button type="button" class="format-btn active" data-format="csv">CSV</button>
                    <button type="button" class="format-btn" data-format="pdf">PDF</button>
                    <input type="hidden" name="format" id="selectedFormat" value="csv">
                </div>
            </div>
        </div>
        <div class="generate-middle">
            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="all">All</option>
                    <option value="Plastic">Plastic</option>
                    <option value="Glass">Glass</option>
                    <option value="Paper">Paper</option>
                    <option value="Metal">Metal</option>
                    <option value="Household">Household</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
        </div>
        <div class="generate-right">
            <button type="submit" class="generate-btn" style="padding: 12px 24px; background: #2e7d32; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; white-space: nowrap;">Generate Report</button>
        </div>
    </div>
</form>

        </div>
    </div>
</section>

<script>
    document.getElementById("hamburger-menu").addEventListener("click", function() {
        document.getElementById("nav-links").classList.toggle("active");
        document.body.classList.toggle("no-scroll");
    });

    const modeSelect = document.getElementById("chartMode");
    if (modeSelect) {
        modeSelect.addEventListener("change", function() {
            window.location.href = "?mode=" + this.value;
        });
    }
    const trendSelect = document.getElementById("trendMode");
    if (trendSelect) {
    trendSelect.addEventListener("change", function() {
        window.location.href = "?mode=" + this.value;
    });
    }

    const tooltip = document.getElementById("chartTooltip");
    document.querySelectorAll(".chart-dot").forEach(dot => {
        dot.addEventListener("mousemove", (e) => {
            tooltip.innerHTML = dot.dataset.label + ": " + dot.dataset.value + " reports";
            tooltip.style.display = "block";
            tooltip.style.left = (e.clientX + 10) + "px";
            tooltip.style.top = (e.clientY - 30) + "px";
            tooltip.style.opacity = 1;
        });
        dot.addEventListener("mouseleave", () => {
            tooltip.style.opacity = 0;
            tooltip.style.display = "none";
        });
    });
    const periodSelect = document.querySelector("select[name='period']");
if (periodSelect) {
    periodSelect.addEventListener("change", function() {
        document.getElementById("customRangeGroup").style.display = this.value === "custom" ? "block" : "none";
    });
    
}
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    const fromPicker = flatpickr("input[name='date_from']", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        onChange: function(selectedDates, dateStr, instance) {
            toPicker.set("minDate", dateStr);
        }
    });

    const toPicker = flatpickr("input[name='date_to']", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        onChange: function(selectedDates, dateStr, instance) {
            fromPicker.set("maxDate", dateStr);
        }
    });

    document.querySelectorAll('.format-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.format-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('selectedFormat').value = this.dataset.format;
        });
    });
</script>
<?php include '../views/layout/footer.php'; ?>
</body>
</html>