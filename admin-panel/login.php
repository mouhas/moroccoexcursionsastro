<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/auth.php';

admin_session_start();
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if (attempt_login($email, $password)) {
        header('Location: index.php');
        exit;
    }
    $error = 'Wrong email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Log in — Admin</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <h1>Morocco Excursions Admin</h1>
    <?php if ($error) : ?><div class="flash error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post">
      <?php echo csrf_field(); ?>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn" style="width:100%;justify-content:center">Log in</button>
    </form>
  </div>
</div>
</body>
</html>
