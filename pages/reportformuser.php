<?php
session_start();
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit(); }

require_once '../config/db.php';
$conn = getDB();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    $category_id = $_POST["category_id"];
    $description = trim($_POST["description"]);
    $location = trim($_POST["location"]);
    $lat = $_POST["lat"];
    $lng = $_POST["lng"];

    if (empty($category_id) || empty($description) || empty($location)) { echo "All fields are required."; exit(); }

    $stmt = $conn->prepare("INSERT INTO reports  (user_id, category_id, location, description, status, latitude, longitude, created_at) VALUES (?, ?, ?, ?, 'pending', ?, ?, NOW())");
    $stmt->bind_param("iissdd", $user_id, $category_id, $location, $description, $lat, $lng);

    $showSuccess = false;
    if ($stmt->execute()) { $showSuccess = true; } else { echo "Error inserting report."; }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityWatch Submit Report</title>
    <link rel="stylesheet" href="../public/main.css">
    <link rel="icon" type="image/x-icon" href="../public/assets/logosmall.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body>
    <header class="navbar">
        <div class="logo"><img src="../public/assets/logosmall.png" alt="CityWatchLogo"></div>
        
        <button class="hamburger" id="hamburger-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        
        <nav class="nav-links" id="nav-links">
            <a href="../pages/userdashboard.php">Home</a>
            <a href="#map-section">Map</a>
            <a href="#reports-section">My Reports</a>
        </nav>
        <div class="nav-right">
            <a href="../pages/logout.php" class="button logout-button"><img src="../public/assets/logouticon.png" alt="logout icon">Logout</a>
        </div>
    </header>
    <section class="report-section">
        <div class="report-card">
            <div class="report-form">
                <h2>Submit a new report</h2>
                <p class="subtitle">Choose the location on the map and complete the details.</p>
                <div class="map-hint">Click on the map to select the location.</div>
                <form method="POST" action="reportformuser.php" autocomplete="off">
                    <input type="hidden" name="lat" id="latInput">
                    <input type="hidden" name="lng" id="lngInput">
                    <label>Category</label> <?php $result = $conn->query("SELECT id, name FROM categories"); ?>
                    <select name="category_id" required>
                        <option value="">Select category</option>
                        <?php while ($row = $result->fetch_assoc()): ?> <option value="<?= $row['id'] ?>"> <?= htmlspecialchars($row['name']) ?> </option> <?php endwhile; ?>
                    </select>
                    <label>Description</label>
                    <textarea name="description" rows="4" placeholder="Describe the issue" required></textarea>
                    <label>Location</label>
                    <input type="text" name="location" id="locationInput" readonly placeholder="Click on map">
                    <button type="submit">Submit Report</button>
                </form>
            </div>
            <div class="report-map">
                <div class="map-overlay">Click on the map to select the location.</div>
                <div id="map"></div>
                <div class="report-map-footer">
                    <div class="report-legend">
                        <div class="report-legend-item"><img src="../public/assets/pinpaper.png" alt="paper pin"> Paper</div>
                        <div class="report-legend-item"><img src="../public/assets/pinplastic.png" alt="plastic pin"> Plastic</div>
                        <div class="report-legend-item"><img src="../public/assets/pinmetal.png" alt="metal pin"> Metal</div>
                        <div class="report-legend-item"><img src="../public/assets/pinglass.png" alt="glass pin"> Glass</div>
                        <div class="report-legend-item"><img src="../public/assets/pinhousehold.png" alt="household pin"> Household</div>
                    </div>
                </div>
                <button id="locateButton" class="locate-button">Locate me</button>
            </div>

        </div>
        <?php if (!empty($showSuccess)): ?>
            <div class="success-overlay" id="successOverlay">
                <div class="success-box">
                    <div class="close-btn" onclick="goToDashboard()">✕</div>
                    <h2>Thank you for submitting the report!</h2>
                    <p>Your request is being checked by our team and we're<br> sending the cleaning crew as soon as possible.</p>
                    <p class="redirect-text">Redirecting you to your dashboard...</p>
                </div>
            </div>
        <?php endif; ?>
    </section>
    <script src="../js/map.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let marker;
            map.on('click', function(e) {
                let lat = e.latlng.lat;
                let lng = e.latlng.lng;

                if (marker) { map.removeLayer(marker); }
                marker = L.marker([lat, lng]).addTo(map);

                document.getElementById("latInput").value = lat;
                document.getElementById("lngInput").value = lng;

                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`).then(res => res.json())
                .then(data => { document.getElementById("locationInput").value = data.display_name; })
                .catch(() => { document.getElementById("locationInput").value = lat + ", " + lng; });
            });
        });
        
        function goToDashboard() { window.location.href = "userdashboard.php"; }
        setTimeout(() => {
            const overlay = document.getElementById("successOverlay");
            if (overlay) {  window.location.href = "userdashboard.php"; } 
        }, 3000);
    </script>
</body>
</html>