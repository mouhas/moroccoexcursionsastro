<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';
require_login();
require_once __DIR__ . '/../lib/submissions.php';
render_submissions_page('src/content/bookings', 'Bookings', 'bookings');
