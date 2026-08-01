<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/github.php';
require_once __DIR__ . '/../lib/layout.php';
require_once __DIR__ . '/../lib/form_helpers.php';
require_login();

$DIR = 'public/uploads';
$ALLOWED = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif', 'svg' => 'image/svg+xml'];
$MAX_BYTES = 8 * 1024 * 1024; // 8MB

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'No file received, or the upload failed.';
    } else {
        $f = $_FILES['image'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!isset($ALLOWED[$ext])) {
            $error = 'Unsupported file type. Use jpg, png, webp, gif, or svg.';
        } elseif ($f['size'] > $MAX_BYTES) {
            $error = 'File is too large (max 8MB).';
        } else {
            try {
                $safeName = preg_replace('/[^a-z0-9._-]+/', '-', strtolower(pathinfo($f['name'], PATHINFO_FILENAME)));
                $filename = $safeName . '-' . substr(md5(uniqid('', true)), 0, 6) . '.' . $ext;
                $content = file_get_contents($f['tmp_name']);
                gh_put_file($DIR . '/' . $filename, $content, 'Upload ' . $filename . ' via admin panel');
                flash_set('ok', 'Uploaded. Use "/uploads/' . $filename . '" in content fields.');
                header('Location: upload.php');
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

$images = [];
$listError = null;
try {
    $images = gh_list_dir($DIR);
    usort($images, function ($a, $b) { return strcmp($b['name'], $a['name']); });
} catch (Exception $e) {
    $listError = $e->getMessage();
}

render_header('Media', 'media');
?>
<h1>Media</h1>
<p class="sub">Uploads go straight to <code>public/uploads/</code> in the repo — reference them as <code>/uploads/filename.jpg</code>.</p>
<?php flash_render(); ?>
<?php if ($error) : ?><div class="flash error"><?php echo h($error); ?></div><?php endif; ?>

<div class="card">
  <form method="post" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <div class="field">
      <label for="image">Choose an image</label>
      <input type="file" id="image" name="image" accept="image/*" required>
    </div>
    <button type="submit" class="btn">Upload</button>
  </form>
</div>

<?php if ($listError) : ?><div class="flash error">Couldn't list uploads: <?php echo h($listError); ?></div><?php endif; ?>
<table>
  <thead><tr><th>File</th><th>Use in content as</th></tr></thead>
  <tbody>
    <?php foreach ($images as $img) : ?>
      <tr>
        <td><a href="<?php echo defined('SITE_URL') ? SITE_URL . '/uploads/' . h($img['name']) : '#'; ?>" target="_blank"><?php echo h($img['name']); ?></a></td>
        <td><code>/uploads/<?php echo h($img['name']); ?></code></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$images && !$listError) : ?><tr><td colspan="2">No images uploaded yet.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php render_footer(); ?>
