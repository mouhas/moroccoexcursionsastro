<?php
// Run this once to get the ADMIN_PASSWORD_HASH value for config.php:
//   php generate-password-hash.php
// It asks for a password (hidden if your terminal supports it) and prints
// the hash to paste into config.php. This file doesn't touch config.php
// itself and isn't needed after setup — delete it if you like, or leave it,
// it has no secrets in it.

if (PHP_SAPI !== 'cli') {
    die("Run this from the command line: php generate-password-hash.php\n");
}

echo "Choose an admin password (visible as you type — this is a local terminal, not a web form): ";
$handle = fopen('php://stdin', 'r');
$password = trim(fgets($handle));
fclose($handle);

if (strlen($password) < 8) {
    die("Use at least 8 characters.\n");
}

$hash = password_hash($password, PASSWORD_DEFAULT);
echo "\nPaste this into config.php as ADMIN_PASSWORD_HASH:\n\n";
echo $hash . "\n";
