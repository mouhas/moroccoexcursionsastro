<?php
// Shared logic for bookings/list.php and messages/list.php — both are the
// same shape of data (see worker/src/index.js), just different folders.

function render_submissions_page($dir, $title, $navKey) {
    require_once __DIR__ . '/github.php';
    require_once __DIR__ . '/frontmatter.php';
    require_once __DIR__ . '/layout.php';
    require_once __DIR__ . '/form_helpers.php';

    $error = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $fname = basename($_POST['file']);
        $path = $dir . '/' . $fname;
        try {
            if (!empty($_POST['delete'])) {
                $existing = gh_get_file($path);
                if ($existing) gh_delete_file($path, $existing['sha'], 'Delete ' . $fname . ' via admin panel');
                flash_set('ok', 'Deleted.');
            } else {
                $existing = gh_get_file($path);
                if ($existing) {
                    $entry = parse_data_file($existing['content'], 'json');
                    $entry['status'] = $_POST['status'];
                    gh_put_file($path, dump_data_file($entry, 'json'), 'Update status on ' . $fname, $existing['sha']);
                    flash_set('ok', 'Status updated.');
                }
            }
        } catch (Exception $e) {
            flash_set('error', 'Failed: ' . $e->getMessage());
        }
        header('Location: list.php');
        exit;
    }

    $items = [];
    try {
        $files = gh_list_dir($dir);
        foreach ($files as $f) {
            if (substr($f['name'], -5) !== '.json') continue;
            $full = gh_get_file($f['path']);
            if (!$full) continue;
            $entry = parse_data_file($full['content'], 'json');
            $entry['_file'] = $f['name'];
            $items[] = $entry;
        }
        usort($items, function ($a, $b) {
            return strcmp(isset($b['submittedAt']) ? $b['submittedAt'] : '', isset($a['submittedAt']) ? $a['submittedAt'] : '');
        });
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    render_header($title, $navKey);
    ?>
    <h1><?php echo h($title); ?></h1>
    <p class="sub"><?php echo count($items); ?> total<?php if (!$items) echo ' — nothing has come in yet.'; ?></p>
    <?php flash_render(); ?>
    <?php if ($error) : ?><div class="flash error">Couldn't load: <?php echo h($error); ?></div><?php endif; ?>

    <?php if ($items) : ?>
    <table>
      <thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Submitted</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($items as $it) : ?>
          <tr>
            <td><?php echo h(isset($it['name']) ? $it['name'] : ''); ?></td>
            <td><?php echo h(isset($it['email']) ? $it['email'] : ''); ?></td>
            <td><?php echo h(isset($it['subject']) ? $it['subject'] : ''); ?></td>
            <td><?php echo h(isset($it['submittedAt']) ? $it['submittedAt'] : ''); ?></td>
            <td>
              <form method="post" style="display:flex;gap:6px;align-items:center">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="file" value="<?php echo h($it['_file']); ?>">
                <select name="status" onchange="this.form.submit()">
                  <?php foreach (['new', 'contacted', 'booked', 'archived'] as $s) : ?>
                    <option value="<?php echo $s; ?>" <?php echo (isset($it['status']) && $it['status'] === $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
            <td>
              <details>
                <summary style="cursor:pointer">View</summary>
                <p><?php echo nl2br(h(isset($it['message']) ? $it['message'] : (isset($it['raw']) ? $it['raw'] : ''))); ?></p>
                <?php if (!empty($it['phone'])) : ?><p>Phone: <?php echo h($it['phone']); ?></p><?php endif; ?>
                <form method="post" onsubmit="return confirm('Delete this entry?')">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="file" value="<?php echo h($it['_file']); ?>">
                  <input type="hidden" name="delete" value="1">
                  <button type="submit" class="btn danger small">Delete</button>
                </form>
              </details>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
    <?php render_footer(); ?>
    <?php
}
