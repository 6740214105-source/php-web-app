<?php
$serverName = 'Apache';
$badgeClass = 'bg-danger';
$btnClass   = 'btn-danger';
$table      = 'users';

$host = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$port = getenv('MYSQLPORT') ?: '3306';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: 'railway';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $pass, $db, (int)$port);
$connected = !$conn->connect_errno;
$rows = null;

if ($connected) {
    $conn->set_charset('utf8mb4');
    $conn->query("CREATE TABLE IF NOT EXISTS $table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        phone VARCHAR(20)
    ) CHARACTER SET utf8mb4");

    foreach (['email' => 'VARCHAR(100)', 'phone' => 'VARCHAR(20)'] as $col => $type) {
        $r = $conn->query("SHOW COLUMNS FROM $table LIKE '$col'");
        if ($r && $r->num_rows == 0) {
            $conn->query("ALTER TABLE $table ADD COLUMN $col $type");
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if ($name !== '') {
            $stmt = $conn->prepare("INSERT INTO $table (name, email, phone) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $name, $email, $phone);
            $stmt->execute();
        }
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }

    $rows = $conn->query("SELECT id, name, email, phone FROM $table ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $serverName ?> Web Server</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 900px;">
  <h1 class="mb-3"><span class="badge <?= $badgeClass ?>"><?= $serverName ?></span> Web Server</h1>

  <?php if ($connected): ?>
    <div class="alert alert-success">
      <strong>Database Status:</strong> Connected to MySQL Server successfully! (Host: <?= htmlspecialchars($host) ?>, Port: <?= htmlspecialchars($port) ?>, Table: <?= $table ?>)
    </div>
  <?php else: ?>
    <div class="alert alert-danger">
      <strong>Database Status:</strong> Connection failed: <?= htmlspecialchars($conn->connect_error) ?>
    </div>
  <?php endif; ?>

  <?php if ($connected): ?>
  <div class="card mb-4">
    <div class="card-header">เพิ่มข้อมูลผู้ใช้</div>
    <div class="card-body">
      <form method="post">
        <div class="mb-3">
          <label class="form-label">ชื่อ</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">เบอร์โทร</label>
          <input type="text" name="phone" class="form-control">
        </div>
        <button type="submit" class="btn <?= $btnClass ?>">บันทึกข้อมูล</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">ข้อมูลผู้ใช้</div>
    <div class="card-body">
      <table class="table table-striped">
        <thead>
          <tr><th>ID</th><th>ชื่อ</th><th>Email</th><th>เบอร์โทร</th></tr>
        </thead>
        <tbody>
          <?php while ($rows && ($row = $rows->fetch_assoc())): ?>
          <tr>
            <td><?= (int)$row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['phone'] ?? '') ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
