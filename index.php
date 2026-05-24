<?php
require_once 'config/db.php';
$conn = getDB();
$mapQuery = $conn->query("SELECT r.*, c.name AS category_name, u.username FROM reports r JOIN categories c ON r.category_id = c.id JOIN users u ON r.user_id = u.id WHERE r.status != 'archived'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityWatch</title>
    <link rel="stylesheet" href="public/main.css">
    <link rel="icon" type="image/x-icon" href="public/assets/logosmall.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

</head>

<!--Landing page is divided into 5 sections: header, topsection, midsection, bottomsection and footer-->
<body>
    <!--Header section: logo and nav bar-->
    <header class="navbar">
        <div class="logo"><img src="public/assets/logosmall.png" alt="CityWatchLogo"></div>
        
        <button class="hamburger" id="hamburger-menu">
        	<span class="bar"></span>
        	<span class="bar"></span>
        	<span class="bar"></span>
        </button>
        
        <nav class="nav-links" id="nav-links">
            <a href="#">Home</a>
            <a href="#">Contact</a>
            <div class="auth-buttons">
                <a href="login.php" class="button login-button">Login</a>
                <a href="register.php" class="button signup-button">Sign Up</a>
            </div>
        </nav>
    </header>

    <!--topsection: title, subtitle-->
    <section class="topsection">
        <div class="overlay"></div>
        <div class="topsection-content">
            <h1> City Watch<br>Keep your city clean.</h1>
            <p>Report waste. Track issues. <br> Make your community better.</p>
        </div>
    </section>

    <!--midsection: map with pins and locate me (placeholder for now)-->
    <section class="midsection">
        <div class="map-card">
            <div class="map-title"><h2>See what is happening in your city</h2></div>
            <div class="map-container">
                <div id="map"></div>
                <button id="locateButton" class="locate-button">Locate me</button>
            </div>
            <div class="map-footer">
                <div class="legend">
                    <div class="legend-item"><img src="public/assets/pinpaper.png" alt="paper pin"> Paper</div>
                    <div class="legend-item"><img src="public/assets/pinplastic.png" alt="plastic pin"> Plastic</div>
                    <div class="legend-item"><img src="public/assets/pinmetal.png" alt="metal pin"> Metal</div>
                    <div class="legend-item"><img src="public/assets/pinglass.png" alt="glass pin"> Glass</div>
                    <div class="legend-item"><img src="public/assets/pinhousehold.png" alt="household pin"> Household</div>
                </div>
            </div>
        </div>
    </section>
    <div class="report-overlay" id="reportOverlay" style="display:none;">
        <div class="report-overlay-box">
            <div class="close-btn" onclick="closeOverlay()">✕</div>
            <p><strong>Waste category:</strong> <span id="ovCategory"></span></p>
            <p><strong>Location:</strong> <span id="ovLocation"></span></p>
            <p><strong>Submitted by:</strong> <span id="ovUser"></span></p>
            <p><strong>Date:</strong> <span id="ovDate"></span></p>
            <p><strong>Status:</strong> <span id="ovStatus"></span></p>
        </div>
    </div>
    <!--bottomsection: cta button, text, title, image-->
    <section class="bottomsection">
        <div class="overlay"></div>
        <div class="bottom-content">
            <div class="bottom-left">
                <a href="register.php" class="cta-button">Create an account</a>
                <p class="cta-text">Report waste: Easily report waste-issues areas by location.<br>Track issues: See reports on the map in real time.<br>Clean city: Help improve your community</p>
            </div>
            <div class="bottom-right"><img src="public/assets/picturelandingpage.png" alt="recycle bins"></div>
        </div>
        <div class="bottom-checklist">
            <span>✔ Create an account</span>
            <span>✔ Report a problem</span>
            <span>✔ Track progress</span>
        </div>
        <div class="bottom-checklist second"><span>✔ We solve the issue!</span></div>
        <h2 class="bottom-title">Help us make the city clean.</h2>
    </section>

    <!--footer contact-->
    <footer>
        <p>CityWatch 2026</p>
        <p>Contact us at citywatch@cleaning.com</p>
    </footer>

    <!--script for map and location-->
    <!-- TO BE UPDATED WITH CURRENT REPORTS once we implement them later on-->

    <script>
        //map var store the Leaflet map object
        //L is the Leaflet library
        //creating the map with the map function, with the html element id 'map'
        //setview with latitude, longitude coords and zoom level
        var map = L.map('map', {attributionControl: false}).setView([47.1585, 27.6014], 13);
        //load the map with the template zoom/lat/long
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {}).addTo(map);
        //attribution, needed for copywrite
        L.control.attribution({ position: 'bottomright' }).addAttribution('© OpenStreetMap').addTo(map);
        <?php while ($row = $mapQuery->fetch_assoc()): ?>
            <?php if ($row['latitude'] && $row['longitude']): ?>
                <?php
                $icon = "";
                switch ($row['category_id']) {
                    case 1:
                        $icon = "public/assets/pinplastic.png";
                        break;
                    case 2:
                        $icon = "public/assets/pinpaper.png";
                        break;
                    case 3:
                        $icon = "public/assets/pinglass.png";
                        break;
                    case 4:
                        $icon = "public/assets/pinmetal.png";
                        break;
                    case 5:
                        $icon = "public/assets/pinhousehold.png";
                        break;
                }
                ?>
                var icon = L.icon({ iconUrl: "<?= $icon ?>", iconSize: [35, 35] });
                var marker = L.marker([<?= $row['latitude'] ?>, <?= $row['longitude'] ?>], { icon: icon }).addTo(map);
                marker.on("click", function() {
                    document.getElementById("reportOverlay").style.display = "flex";
                    document.getElementById("ovCategory").textContent = "<?= htmlspecialchars($row['category_name']) ?>";
                    document.getElementById("ovLocation").textContent = "<?= htmlspecialchars($row['location']) ?>";
                    document.getElementById("ovUser").textContent = "<?= htmlspecialchars($row['username']) ?>";
                    document.getElementById("ovDate").textContent = "<?= $row['created_at'] ?>";
                    document.getElementById("ovStatus").textContent = "<?= $row['status'] ?>";
                });
            <?php endif; ?>
        <?php endwhile; ?>
        function closeOverlay() { document.getElementById("reportOverlay").style.display = "none"; }
        //locate me button on click uses geolocation API for location
        document.getElementById("locateButton").addEventListener("click", function () {
            if (!navigator.geolocation) { alert("Geolocation is not available."); return; }
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    map.setView([lat, lng], 15);
                    //marker the pin that shows the location
                    L.marker([lat, lng]).addTo(map).bindPopup("You are here.").openPopup();},
                function () {alert("Unable to retrieve your location.");}
            );
        });
    </script>
    <script>
    	document.addEventListener("DOMContentLoaded", function(){
    		const hamburger = document.getElementById("hamburger-menu");
    		const navLinks = document.getElementById("nav-links");
    		
    		hamburger.addEventListener("click",function(){
    			navLinks.classList.toggle("active");
            document.body.classList.toggle("no-scroll");
    			});
    		});
    </script>
</body>
</html>