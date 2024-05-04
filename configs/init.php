<?php

// URL
define('SITE_SCHEME', 'http://');
define('SITE_HOST', 'localhost');
define('SITE_PATH', '');
define('SITE_URL', SITE_SCHEME . SITE_HOST . SITE_PATH);
// Cookie
define('COOKIE_PATH', '/' . SITE_PATH);
// Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'justdocs');

// ReCaptcha
define('g_site_key', '6LerhaYbAAAAAG5MsOgY6w7cjbvJjU61hGfPqSRU');
define('g_secret_key', '6LerhaYbAAAAAF_qswE64H5DdqoukhAhKnxd6nrQ');
// Admin account
define('ADMIN_LOGIN', ['account' => 'admin', 'password' => 'khanh65me1']);

// Time zone
date_default_timezone_set('Asia/Ho_Chi_Minh');

ini_set('session.use_trans_sid', '0');
ini_set('arg_separator.output', '&amp;');
mb_internal_encoding('UTF-8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1);