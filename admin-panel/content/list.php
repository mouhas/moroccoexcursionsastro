<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/github.php';
require_once __DIR__ . '/../lib/layout.php';
require_login();

$CONTENT_DIR = 'src/content/site';

$error = null;
$files = [];
try {
    $files = gh_list_dir($CONTENT_DIR);
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Filenames are "{lang}__{section}__{pslug}.md" — parse without fetching
// each file's content (221 files; fetching all of them on every list-page
// load would be slow and burn through the GitHub API rate limit).
$entries = [];
foreach ($files as $f) {
    if (substr($f['name'], -3) !== '.md') continue;
    $base = substr($f['name'], 0, -3);
    $parts = explode('__', $base, 3);
    if (count($parts) !== 3) continue;
    list($lang, $section, $pslug) = $parts;
    $entries[] = ['lang' => $lang, 'section' => $section, 'pslug' => $pslug, 'file' => $f['name']];
}

$qLang = isset($_GET['lang']) ? $_GET['lang'] : '';
$qSection = isset($_GET['section']) ? $_GET['section'] : '';
$qSearch = isset($_GET['q']) ? trim($_GET['q']) : '';

$filtered = array_filter($entries, function ($e) use ($qLang, $qSection, $qSearch) {
    if ($qLang && $e['lang'] !== $qLang) return false;
    if ($qSection && $e['section'] !== $qSection) return false;
    if ($qSearch && stripos($e['pslug'], $qSearch) === false) return false;
    return true;
});
usort($filtered, function ($a, $b) { return strcmp($a['pslug'], $b['pslug']); });

render_header('Site Content', 'content');
?>
<h1>Site Content</h1>
<p class="sub"><?php echo count($entries); ?> total entries across all languages — tours, cars, and pages.</p>
<?php flash_render(); ?>
<?php if ($error) : ?><div class="flash error">Couldn't load from GitHub: <?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<form class="filters" method="get">
  <input type="search" name="q" placeholder="Search by slug…" value="<?php echo htmlspecialchars($qSearch); ?>">
  <select name="lang">
    <option value="">All languages</option>
    <?php foreach (['en', 'fr', 'es', 'it', 'pt-br'] as $l) : ?>
      <option value="<?php echo $l; ?>" <?php echo $qLang === $l ? 'selected' : ''; ?>><?php echo $l; ?></option>
    <?php endforeach; ?>
  </select>
  <select name="section">
    <option value="">All sections</option>
    <?php foreach (['pages', 'tours', 'cars'] as $s) : ?>
      <option value="<?php echo $s; ?>" <?php echo $qSection === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn secondary small">Filter</button>
</form>

<table>
  <thead><tr><th>Slug</th><th>Language</th><th>Section</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($filtered as $e) : ?>
      <tr>
        <td><?php echo htmlspecialchars($e['pslug']); ?></td>
        <td><?php echo htmlspecialchars($e['lang']); ?></td>
        <td><?php echo htmlspecialchars($e['section']); ?></td>
        <td><a href="edit.php?file=<?php echo urlencode($e['file']); ?>" class="btn small">Edit</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$filtered) : ?>
      <tr><td colspan="4">No entries match those filters.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
<?php render_footer(); ?>
