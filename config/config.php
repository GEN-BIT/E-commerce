<?php
/**
 * Global site configuration
 * Stage: 2 - Database / bootstrapping
 */

// Harden session cookie behavior before the session starts
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,   // JS can't read the session cookie
        'samesite' => 'Lax',  // basic CSRF mitigation for cross-site requests
    ]);
    session_start();
}

// Base URL - adjust if your LAMPP alias differs
define('BASE_URL', 'http://localhost/ecommerce');

define('SITE_NAME', 'My Store');

// Error display - turn off in production
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/../includes/log-functions.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cart-functions.php';
require_once __DIR__ . '/../includes/order-functions.php';
require_once __DIR__ . '/../includes/review-functions.php';
require_once __DIR__ . '/../includes/wishlist-functions.php';
