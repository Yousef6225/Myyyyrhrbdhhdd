<?php
session_start();
// اگر لاگین نیست، هدایت به صفحه ورود
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$dataDir = __DIR__ . '/data';
$videos   = json_decode(file_get_contents("$dataDir/videos.json"), true);
$comments = file_exists("$dataDir/comments.json") ? json_decode(file_get_contents("$dataDir/comments.json"), true) : [];
$views    = file_exists("$dataDir/views.json")    ? json_decode(file_get_contents("$dataDir/views.json"), true)    : [];
$likes    = file_exists("$dataDir/likes.json")    ? json_decode(file_get_contents("$dataDir/likes.json"), true)    : [];
$poll     = json_decode(file_get_contents("$dataDir/poll.json"), true);

// رای به نظرسنجی
if (isset($_GET['vote'])) {
    $i = (int)$_GET['vote'];
    $poll['votes'][$i] = ($poll['votes'][$i] ?? 0) + 1;
    file_put_contents("$dataDir/poll.json", json_encode($poll));
    header('Location: index.php');
    exit;
}

// ارسال نظر
if (isset($_POST['comment'])) {
    $i   = (int)$_POST['vid'];
    $txt = trim(htmlspecialchars($_POST['comment']));
    if ($txt) {
        $comments[$i][] = ['user'=>$_SESSION['user'], 'text'=>$txt, 'time'=>date('Y-m-d H:i:s')];
        file_put_contents("$dataDir/comments.json", json_encode($comments));
    }
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
  <meta charset="UTF-8">
  <title>ویدیوها</title>
</head>
<body>
  <h2>خوش آمدید <?= htmlspecialchars($_SESSION['user']) ?></h2>
  <p><a href="logout.php">خروج</a></p>

  <h3>نظرسنجی: بهترین ویدیو</h3>
  <ul>
    <?php foreach ($videos as $i=>$v): ?>
      <li><a href="?vote=<?= $i ?>"><?= htmlspecialchars($v['title']) ?> (<?= $poll['votes'][$i] ?? 0 ?>)</a></li>
    <?php endforeach; ?>
  </ul>
  <hr>

  <?php foreach ($videos as $i=>$v): ?>
    <div>
      <h3><?= htmlspecialchars($v['title']) ?></h3>
      <video width="320" controls src="<?= htmlspecialchars($v['url']) ?>"></video>
      <p>بازدید:
        <?php
          $views[$i] = ($views[$i] ?? 0) + 1;
          echo $views[$i];
          file_put_contents("$dataDir/views.json", json_encode($views));
        ?>
      </p>
      <p>
        <a href="?like=<?= $i ?>">
          لایک (<?php
            $likes[$i] = $likes[$i] ?? 0;
            if (isset($_GET['like']) && (int)$_GET['like'] === $i) {
              $likes[$i]--;
            }
            echo $likes[$i];
            file_put_contents("$dataDir/likes.json", json_encode($likes));
          ?>)
        </a>
      </p>
      <h4>نظرات:</h4>
      <?php if (!empty($comments[$i])): foreach ($comments[$i] as $c): ?>
        <p><b><?= htmlspecialchars($c['user']) ?></b>: <?= htmlspecialchars($c['text']) ?> [<?= $c['time'] ?>]</p>
      <?php endforeach; else: ?>
        <p>هنوز نظری ثبت نشده.</p>
      <?php endif; ?>
      <form method="post">
        <input type="hidden" name="vid" value="<?= $i ?>">
        <textarea name="comment" required placeholder="نظر شما..."></textarea><br>
        <button type="submit">ارسال نظر</button>
      </form>
    </div>
    <hr>
  <?php endforeach; ?>
</body>
</html>