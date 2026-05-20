/* Seed Reports 
Ran once to populate the database, if we want more reports we should change the coordinates so the pins 
don't overlap, also change the descriptions and maybe add more categories.
*/

<?php
$conn = new mysqli("localhost", "root", "", "citywatch");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error);}

$locations = [
    ["Copou", 47.1753, 27.5749],
    ["Tudor Vladimirescu", 47.1585, 27.6014],
    ["Podu Ros", 47.1580, 27.5830],
    ["Alexandru cel Bun", 47.1682, 27.5500],
    ["Nicolina", 47.1505, 27.5700],
    ["Dacia", 47.1800, 27.5400],
    ["Pacurari", 47.1750, 27.5600],
    ["CUG", 47.1400, 27.5800],
    ["Bularga", 47.1500, 27.6100],
    ["Cantemir", 47.1550, 27.5900]
];

$descriptions = [
    "Garbage not collected",
    "Broken glass on street",
    "Metal waste dumped",
    "Illegal dumping of waste",
    "Overflowing trash bin household waste",
    "Household waste left on sidewalk",
    "Metal waste left behind",
    "Household waste dumped",
    "Big garbage pile",
    "Plastic waste scattered"
];

$statuses = ["pending", "progress", "resolved"];
$userIds = [];
$res = $conn->query("SELECT id FROM users WHERE role='user'");

while ($row = $res->fetch_assoc()) { $userIds[] = $row['id']; }
for ($i = 0; $i < 10; $i++) {
    $loc = $locations[$i];
    $desc = $descriptions[$i];

    $category_id = rand(1, 5);
    $status = $statuses[array_rand($statuses)];

    $user_id = $userIds[array_rand($userIds)];

    $day = rand(1, date("t"));
    $date = date("Y-m-") . str_pad($day, 2, "0", STR_PAD_LEFT) . " " . rand(8, 20) . ":00:00";

    $stmt = $conn->prepare("INSERT INTO reports (user_id, category_id, location, latitude, longitude, description, status, created_at)  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssss", $user_id, $category_id, $loc[0], $loc[1], $loc[2], $desc, $status, $date);
    $stmt->execute();
}

echo "Reports inserted successfully!";
?>