<?php
session_start();
// فقط ادمین اجازه دارد
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// مسیر پوشه data و فایل‌ها
$dataDir    = __DIR__ . '/data';
$videosFile = "$dataDir/videos.json";
$pollFile   = "$dataDir/poll.json";

// ساخت پوشه و فایل در صورت عدم وجود
if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);
if (!file_exists($videosFile)) file_put_contents($videosFile, '[]');
if (!file_exists($pollFile))   file_put_contents($pollFile, json_encode(['votes'=>[]]));

$videos = json_decode(file_get_contents($videosFile), true);
$poll   = json_decode(file_get_contents($pollFile), true);

$msg = '';
$error = '';

// درخواست آپلود یا ریست نظرسنجی
if (isset($_POST['upload'])) {
    // آپلود ویدیو
    if ($_FILES['video']['error'] === 0) {
        $dir = 'videos/';
        if (!is_dir($dir)) mkdir($dir);
        $name = basename($_FILES['video']['name']);
        $path = $dir . $name;
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (in_array($ext, ['mp4','webm','ogg'])) {
            move_uploaded_file($_FILES['video']['tmp_name'], $path);
            $videos[] = ['id'=>time(), 'title'=>trim($_POST['title']), 'url'=>$path];
            file_put_contents($videosFile, json_encode($videos, JSON_PRETTY_PRINT));
            $msg = 'آپلود موفق.';
        } else {
            $error = 'فرمت نامعتبر.';
        }
    }
} elseif (isset($_POST['reset_poll'])) {
    // ریست نظرسنجی
    $poll = ['votes'=>[]];
    file_put_contents($pollFile, json_encode($poll));
    $msg = 'نظرسنجی ریست شد.';
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
  <meta charset="UTF-8">
  <title>پنل مدیریت</title>
</head>
<body>
  <h2>پنل ادمین</h2>
  <p><a href="logout.php">خروج</a></p>

  <?php if ($msg):   echo "<p style='color:green;'>$msg</p>"; ?>
  <?php elseif($error): echo "<p style='color:red;'>$error</p>"; endif; ?>

  <form method="post" enctype="multipart/form-data">
    عنوان:<br>
    <input type="text" name="title" required><br><br>
    فایل ویدیو:<br>
    <input type="file" name="video" accept="video/*" required><br><br>
    <button name="upload">آپلود</button>
  </form>

  <hr>
  <h3>فهرست ویدیوها:</h3>
  <ul>
    <?php foreach ($videos as $v): ?>
      <li><?= htmlspecialchars($v['title']) ?></li>
    <?php endforeach; ?>
  </ul>

  <hr>
  <h3>نظرسنجی:</h3>
  <form method="post">
    <button name="reset_poll">ریست نظرسنجی</button>
  </form>
  <p>کل آراء: <?= array_sum($poll['votes']) ?></p>
</body>
</html>