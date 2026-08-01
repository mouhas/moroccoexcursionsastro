<?php
// Vendored from symfony/yaml 5.4 (MIT license, see LICENSE in this folder) —
// a battle-tested YAML parser/dumper, used instead of a hand-rolled one so
// tour/page frontmatter (which has real-world quirks: folded multi-line
// quoted strings, embedded HTML, apostrophes) round-trips correctly instead
// of risking silent corruption on save. No Composer needed on shared
// hosting — this just requires the files directly in dependency order.

if (!function_exists('trigger_deprecation')) {
    // symfony/yaml calls this for a few soft-deprecation warnings (e.g.
    // legacy octal number parsing). We don't need deprecation reporting
    // here, so this is a harmless no-op instead of vendoring the whole
    // symfony/deprecation-contracts package for one function.
    function trigger_deprecation(string $package, string $version, string $message, ...$args): void {}
}

require_once __DIR__ . '/Exception/ExceptionInterface.php';
require_once __DIR__ . '/Exception/RuntimeException.php';
require_once __DIR__ . '/Exception/ParseException.php';
require_once __DIR__ . '/Exception/DumpException.php';
require_once __DIR__ . '/Tag/TaggedValue.php';
require_once __DIR__ . '/Escaper.php';
require_once __DIR__ . '/Unescaper.php';
require_once __DIR__ . '/Inline.php';
require_once __DIR__ . '/ParserState.php';
require_once __DIR__ . '/Parser.php';
require_once __DIR__ . '/Dumper.php';
require_once __DIR__ . '/Yaml.php';
