<?php
require_once __DIR__ . '/../config/config.php';
require_login();

if (is_admin()) {
    redirect('admin/index.php');
}

if (!verify_csrf_token($_GET['csrf_token'] ?? null)) {
    redirect('cart/index.php?msg=' . urlencode('Invalid request.'));
}

clear_cart(current_user_id());

redirect('cart/index.php');
