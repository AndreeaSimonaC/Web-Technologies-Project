<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityWatch</title>
    <link rel="stylesheet" href="main.css">
    <link rel="icon" type="image/x-icon" href="assets/logosmall.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<!--Landing page is divided into 5 sections: header, topsection, midsection, bottomsection and footer-->
<body>
    <!--Header section: logo and nav bar-->
    <header class="navbar">
        <div class="logo"><img src="assets/logosmall.png" alt="CityWatchLogo"></div>
        <nav class="nav-links">
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
                    <div class="legend-item"><img src="assets/pinpaper.png" alt="paper pin"> Paper</div>
                    <div class="legend-item"><img src="assets/pinplastic.png" alt="plastic pin"> Plastic</div>
                    <div class="legend-item"><img src="assets/pinmetal.png" alt="metal pin"> Metal</div>
                    <div class="legend-item"><img src="assets/pinglass.png" alt="glass pin"> Glass</div>
                    <div class="legend-item"><img src="assets/pinhousehold.png" alt="household pin"> Household</div>
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
            <div class="bottom-right"><img src="assets/picturelandingpage.png" alt="recycle bins"></div>
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

    <script src="map.js"></script>
</body>
</html>