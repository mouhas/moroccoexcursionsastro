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
    // Markdown links: [text](URL) or [text](URL "Title"). The URL group's
    // alternation allows one level of the URL's own matched parens — e.g.
    // Wikipedia's .../wiki/Riad_(architecture) — without stopping at that
    // inner ")" and truncating the URL before its real closing paren. The
    // optional trailing (?:\s+"...")? absorbs a markdown title attribute
    // so it doesn't get swallowed into the URL capture either.
    if (preg_match_all('/\]\(\s*((?:[^()\s]|\([^()]*\))+)(?:\s+"[^"]*")?\)/', $text, $m)) $links = array_merge($links, $m[1]);
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

// Every external (non-morocco-excursion.com) http(s) link a page contains —
// the candidates for the live external-link check.
function extract_external_links($text) {
    $out = [];
    foreach (extract_link_targets($text) as $raw) {
        $url = trim($raw);
        if (preg_match('#^https?://#i', $url) && !preg_match('#morocco-excursion\.com#i', $url)) {
            $out[$url] = true; // de-dupe per file
        }
    }
    return array_keys($out);
}

// A body that contains its own level-1 "# Heading" duplicates the page's
// own auto-rendered <h1>{title}</h1> — two H1s on one page confuses search
// engines about what the page is actually about. (`##` and deeper are fine.)
function has_stray_h1($body) {
    return (bool) preg_match('/^#[ \t]+\S/m', $body);
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
                'externalLinks' => extract_external_links($linkBlob),
                'strayH1' => has_stray_h1($body),
                'hasOverview' => !empty($data['overviewHtml']),
            ];
        }

        // Live-check every distinct external link found anywhere on the site
        // (small enough — usually a couple dozen Wikipedia/reference links —
        // to check on every refresh rather than needing its own cache).
        $allExternal = [];
        foreach ($entries as $e) foreach ($e['externalLinks'] as $u) $allExternal[$u] = true;
        $externalStatus = check_urls_concurrent(array_keys($allExternal));

        // Cross-check the gallery photo manifest against what actually
        // exists in the repo — a manifest entry with no matching file shows
        // as a broken image on the live site.
        $repoImagePaths = [];
        try {
            $repoImagePaths = array_flip(gh_list_all_paths());
        } catch (Exception $e) {
            // non-fatal — the rest of the audit still runs
        }

        // Missing alt text and missing files — a separate, sitewide check
        // against the gallery image manifest rather than per-page
        // frontmatter (see lib/gallery.php).
        $altIssues = [];
        $missingImageFiles = [];
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
                        $imgFile = isset($img['file']) ? $img['file'] : null;
                        if ($imgFile && $repoImagePaths && !isset($repoImagePaths['public/images/' . $imgFile])) {
                            $missingImageFiles[] = ['urlPath' => $urlPath, 'file' => $imgFile];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // non-fatal — the rest of the audit still runs
        }

        $_SESSION['seo_audit_v2'] = [
            'entries' => $entries,
            'altIssues' => $altIssues,
            'missingImageFiles' => $missingImageFiles,
            'imageManifestPaths' => $imageManifestPaths,
            'externalStatus' => $externalStatus,
            'computedAt' => time(),
        ];
        $cache = $_SESSION['seo_audit_v2'];
    } catch (Exception $e) {
        $error = $e->getMessage();
        if (!$cache) { $cache = ['entries' => [], 'altIssues' => [], 'missingImageFiles' => [], 'imageManifestPaths' => [], 'externalStatus' => [], 'computedAt' => time()]; }
    }
}

$entries = $cache['entries'];
$altIssues = $cache['altIssues'];
$imageManifestPaths = $cache['imageManifestPaths'];
// ?? [] guards against a session cached by a previous version of this page
// (before these checks existed) that hasn't been refreshed yet.
$missingImageFiles = $cache['missingImageFiles'] ?? [];
$externalStatus = $cache['externalStatus'] ?? [];

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

// Broken external links — a live check (see check_urls_concurrent), not
// something guessable from the content alone. Only 404/410 ("this page is
// definitely gone") are unambiguous enough for Critical. Everything else
// that isn't a clean success — no response, 403, 429, 5xx — is just as
// likely to be a site blocking an automated HEAD request as a real outage,
// so it's a Warning worth a manual look rather than a certain broken link
// (verified against real sites: Britannica returns 403 to this exact check
// while working fine in an actual browser).
foreach ($entries as $e) {
    foreach ($e['externalLinks'] as $url) {
        $status = $externalStatus[$url] ?? null;
        if ($status === 404 || $status === 410) {
            $critical[] = ['file' => $e['file'], 'issue' => 'External link returned HTTP ' . $status . ' (page no longer exists): ' . $url];
        } elseif ($status === null || $status >= 400) {
            $warnings[] = ['file' => $e['file'], 'issue' => 'External link ' . ($status ? 'returned HTTP ' . $status : "didn't respond") . ' (may just be blocking automated requests — worth checking manually): ' . $url];
        }
    }
}

// A body with its own "# Heading" duplicates the page's auto-rendered H1
// (the title). Two H1s on one page muddies what search engines think the
// page is about — this is a real, if rare, editing mistake to catch.
foreach ($entries as $e) {
    if ($e['strayH1']) {
        $critical[] = ['file' => $e['file'], 'issue' => 'Body content has its own "# Heading", which duplicates the page\'s own title as a second H1.'];
    }
}

// Gallery photo manifest references a file that doesn't actually exist in
// the repo — that page will show a broken image icon to visitors.
foreach ($missingImageFiles as $m) {
    $critical[] = ['file' => $m['urlPath'], 'issue' => 'Gallery photo file missing from the repo: ' . $m['file']];
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
<div class="hint" style="margin-bottom:10px">Missing titles, broken internal/external links, duplicate URLs, duplicate titles, duplicate content, duplicate H1s, and missing gallery photo files — these actively hurt rankings or break navigation.</div>
<?php render_issue_table($critical, $CONTENT_DIR); ?>

<h2 class="section-heading" style="margin-top:28px">Warnings (<?php echo count($warnings); ?>)</h2>
<div class="hint" style="margin-bottom:10px">Title/description length outside the recommended range, a description reused on more than one page, or an external link that didn't respond (worth a manual check — could be blocking automated requests rather than actually down).</div>
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
