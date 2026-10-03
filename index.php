<?php
$host = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$port = getenv('MYSQLPORT') ?: '3306';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: 'railway';
$table = 'users';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $pass, $db, (int)$port);

if ($conn->connect_errno) {
    $status = "Database Status: Connection failed: " . htmlspecialchars($conn->connect_error);
} else {
    $conn->query("CREATE TABLE IF NOT EXISTS $table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $r = $conn->query("SELECT COUNT(*) AS c FROM $table");
    if ($r && $r->fetch_assoc()['c'] == 0) {
        $conn->query("INSERT INTO $table (name) VALUES ('Apache user')");
    }
    $status = "Database Status: Connected to MySQL Server successfully! (Host: $host, Port: $port, Table: $table)";
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Apache Web Server</title></head>
<body style="font-family: sans-serif; padding: 20px;">
  <h2>Apache Web Server</h2>
  <p><?= $status ?></p>
</body>
</html>
