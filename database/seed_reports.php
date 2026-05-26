<?php
require_once 'config/db.php';
$conn = getDB();

$locations = [
    ["Copou, Iași",              47.1753, 27.5749],
    ["Tudor Vladimirescu, Iași", 47.1585, 27.6014],
    ["Podu Roș, Iași",           47.1580, 27.5830],
    ["Alexandru cel Bun, Iași",  47.1682, 27.5500],
    ["Nicolina, Iași",           47.1505, 27.5700],
    ["Dacia, Iași",              47.1800, 27.5400],
    ["Păcurari, Iași",           47.1750, 27.5600],
    ["CUG, Iași",                47.1400, 27.5800],
    ["Bularga, Iași",            47.1500, 27.6100],
    ["Cantemir, Iași",           47.1550, 27.5900],
    ["Tătărași, Iași",           47.1560, 27.6150],
    ["Galata, Iași",             47.1490, 27.5650],
    ["Moara de Vânt, Iași",      47.1660, 27.5950],
    ["Bucium, Iași",             47.1245, 27.6200],
    ["Aviației, Iași",           47.1561, 27.6323],
];

$descriptions = [
    "Large pile of plastic bottles near the park",
    "Broken glass scattered on the sidewalk",
    "Metal waste dumped next to the bins",
    "Illegal dumping of household waste",
    "Overflowing trash bin, needs urgent attention",
    "Paper and cardboard left on the street",
    "Mixed waste dumped in the green area",
    "Construction debris left on the road",
    "Old furniture abandoned on the sidewalk",
    "Plastic bags scattered near the bus stop",
    "Glass bottles broken near the school",
    "Metal cans and wrappers on the ground",
    "Household waste bags left uncollected",
    "Large illegal dump site discovered",
    "Trash overflowing from public bin",
];

$statuses  = ["pending", "pending", "pending", "progress", "resolved", "archived"];
$userIds   = [];
$workerIds = [];

$res = $conn->query("SELECT id FROM users WHERE role='user'");
while ($row = $res->fetch_assoc()) { $userIds[] = $row['id']; }

$res = $conn->query("SELECT id FROM users WHERE role='worker'");
while ($row = $res->fetch_assoc()) { $workerIds[] = $row['id']; }

if (empty($userIds)) { die("No users found. Run seed_users.php first."); }

for ($i = 0; $i < 30; $i++) {
    $loc         = $locations[$i % count($locations)];
    $desc        = $descriptions[$i % count($descriptions)];
    $category_id = rand(1, 5);
    $status      = $statuses[array_rand($statuses)];
    $user_id     = $userIds[array_rand($userIds)];
    $worker_id   = ($status === 'progress' || $status === 'resolved' || $status === 'archived') && !empty($workerIds)
                   ? $workerIds[array_rand($workerIds)]
                   : null;

    $daysAgo  = rand(0, 29);
    $hour     = rand(7, 21);
    $minute   = rand(0, 59);
    $date     = date("Y-m-d H:i:s", strtotime("-$daysAgo days $hour:$minute:00"));

    $resolved_at = ($status === 'resolved' || $status === 'archived')
                   ? date("Y-m-d H:i:s", strtotime($date . " +" . rand(1, 5) . " hours"))
                   : null;

    $stmt = $conn->prepare("
        INSERT INTO reports (user_id, category_id, location, latitude, longitude, description, status, worker_id, created_at, resolved_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iissdssiss", $user_id, $category_id, $loc[0], $loc[1], $loc[2], $desc, $status, $worker_id, $date, $resolved_at);
    $stmt->execute();
}

echo "30 reports seeded successfully!";
?>