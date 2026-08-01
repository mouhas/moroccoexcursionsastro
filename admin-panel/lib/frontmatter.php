<?php
// Splits a tour/page markdown file into its YAML frontmatter (parsed to a
// PHP array) and markdown body, and reassembles them back into a file. Uses
// the vendored symfony/yaml (see vendor/symfony-yaml) — verified to
// round-trip real content correctly (folded multi-paragraph HTML, nested
// itinerary/FAQ/review lists, etc.) via the exact same gray-matter/js-yaml
// stack Astro itself uses.
require_once __DIR__ . '/../vendor/symfony-yaml/autoload.php';
use Symfony\Component\Yaml\Yaml;

function parse_frontmatter($fileContent) {
    if (!preg_match('/^---\r?\n(.*?)\r?\n---\r?\n?(.*)$/s', $fileContent, $m)) {
        return ['data' => [], 'body' => $fileContent];
    }
    $data = Yaml::parse($m[1]);
    if (!is_array($data)) $data = [];
    return ['data' => $data, 'body' => $m[2]];
}

function dump_frontmatter($data, $body = '') {
    $yaml = Yaml::dump($data, 10, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    $out = "---\n" . $yaml . "---\n";
    if (trim($body) !== '') $out .= $body;
    return $out;
}

// Plain data files (settings, bookings, messages) — YAML or JSON, no
// frontmatter/body split. $format is 'yaml' or 'json'.
function parse_data_file($fileContent, $format) {
    if ($format === 'json') {
        $data = json_decode($fileContent, true);
        return is_array($data) ? $data : [];
    }
    $data = Yaml::parse($fileContent);
    return is_array($data) ? $data : [];
}

function dump_data_file($data, $format) {
    if ($format === 'json') {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    }
    return Yaml::dump($data, 10, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
}
