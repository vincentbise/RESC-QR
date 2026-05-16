<?php

require_once __DIR__ . '/database.php';

define('APP_NAME', 'RESC-QR');
define('APP_FULL_NAME', 'Rapid Emergency Status Checking via Quick Response');
define('APP_VERSION', '1.0.0');
define('BASE_URL', '/RESC-QR');
define('PUBLIC_URL', BASE_URL . '/public');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
session_set_cookie_params([
    'lifetime' => 3600,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,
    'httponly'  => true,
    'samesite'  => 'Lax'
]);
session_start();

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

define('EMERGENCY_HOTLINES', [
    ['name' => 'National Emergency', 'number' => '911'],
    ['name' => 'Philippine Red Cross', 'number' => '143'],
    ['name' => 'NDRRMC', 'number' => '(02) 8911-5061'],
    ['name' => 'PNP Hotline', 'number' => '117'],
    ['name' => 'Bureau of Fire Protection', 'number' => '(02) 8426-0219'],
    ['name' => 'USeP Security Office', 'number' => '(084) 655-0840'],
]);
