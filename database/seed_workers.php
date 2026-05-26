/* Seed Workers
Ran once to populate the database with some workers,
if we want more workers we should change the loop limits (i til 13, we already have worker13, so we need to change the limit)
 and the credentials. (password is worker{i}000, so worker6000 for worker6, worker7000 for worker7 etc.)
*/
<?php
$conn = new mysqli("localhost", "root", "", "citywatch");

if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

for ($i = 6; $i <= 15; $i++) {
    $username = "worker$i";
    $email = "worker$i@citywatch.com";
    $password = password_hash("worker{$i}000", PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status, created_at) VALUES (?, ?, ?, 'worker', 'active', NOW())");
    $stmt->bind_param("sss", $username, $email, $password);
    $stmt->execute();
}

echo "Workers inserted successfully!";

?>