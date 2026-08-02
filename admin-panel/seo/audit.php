<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/github.php';
require_once __DIR__ . '/../lib/frontmatter.php';
require_once __DIR__ . '/../lib/layout.php';
require_once __DIR__ . '/../lib/form_helpers.php';
require_login();
admin_session_start();

$CONTENT_DIR = 'src/content/site';
$CACHE_TTL = 600; // 10 minutes — long enough to browse the results without refetching on every click, short enough to not go stale for long.
$LANGS = ['en', 'fr', 'es', 'it', 'pt-br'];

$refresh = !empty($_GET['refresh']);
$cache = isset($_SESSION['seo_audit_v2']) ? $_SESSION['seo_audit_v2'] : null;
$cacheAge = $cache ? time() - $cache['computedAt'] : null;

// --- Helpers used both when building the cache and when it's already warm ---

function strip_html($html) {
    return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $html))));
}

// Pulls every markdown-link and HTML href target out of a blob of text.
// Markdown links can carry an optional title after the URL — [t](/x "Title")
// — so we stop at the first whitespace/paren rather than capturing to ")".
function extract_link_targets($text) {
    $links = [];
    if (preg_match_all('/\]\(\s*([^)\s]+)/', $text, $m)) $links = array_merge($links, $m[1]);
    if (preg_match_all('/href=["\']([^"\']+)["\']/i', $text, $m)) $links = array_merge($links, $m[1]);
    return $links;
}

// Resolves a raw link target to a site-relative path with no leading/trailing
// slash (matching how urlPath is stored), or null if it's not a checkable
// internal link (external site, mailto/tel, anchor-only, query-only route
// like Google Maps directions, etc).
function normalize_internal_link($url) {
    $url = trim($url);
    if ($url === '' || $url[0] === '#') return null;
    if (preg_match('#^(mailto:|tel:|javascript:|data:)#i', $url)) return null;
    if (preg_match('#^https?://(www\.)?morocco-excursion\.com(/.*)?$#i', $url, $m)) {
        $path = isset($m[2]) ? $m[2] : '/';
    } elseif ($url[0] === '/') {
        $path = $url;
    } else {
        return null; // external domain, or a relative asset path — not checkable
    }
    $path = preg_split('/[?#]/', $path)[0];
    return trim($path, '/'); // '' means homepage
}

if ($refresh || !$cache || $cacheAge > $CACHE_TTL) {
    try {
        $files = gh_list_dir($CONTENT_DIR);
        $mdFiles = [];
        foreach ($files as $f) {
            if (substr($f['name'], -3) === '.md') $mdFiles[$f['name']] = $f['path'];
        }
        $fetched = gh_get_files_multi($mdFiles);

        $entries = [];
        foreach ($fetched as $name => $file) {
            if (!$file) continue;
            $parts = explode('__', substr($name, 0, -3), 3);
            if (count($parts) !== 3) continue;
            list($lang, $section, $pslug) = $parts;
            $parsed = parse_frontmatter($file['content']);
            $data = $parsed['data'];
            $body = $parsed['body'];

            // A single fingerprint of the actual page content (body copy +
            // overview), used to catch two pages that are really the same
            // content published twice under different slugs.
            $contentText = strip_html($body);
            if (!empty($data['overviewHtml'])) $contentText .= ' ' . strip_html($data['overviewHtml']);
            $contentHash = strlen($contentText) > 40 ? md5(strtolower(preg_replace('/\s+/', ' ', $contentText))) : null;

            // Collect every internal link this page contains, from the body
            // markdown and from every rich-HTML field that can hold one.
            $linkBlob = $body;
            foreach (['overviewHtml', 'priceHeading', 'notesHeading'] as $k) {
                if (!empty($data[$k])) $linkBlob .= ' ' . $data[$k];
            }
            foreach (['itinerary', 'faqs'] as $listKey) {
                if (!empty($data[$listKey]) && is_array($data[$listKey])) {
                    foreach ($data[$listKey] as $item) {
                        if (isset($item['html'])) $linkBlob .= ' ' . $item['html'];
                        if (isset($item['aHtml'])) $linkBlob .= ' ' . $item['aHtml'];
                    }
                }
            }
            $rawLinks = extract_link_targets($linkBlob);
            $links = [];
            foreach ($rawLinks as $raw) {
                $norm = normalize_internal_link($raw);
                if ($norm !== null) $links[$norm] = true; // de-dupe per file
            }

            $entries[] = [
                'file' => $name,
                'lang' => $lang,
                'section' => $section,
                'pslug' => $pslug,
                'urlPath' => isset($data['urlPath']) ? trim((string) $data['urlPath'], '/') : '',
                'title' => isset($data['title']) ? trim((string) $data['title']) : '',
                'metaTitle' => isset($data['metaTitle']) && $data['metaTitle'] ? trim((string) $data['metaTitle']) : '',
                'metaDescription' => isset($data['metaDescription']) && $data['metaDescription'] ? trim((string) $data['metaDescription']) : '',
                'contentHash' => $contentHash,
                'links' => array_keys($links),
                'hasOverview' => !empty($data['overviewHtml']),
            ];
        }

        // Missing alt text — a separate, sitewide check against the gallery
        // image manifest rather than per-page frontmatter (see lib/gallery.php).
        $altIssues = [];
        $imageManifestPaths = [];
        try {
            $manifestFile = gh_get_file('src/data/tour-images.json');
            $manifest = $manifestFile ? json_decode($manifestFile['content'], true) : [];
            if (is_array($manifest)) {
                foreach ($manifest as $urlPath => $images) {
                    if (!is_array($images)) continue;
                    $imageManifestPaths[trim($urlPath, '/')] = count($images);
                    foreach ($images as $img) {
                        if (empty($img['alt']) || trim((string) $img['alt']) === '') {
                            $altIssues[] = ['urlPath' => $urlPath, 'file' => isset($img['file']) ? $img['file'] : '(unknown file)'];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // non-fatal — the rest of the audit still runs
        }

        $_SESSION['seo_audit_v2'] = ['entries' => $entries, 'altIssues' => $altIssues, 'imageManifestPaths' => $imageManifestPaths, 'computedAt' => time()];
        $cache = $_SESSION['seo_audit_v2'];
    } catch (Exception $e) {
        $error = $e->getMessage();
        if (!$cache) { $cache = ['entries' => [], 'altIssues' => [], 'imageManifestPaths' => [], 'computedAt' => time()]; }
    }
}

$entries = $cache['entries'];
$altIssues = $cache['altIssues'];
$imageManifestPaths = $cache['imageManifestPaths'];

// --- Compute issues ---------------------------------------------------------
$critical = [];
$warnings = [];
$infos = [];

// Every route that actually resolves on the live site, for broken-link
// checking: every content page's own urlPath, plus the handful of
// generated listing routes that aren't backed by a content file.
$validPaths = ['' => true, 'tours' => true];
foreach ($LANGS as $l) {
    if ($l === 'en') continue;
    $validPaths[$l] = true;
    $validPaths[$l . '/tours'] = true;
}
foreach ($entries as $e) {
    if ($e['urlPath'] !== '') $validPaths[$e['urlPath']] = true;
}

foreach ($entries as $e) {
    if ($e['title'] === '') {
        $critical[] = ['file' => $e['file'], 'issue' => 'Missing title entirely.'];
    }
}

// Duplicate urlPath within the same language — two files claiming the same
// route; one silently shadows the other at build time.
$byUrlPath = [];
foreach ($entries as $e) {
    if ($e['urlPath'] === '') continue;
    $byUrlPath[$e['urlPath']][] = $e['file'];
}
foreach ($byUrlPath as $path => $files) {
    if (count($files) > 1) {
        $critical[] = ['file' => implode(', ', $files), 'issue' => 'Same URL path ("' . $path . '") claimed by ' . count($files) . ' files — only one can actually be reachable.'];
    }
}

// Broken internal links — this page links to a path that doesn't resolve
// to any real page on the site.
foreach ($entries as $e) {
    foreach ($e['links'] as $target) {
        if (!isset($validPaths[$target])) {
            $critical[] = ['file' => $e['file'], 'issue' => 'Broken internal link to "/' . $target . '" — no page has that URL.'];
        }
    }
}

// Duplicate titles within the same language — two pages competing for the
// same search-result title. Uses metaTitle when set (that's what actually
// renders), falling back to title.
$byLang = [];
foreach ($entries as $e) {
    $effectiveTitle = $e['metaTitle'] !== '' ? $e['metaTitle'] : $e['title'];
    if ($effectiveTitle === '') continue;
    $key = $e['lang'] . '|' . strtolower($effectiveTitle);
    $byLang[$key][] = $e['file'];
}
foreach ($byLang as $key => $files) {
    if (count($files) > 1) {
        $critical[] = ['file' => implode(', ', $files), 'issue' => 'Same title used on ' . count($files) . ' pages in this language — they compete for the same search result.'];
    }
}

// Duplicate content — the same page copy published under two different
// URLs (thin/duplicate content is a real ranking penalty, and it's exactly
// how stray legacy pages accumulate over time).
$byContentHash = [];
foreach ($entries as $e) {
    if (!$e['contentHash']) continue;
    $byContentHash[$e['lang'] . '|' . $e['contentHash']][] = $e['file'];
}
foreach ($byContentHash as $key => $files) {
    if (count($files) > 1) {
        $critical[] = ['file' => implode(', ', $files), 'issue' => 'Identical page content published under ' . count($files) . ' different URLs — duplicate content.'];
    }
}

// Duplicate meta descriptions within the same language.
$byDesc = [];
foreach ($entries as $e) {
    if ($e['metaDescription'] === '') continue;
    $byDesc[$e['lang'] . '|' . strtolower($e['metaDescription'])][] = $e['file'];
}
foreach ($byDesc as $key => $files) {
    if (count($files) > 1) {
        $warnings[] = ['file' => implode(', ', $files), 'issue' => 'Same SEO description used on ' . count($files) . ' pages in this language.'];
    }
}

foreach ($entries as $e) {
    $effectiveTitle = $e['metaTitle'] !== '' ? $e['metaTitle'] : $e['title'];
    $len = mb_strlen($effectiveTitle);
    if ($effectiveTitle !== '' && ($len < 15 || $len > 70)) {
        $warnings[] = ['file' => $e['file'], 'issue' => 'Title is ' . $len . ' characters (recommended 15–70) — "' . $effectiveTitle . '"'];
    }
    if ($e['metaDescription'] !== '') {
        $dlen = mb_strlen($e['metaDescription']);
        if ($dlen < 70 || $dlen > 160) {
            $warnings[] = ['file' => $e['file'], 'issue' => 'SEO description is ' . $dlen . ' characters (recommended 70–160).'];
        }
    } else {
        $infos[] = ['file' => $e['file'], 'issue' => 'No SEO description override — uses the site-wide default from Settings.'];
    }
    if ($e['urlPath'] !== '' && $e['section'] !== 'cars' && empty($imageManifestPaths[$e['urlPath']])) {
        $infos[] = ['file' => $e['file'], 'issue' => 'No real photos assigned yet — the page falls back to a placeholder image.'];
    }
}

usort($critical, function ($a, $b) { return strcmp($a['file'], $b['file']); });
usort($warnings, function ($a, $b) { return strcmp($a['file'], $b['file']); });
usort($infos, function ($a, $b) { return strcmp($a['file'], $b['file']); });

render_header('SEO Audit', 'seo');
?>
<div class="actions-row">
  <div>
    <h1>SEO Audit</h1>
    <p class="sub"><?php echo count($entries); ?> pages checked<?php if ($cache) : ?> · updated <?php echo h(date('g:ia', $cache['computedAt'])); ?><?php endif; ?></p>
  </div>
  <a href="audit.php?refresh=1" class="btn secondary small">↻ Refresh</a>
</div>
<?php if (!empty($error)) : ?><div class="flash error">Couldn't load from GitHub: <?php echo h($error); ?></div><?php endif; ?>

<div class="stat-grid" style="margin-bottom:28px">
  <div class="stat-card" style="cursor:default">
    <span class="stat-icon" style="background:#fdecea;color:var(--danger)">!</span>
    <span class="stat-num"><?php echo count($critical); ?></span>
    <span class="stat-label">Critical</span>
  </div>
  <div class="stat-card" style="cursor:default">
    <span class="stat-icon" style="background:#fff8e1;color:#a86b00">~</span>
    <span class="stat-num"><?php echo count($warnings); ?></span>
    <span class="stat-label">Warnings</span>
  </div>
  <div class="stat-card" style="cursor:default">
    <span class="stat-icon" style="background:#eef5f8;color:#2f83b5">i</span>
    <span class="stat-num"><?php echo count($infos); ?></span>
    <span class="stat-label">Info</span>
  </div>
  <div class="stat-card" style="cursor:default">
    <span class="stat-icon" style="background:#f0eefc;color:#6d4fd1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
    <span class="stat-num"><?php echo count($altIssues); ?></span>
    <span class="stat-label">Missing Alt Text</span>
  </div>
</div>

<?php
function render_issue_table($rows, $CONTENT_DIR) {
    if (!$rows) { echo '<p class="sub">Nothing here.</p>'; return; }
    echo '<table><thead><tr><th>Page</th><th>Issue</th><th></th></tr></thead><tbody>';
    foreach ($rows as $r) {
        $firstFile = trim(explode(',', $r['file'])[0]);
        echo '<tr><td><code style="font-size:12.5px">' . h($r['file']) . '</code></td><td>' . h($r['issue']) . '</td>';
        echo '<td>' . (preg_match('/\.md$/', $firstFile) ? '<a class="btn small" href="../content/edit.php?file=' . urlencode($firstFile) . '">Edit</a>' : '') . '</td></tr>';
    }
    echo '</tbody></table>';
}
?>

<h2 class="section-heading" style="margin-top:8px">Critical (<?php echo count($critical); ?>)</h2>
<div class="hint" style="margin-bottom:10px">Missing titles, broken internal links, duplicate URLs, duplicate titles, and duplicate content — these actively hurt rankings or break navigation.</div>
<?php render_issue_table($critical, $CONTENT_DIR); ?>

<h2 class="section-heading" style="margin-top:28px">Warnings (<?php echo count($warnings); ?>)</h2>
<div class="hint" style="margin-bottom:10px">Title/description length outside the recommended range, or a description reused on more than one page.</div>
<?php render_issue_table($warnings, $CONTENT_DIR); ?>

<h2 class="section-heading" style="margin-top:28px">Missing Alt Text (<?php echo count($altIssues); ?>)</h2>
<?php if (!$altIssues) : ?>
  <p class="sub">Every gallery photo has alt text.</p>
<?php else : ?>
  <div class="hint" style="margin-bottom:10px">These come from the shared gallery photo pool (src/data/tour-images.json), not individual page fields — ask your developer to fill these in directly, the admin panel doesn't edit this file yet.</div>
  <table><thead><tr><th>Used on</th><th>File</th></tr></thead><tbody>
    <?php foreach ($altIssues as $a) : ?>
      <tr><td><?php echo h($a['urlPath']); ?></td><td><code style="font-size:12.5px"><?php echo h($a['file']); ?></code></td></tr>
    <?php endforeach; ?>
  </tbody></table>
<?php endif; ?>

<h2 class="section-heading" style="margin-top:28px">Info (<?php echo count($infos); ?>)</h2>
<details>
  <summary style="cursor:pointer;color:var(--muted);font-size:13px;margin-bottom:10px">Show — mostly expected, not necessarily action needed (missing SEO description override, no real photos yet)</summary>
  <?php render_issue_table($infos, $CONTENT_DIR); ?>
</details>

<?php render_footer(); ?>
