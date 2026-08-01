<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/auth.php';
do_logout();
header('Location: login.php');
exit;
