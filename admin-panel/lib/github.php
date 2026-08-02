<?php
// Minimal GitHub Contents API client. Every edit in this admin panel becomes
// a real commit to the site's repo — the GitHub Actions workflow already in
// .github/workflows/deploy.yml picks it up and republishes to GoDaddy.
// Requires config.php to define GITHUB_TOKEN, GITHUB_REPO, GITHUB_BRANCH.

function gh_request($method, $path, $body = null) {
    $url = 'https://api.github.com/repos/' . GITHUB_REPO . '/' . $path;
    $ch = curl_init($url);
    $headers = [
        'Authorization: Bearer ' . GITHUB_TOKEN,
        'Accept: application/vnd.github+json',
        'X-GitHub-Api-Version: 2022-11-28',
        'User-Agent: morocco-excursions-admin-panel',
        'Content-Type: application/json',
    ];
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        throw new Exception('GitHub request failed: ' . $err);
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $data = json_decode($raw, true);
    return ['status' => $status, 'data' => $data];
}

// Returns ['content' => string, 'sha' => string] or null if the file doesn't exist.
function gh_get_file($path) {
    $res = gh_request('GET', 'contents/' . rawurlencode_path($path) . '?ref=' . GITHUB_BRANCH);
    if ($res['status'] === 404) return null;
    if ($res['status'] !== 200) throw new Exception('GitHub GET failed (' . $res['status'] . '): ' . $path);
    $content = base64_decode(str_replace("\n", '', $res['data']['content']));
    return ['content' => $content, 'sha' => $res['data']['sha']];
}

// Fetches many files concurrently instead of one request at a time — an
// SEO audit across 200+ content files would otherwise take a couple of
// minutes; this brings it down to a few seconds. $paths is [key => path];
// returns [key => ['content'=>..., 'sha'=>...] or null]. Batches requests
// (default 20 at a time) rather than firing all of them at once, since
// GitHub's abuse-detection can throttle very large bursts of concurrent
// connections from one client.
function gh_get_files_multi($paths, $batchSize = 20) {
    $results = [];
    $chunks = array_chunk($paths, $batchSize, true);
    foreach ($chunks as $chunk) {
        $mh = curl_multi_init();
        $handles = [];
        foreach ($chunk as $key => $path) {
            $ch = curl_init('https://api.github.com/repos/' . GITHUB_REPO . '/contents/' . rawurlencode_path($path) . '?ref=' . GITHUB_BRANCH);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . GITHUB_TOKEN,
                'Accept: application/vnd.github+json',
                'X-GitHub-Api-Version: 2022-11-28',
                'User-Agent: morocco-excursions-admin-panel',
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_multi_add_handle($mh, $ch);
            $handles[$key] = $ch;
        }
        $running = null;
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running) curl_multi_select($mh);
        } while ($running && $status === CURLM_OK);
        foreach ($handles as $key => $ch) {
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $raw = curl_multi_getcontent($ch);
            $data = json_decode($raw, true);
            if ($httpStatus === 200 && isset($data['content'])) {
                $results[$key] = ['content' => base64_decode(str_replace("\n", '', $data['content'])), 'sha' => $data['sha']];
            } else {
                $results[$key] = null;
            }
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);
    }
    return $results;
}

// Creates or updates a file. Pass $sha (from gh_get_file) when updating an
// existing file — omit it only when you're sure the file doesn't exist yet.
function gh_put_file($path, $content, $message, $sha = null) {
    $body = [
        'message' => $message,
        'content' => base64_encode($content),
        'branch' => GITHUB_BRANCH,
    ];
    if ($sha) $body['sha'] = $sha;
    $res = gh_request('PUT', 'contents/' . rawurlencode_path($path), $body);
    if ($res['status'] !== 200 && $res['status'] !== 201) {
        $msg = isset($res['data']['message']) ? $res['data']['message'] : 'unknown error';
        throw new Exception('GitHub save failed (' . $res['status'] . '): ' . $msg);
    }
    return $res['data'];
}

function gh_delete_file($path, $sha, $message) {
    $res = gh_request('DELETE', 'contents/' . rawurlencode_path($path), [
        'message' => $message,
        'sha' => $sha,
        'branch' => GITHUB_BRANCH,
    ]);
    if ($res['status'] !== 200) {
        throw new Exception('GitHub delete failed (' . $res['status'] . ')');
    }
}

// Lists files directly inside a repo directory (non-recursive).
// Returns an array of ['name' => ..., 'path' => ..., 'sha' => ...], or [] if
// the directory doesn't exist yet (e.g. no bookings have come in yet).
function gh_list_dir($path) {
    $res = gh_request('GET', 'contents/' . rawurlencode_path($path) . '?ref=' . GITHUB_BRANCH);
    if ($res['status'] === 404) return [];
    if ($res['status'] !== 200) throw new Exception('GitHub list failed (' . $res['status'] . '): ' . $path);
    $out = [];
    foreach ($res['data'] as $item) {
        if ($item['type'] === 'file') {
            $out[] = ['name' => $item['name'], 'path' => $item['path'], 'sha' => $item['sha']];
        }
    }
    return $out;
}

function rawurlencode_path($path) {
    // Encode each segment separately so the slashes in the path survive.
    return implode('/', array_map('rawurlencode', explode('/', $path)));
}
