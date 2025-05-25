<?php
session_start();
// تنظیم نام‌کاربری/رمز
$users = [
    'admin' => 'kalepache',
    'user'  => 'sosfelfel123'
];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    if (isset($users[$u]) && $users[$u] === $p) {
        $_SESSION['user'] = $u;
        // ادمین می‌رود admin.php و کاربر معمولی می‌رود index.php
        header('Location: ' . ($u === 'admin' ? 'admin.php' : 'index.php'));
        exit;
    } else {
        $error = 'نام کاربری یا رمز اشتباه است.';
    }
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
  <meta charset="UTF-8">
  <title>ورود</title>
</head>
<body>
  <h2>ورود</h2>
  <?php if ($error): ?>
    <p style="color:red;"><?= $error ?></p>
  <?php endif; ?>
  <form method="post">
    نام کاربری:<br>
    <input type="text" name="username" required><br><br>
    رمز عبور:<br>
    <input type="password" name="password" required><br><br>
    <button type="submit">ورود</button>
  </form>
</body>
</html>