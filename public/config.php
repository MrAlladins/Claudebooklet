<?php
/**
 * Thaibooklet System Configuration
 */

// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'u357300497_thaibooklet');
define('DB_USER', 'u357300497_klickjonas');
define('DB_PASS', 'Jonas366#');

// Site settings
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));
define('APP_NAME', 'Thaibooklet System');
define('APP_VERSION', '1.0.0');

// WooCommerce API settings
define('WC_STORE_URL', ''); // Your WordPress site URL
define('WC_CONSUMER_KEY', '');
define('WC_CONSUMER_SECRET', '');

// Security settings
define('HASH_SALT', bin2hex(random_bytes(16)));
define('SESSION_LIFETIME', 86400); // 24 hours

// Debug mode
define('DEBUG_MODE', true);