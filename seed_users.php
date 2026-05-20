/* Seed Users
Ran once to populate the database with some users, 
if we want more users we should change the loop limits (i til 13, we already have user13, so we need to change the limit)
 and the credentials. (password is user{i}000, so user4000 for user4, user5000 for user5 etc.)
*/
<?php
$conn = new mysqli("localhost", "root", "", "citywatch");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

for ($i = 4; $i <= 13; $i++) {
    $username = "user$i";
    $email = "user$i@citywatch.com";
    $password = password_hash("user{$i}000", PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status, created_at) VALUES (?, ?, ?, 'user', 'active', NOW())");
    $stmt->bind_param("sss", $username, $email, $password);
    $stmt->execute();
}

echo "Users inserted successfully!";
?>