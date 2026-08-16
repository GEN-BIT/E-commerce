<?php
// Highlight whichever top-level admin section the current URL is under
$currentPath = $_SERVER['SCRIPT_NAME'];
$sections = [
    'Dashboard'  => '/admin/index.php',
    'Products'   => '/admin/products/',
    'Categories' => '/admin/categories/',
    'Orders'     => '/admin/orders/',
    'Users'      => '/admin/users/',
    'Customers'  => '/admin/customers/',
    'Reports'    => '/admin/reports/',
    'Settings'   => '/admin/settings/',
];
?>
<aside class="admin-sidebar">
    <?php foreach ($sections as $label => $path): ?>
        <?php $isActive = strpos($currentPath, $path) !== false; ?>
        <a href="<?= BASE_URL . rtrim($path, '/') ?><?= $path === '/admin/index.php' ? '' : '/index.php' ?>"
           class="<?= $isActive ? 'active' : '' ?>"><?= sanitize($label) ?></a>
    <?php endforeach; ?>
</aside>
