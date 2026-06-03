<?php
define('DB_NAME', 'wordpress');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

define('AUTH_KEY',         'da284e1e973e8db768dd329ec0eb228345436598270ecbc0682b7ffd527a4465');
define('SECURE_AUTH_KEY',  'ef985436966542b86529c0d8a90acce8ea7d5dd161f6ff7c2fc223eada397402');
define('LOGGED_IN_KEY',    'b3c1f2e4a5d6789012345678901234567890abcdefabcdefabcdefabcdefabcd');
define('NONCE_KEY',        '1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef');
define('AUTH_SALT',        'abcdefabcdefabcdefabcdefabcdef1234567890123456789012345678901234');
define('SECURE_AUTH_SALT', 'fedcbafedcbafedcbafedcbafedcba0987654321098765432109876543210987');
define('LOGGED_IN_SALT',   '0a1b2c3d4e5f6789abcdef0123456789abcdef0123456789abcdef0123456789');
define('NONCE_SALT',       'f0e1d2c3b4a5968778695a4b3c2d1e0f1a2b3c4d5e6f78901234567890abcde');

$table_prefix = 'wp_';

define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true);

define('WP_AUTO_UPDATE_CORE', false);

// Dynamic URL — works on localhost:5000 and Replit preview domain
if (!defined('WP_HOME')) {
    $ss_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $ss_host   = $_SERVER['HTTP_HOST'] ?? 'localhost:5000';
    define('WP_HOME',    $ss_scheme . '://' . $ss_host);
    define('WP_SITEURL', $ss_scheme . '://' . $ss_host);
}

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
