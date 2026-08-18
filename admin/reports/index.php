<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$totalRevenue = (float) $pdo->query(
    "SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders WHERE status != 'cancelled'"
)->fetch()['total'];

$totalOrders = (int) $pdo->query(
    "SELECT COUNT(*) AS cnt FROM orders WHERE status != 'cancelled'"
)->fetch()['cnt'];

$totalCustomers = (int) $pdo->query(
    'SELECT COUNT(*) AS cnt FROM users WHERE role = "customer"'
)->fetch()['cnt'];

$totalProducts = (int) $pdo->query(
    'SELECT COUNT(*) AS cnt FROM products WHERE is_active = 1'
)->fetch()['cnt'];

$pendingOrders = (int) $pdo->query(
    "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'"
)->fetch()['cnt'];

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Reports</h1>

<p>Quick overview of your store performance.</p>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 1.5rem 0;">
    <div style="border: 1px solid var(--color-line); border-radius: var(--radius); padding: 1.25rem; background: var(--color-surface);">
        <h3 style="margin: 0 0 .5rem; font-size: .9rem; color: var(--color-ink-soft);">Total Revenue</h3>
        <p style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--color-forest-dk);"><?= format_price($totalRevenue) ?></p>
    </div>
    <div style="border: 1px solid var(--color-line); border-radius: var(--radius); padding: 1.25rem; background: var(--color-surface);">
        <h3 style="margin: 0 0 .5rem; font-size: .9rem; color: var(--color-ink-soft);">Total Orders</h3>
        <p style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--color-forest-dk);"><?= number_format($totalOrders) ?></p>
    </div>
    <div style="border: 1px solid var(--color-line); border-radius: var(--radius); padding: 1.25rem; background: var(--color-surface);">
        <h3 style="margin: 0 0 .5rem; font-size: .9rem; color: var(--color-ink-soft);">Customers</h3>
        <p style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--color-forest-dk);"><?= number_format($totalCustomers) ?></p>
    </div>
    <div style="border: 1px solid var(--color-line); border-radius: var(--radius); padding: 1.25rem; background: var(--color-surface);">
        <h3 style="margin: 0 0 .5rem; font-size: .9rem; color: var(--color-ink-soft);">Active Products</h3>
        <p style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--color-forest-dk);"><?= number_format($totalProducts) ?></p>
    </div>
    <div style="border: 1px solid var(--color-line); border-radius: var(--radius); padding: 1.25rem; background: var(--color-surface);">
        <h3 style="margin: 0 0 .5rem; font-size: .9rem; color: var(--color-ink-soft);">Pending Orders</h3>
        <p style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--color-accent);"><?= number_format($pendingOrders) ?></p>
    </div>
</div>

<h2>Detailed Reports</h2>
<ul>
    <li><a href="<?= BASE_URL ?>/admin/reports/sales.php">Sales Report</a> — revenue by status and daily trends</li>
    <li><a href="<?= BASE_URL ?>/admin/reports/products.php">Products Report</a> — stock levels and product performance</li>
    <li><a href="<?= BASE_URL ?>/admin/reports/customers.php">Customers Report</a> — customer list with order counts</li>
</ul>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
