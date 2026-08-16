<?php
/**
 * App-wide constants
 * Stage: 2 - Database
 */

// User roles
define('ROLE_CUSTOMER', 'customer');
define('ROLE_ADMIN', 'admin');

// Order statuses
define('ORDER_PENDING', 'pending');
define('ORDER_CONFIRMED', 'confirmed');
define('ORDER_PROCESSING', 'processing');
define('ORDER_SHIPPED', 'shipped');
define('ORDER_DELIVERED', 'delivered');
define('ORDER_CANCELLED', 'cancelled');

// Upload paths (relative to project root)
define('UPLOAD_PRODUCTS_DIR', __DIR__ . '/../uploads/products/');
define('UPLOAD_CATEGORIES_DIR', __DIR__ . '/../uploads/categories/');
define('UPLOAD_USERS_DIR', __DIR__ . '/../uploads/users/');

define('UPLOAD_PRODUCTS_URL', BASE_URL . '/uploads/products/');
define('UPLOAD_CATEGORIES_URL', BASE_URL . '/uploads/categories/');
define('UPLOAD_USERS_URL', BASE_URL . '/uploads/users/');

// Allowed image types for uploads
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB
