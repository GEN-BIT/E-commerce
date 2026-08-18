<?php
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
           class="<?= $isActive ? 'active' : '' ?>">
            <?php if ($label === 'Dashboard'): ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:.35rem;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <?php endif; ?>
            <?= sanitize($label) ?>
        </a>
    <?php endforeach; ?>
</aside>
