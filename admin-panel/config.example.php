<?php
// Copy this file to config.php (same folder) and fill in real values.
// config.php is gitignored — it holds real secrets and must never be
// committed. See PHP-ADMIN-SETUP.md for how to get each value.

// Your login. Generate the hash with: php generate-password-hash.php
define('ADMIN_EMAIL', 'you@example.com');
define('ADMIN_PASSWORD_HASH', '$2y$10$REPLACE_ME_WITH_OUTPUT_OF_generate-password-hash.php');

// A fine-grained GitHub token scoped to ONLY this repo, with
// "Contents: Read and write" permission. Every save in this admin panel
// becomes a commit made with this token.
define('GITHUB_TOKEN', 'github_pat_REPLACE_ME');
define('GITHUB_REPO', 'your-username/your-repo-name');
define('GITHUB_BRANCH', 'main');

// Shown in the admin footer / used for "back to site" links — no trailing slash.
define('SITE_URL', 'https://morocco-excursion.com');
