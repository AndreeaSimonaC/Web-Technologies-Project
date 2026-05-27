var map = L.map('map', { attributionControl: false }).setView([47.1585, 27.6014], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {}).addTo(map);
L.control.attribution({ position: 'bottomright' }).addAttribution('© OpenStreetMap').addTo(map);

document.getElementById("locateButton").addEventListener("click", function () {
    if (!navigator.geolocation) { alert("Geolocation is not available."); return; }
    navigator.geolocation.getCurrentPosition(
        function (position) {
            map.setView([position.coords.latitude, position.coords.longitude], 15);
            L.marker([position.coords.latitude, position.coords.longitude]).addTo(map).bindPopup("You are here.").openPopup();
        },
        function () { alert("Unable to retrieve your location."); }
    );
});

fetch("../api/get_reports.php").then(response => response.json()).then(data => {
    data.forEach(report => {
        if (!report.latitude || !report.longitude) return;

        let iconUrl = "";
        switch (parseInt(report.category_id)) {
            case 1: iconUrl = "../public/assets/pinplastic.png"; break;
            case 2: iconUrl = "../public/assets/pinpaper.png"; break;
            case 3: iconUrl = "../public/assets/pinglass.png"; break;
            case 4: iconUrl = "../public/assets/pinmetal.png"; break;
            case 5: iconUrl = "../public/assets/pinhousehold.png"; break;
            default: return;
        }

        let customIcon = L.icon({
            iconUrl: iconUrl,
            iconSize: [30, 40],
            iconAnchor: [15, 40],
            className: report.status === "archived" ? "archived-pin" : ""
        });

        L.marker([report.latitude, report.longitude], {
            icon: customIcon,
            opacity: report.status === "archived" ? 0.5 : 1
        }).addTo(map).on("click", function () {
            document.getElementById("ovCategory").textContent = report.category_name;
            document.getElementById("ovLocation").textContent = report.location;
            document.getElementById("ovUser").textContent = report.username;
            document.getElementById("ovDate").textContent = new Date(report.created_at).toLocaleDateString();

            let statusEl = document.getElementById("ovStatus");
            statusEl.textContent = report.status;
            statusEl.style.color = report.status === "pending" ? "#e74c3c"
                                 : report.status === "progress" ? "#f39c12"
                                 : "#27ae60";

            document.getElementById("reportOverlay").style.display = "flex";
        });
    });
}).catch(error => console.error("Error loading reports:", error));

function closeOverlay() { document.getElementById("reportOverlay").style.display = "none"; }