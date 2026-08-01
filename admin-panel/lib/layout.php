<?php
function render_header($title, $active = '') {
    $nav = [
        'dashboard' => ['index.php', 'Dashboard'],
        'content' => ['content/list.php', 'Site Content'],
        'settings' => ['settings/edit.php', 'Settings'],
        'bookings' => ['bookings/list.php', 'Bookings'],
        'messages' => ['messages/list.php', 'Messages'],
        'media' => ['media/upload.php', 'Media'],
    ];
    $base = base_path();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo htmlspecialchars($title); ?> — Admin</title>
<link rel="stylesheet" href="<?php echo $base; ?>/assets/style.css">
</head>
<body>
<div class="topbar">
  <a class="brand" href="<?php echo $base; ?>/index.php">Morocco Excursions Admin</a>
  <nav>
    <?php foreach ($nav as $key => $item) : ?>
      <a href="<?php echo $base . '/' . $item[0]; ?>" class="<?php echo $active === $key ? 'active' : ''; ?>"><?php echo $item[1]; ?></a>
    <?php endforeach; ?>
    <a href="<?php echo defined('SITE_URL') ? SITE_URL : '/'; ?>" target="_blank">View site ↗</a>
    <a href="<?php echo $base; ?>/logout.php">Log out</a>
  </nav>
</div>
<div class="wrap">
<?php
}

function render_footer() {
    ?>
</div>
</body>
</html>
<?php
}

function flash_set($type, $message) {
    admin_session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_render() {
    admin_session_start();
    if (empty($_SESSION['flash'])) return;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    echo '<div class="flash ' . htmlspecialchars($f['type']) . '">' . htmlspecialchars($f['message']) . '</div>';
}

// Root-relative base path this admin panel is deployed under (works whether
// it's at /admin-panel, a subdomain's document root, or anywhere else) —
// computed from the currently running script, not hardcoded.
function base_path() {
    $script = $_SERVER['SCRIPT_NAME'];
    // Strip the current file's own path segment (e.g. /content/list.php ->
    // walk up one level per extra directory depth under admin-panel root).
    $dir = str_replace('\\', '/', dirname($script));
    // Pages one level deep (content/, settings/, bookings/, messages/,
    // media/) need to go up one more level to reach admin-panel root.
    if (preg_match('#/(content|settings|bookings|messages|media)$#', $dir)) {
        $dir = dirname($dir);
    }
    return rtrim($dir, '/');
}
