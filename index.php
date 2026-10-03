<?php
$serverName = 'Apache';
$table      = 'users';
$c1         = '#6d28d9';
$c2         = '#c084fc';

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
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root { --c1: <?= $c1 ?>; --c2: <?= $c2 ?>; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Prompt', 'Segoe UI', sans-serif; background: #f8fafc; color: #1f2937; }
    .hero { background: linear-gradient(135deg, var(--c1), var(--c2)); color: #fff; text-align: center; padding: 38px 20px 80px; }
    .hero h1 { margin: 0; font-size: 2.2rem; font-weight: 600; letter-spacing: .5px; }
    .hero p { margin: 6px 0 0; opacity: .9; }
    .wrap { max-width: 880px; margin: -50px auto 40px; padding: 0 16px; }
    .status { background: #fff; border-left: 6px solid #22c55e; border-radius: 14px; padding: 14px 18px; box-shadow: 0 6px 20px rgba(0,0,0,.08); margin-bottom: 20px; }
    .status.err { border-left-color: #ef4444; }
    .card { background: #fff; border-radius: 18px; box-shadow: 0 6px 20px rgba(0,0,0,.08); padding: 24px; margin-bottom: 20px; }
    .card h2 { margin: 0 0 16px; font-size: 1.1rem; color: var(--c1); }
    .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    label { display: block; font-size: .9rem; margin-bottom: 6px; color: #4b5563; }
    input { width: 100%; padding: 11px 14px; border: 2px solid #e5e7eb; border-radius: 12px; font: inherit; }
    input:focus { outline: none; border-color: var(--c1); }
    button { margin-top: 16px; background: linear-gradient(135deg, var(--c1), var(--c2)); color: #fff; border: 0; padding: 12px 30px; border-radius: 999px; font: inherit; font-weight: 600; cursor: pointer; }
    button:hover { opacity: .9; }
    .scroll { overflow-x: auto; }
    table { width: 100%; border-collapse: separate; border-spacing: 0; }
    th { background: var(--c1); color: #fff; text-align: left; padding: 11px 14px; }
    th:first-child { border-radius: 12px 0 0 12px; }
    th:last-child { border-radius: 0 12px 12px 0; }
    td { padding: 11px 14px; border-bottom: 1px solid #eef0f3; }
    tr:nth-child(even) td { background: #f9fafb; }
    .empty { text-align: center; color: #9ca3af; padding: 20px; }
    @media (max-width: 700px) { .grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <div class="hero">
    <h1><?= $serverName ?> Web Server</h1>
    <p>ระบบจัดการข้อมูลผู้ใช้</p>
  </div>

  <div class="wrap">
    <?php if ($connected): ?>
      <div class="status">
        <strong>Database Status:</strong> Connected to MySQL Server successfully! (Host: <?= htmlspecialchars($host) ?>, Port: <?= htmlspecialchars($port) ?>, Table: <?= $table ?>)
      </div>
    <?php else: ?>
      <div class="status err">
        <strong>Database Status:</strong> Connection failed: <?= htmlspecialchars($conn->connect_error) ?>
      </div>
    <?php endif; ?>

    <?php if ($connected): ?>
    <div class="card">
      <h2>เพิ่มข้อมูลผู้ใช้</h2>
      <form method="post">
        <div class="grid">
          <div>
            <label>ชื่อ</label>
            <input type="text" name="name" required>
          </div>
          <div>
            <label>Email</label>
            <input type="email" name="email">
          </div>
          <div>
            <label>เบอร์โทร</label>
            <input type="text" name="phone">
          </div>
        </div>
        <button type="submit">บันทึกข้อมูล</button>
      </form>
    </div>

    <div class="card">
      <h2>ข้อมูลผู้ใช้</h2>
      <div class="scroll">
        <table>
          <thead>
            <tr><th>ID</th><th>ชื่อ</th><th>Email</th><th>เบอร์โทร</th></tr>
          </thead>
          <tbody>
            <?php if ($rows && $rows->num_rows > 0): ?>
              <?php while ($row = $rows->fetch_assoc()): ?>
              <tr>
                <td><?= (int)$row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['phone'] ?? '') ?></td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="4" class="empty">ยังไม่มีข้อมูล</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>
</body>
</html>
