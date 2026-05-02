var map = L.map('map', { attributionControl: false }).setView([47.1585, 27.6014], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {}).addTo(map);
L.control.attribution({ position: 'bottomright' }).addAttribution('© OpenStreetMap').addTo(map);
document.getElementById("locateButton").addEventListener("click", function () {
    if (!navigator.geolocation) { alert("Geolocation is not available."); return; }
    navigator.geolocation.getCurrentPosition(
        function (position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            map.setView([lat, lng], 15);
            L.marker([lat, lng]).addTo(map).bindPopup("You are here.").openPopup();
        },
        function () { alert("Unable to retrieve your location."); }
    );
});

fetch("get_reports.php").then(response => response.json()).then(data => {
    data.forEach(report => {
        let iconUrl = "";
        switch (parseInt(report.category_id)) {
            case 1: iconUrl = "assets/pinplastic.png"; break;
            case 2: iconUrl = "assets/pinpaper.png"; break;
            case 3: iconUrl = "assets/pinglass.png"; break;
            case 4: iconUrl = "assets/pinmetal.png"; break;
            case 5: iconUrl = "assets/pinhousehold.png"; break;
        }

        let customIcon = L.icon({ iconUrl: iconUrl, iconSize: [30, 40], iconAnchor: [15, 40] });

        L.marker([report.latitude, report.longitude], { icon: customIcon }).addTo(map).on("click", function () {
            document.getElementById("ovCategory").textContent = report.category_name;
            document.getElementById("ovLocation").textContent = report.location;
            document.getElementById("ovUser").textContent = report.username;

            let date = new Date(report.created_at);
            document.getElementById("ovDate").textContent = date.toLocaleDateString();

            let statusEl = document.getElementById("ovStatus");
            statusEl.textContent = report.status;
            statusEl.style.color = report.status === "pending" ? "red" : report.status === "in progress" ? "orange" : "green";
            document.getElementById("reportOverlay").style.display = "flex";
        });
    });

}).catch(error => console.error("Error loading reports:", error));

function closeOverlay() { document.getElementById("reportOverlay").style.display = "none"; }
statusEl.style.color = report.status === "pending" ? "#e74c3c" : report.status === "in progress" ? "#f39c12" : "#27ae60";
