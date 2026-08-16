<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$topSpenders = $pdo->query(
    "SELECT u.id, u.name, u.email, COUNT(o.id) AS order_count, SUM(o.total_amount) AS total_spent
     FROM users u
     JOIN orders o ON o.user_id = u.id AND o.status != 'cancelled'
     WHERE u.role = 'customer'
     GROUP BY u.id
     ORDER BY total_spent DESC
     LIMIT 10"
)->fetchAll();

$recentSignups = $pdo->query(
    "SELECT id, name, email, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC LIMIT 10"
)->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Customer Report</h1>

<h2>Top Spenders</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <thead><tr><th>Name</th><th>Orders</th><th>Total Spent</th></tr></thead>
    <tbody>
    <?php foreach ($topSpenders as $c): ?>
        <tr>
            <td><a href="<?= BASE_URL ?>/admin/users/view.php?id=<?= $c['id'] ?>"><?= sanitize($c['name']) ?></a></td>
            <td><?= (int) $c['order_count'] ?></td>
            <td><?= format_price((float) $c['total_spent']) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($topSpenders)): ?>
        <tr><td colspan="3">No orders yet.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<h2>Recent Signups</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <thead><tr><th>Name</th><th>Email</th><th>Joined</th></tr></thead>
    <tbody>
    <?php foreach ($recentSignups as $c): ?>
        <tr>
            <td><a href="<?= BASE_URL ?>/admin/users/view.php?id=<?= $c['id'] ?>"><?= sanitize($c['name']) ?></a></td>
            <td><?= sanitize($c['email']) ?></td>
            <td><?= sanitize($c['created_at']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<p><a href="<?= BASE_URL ?>/admin/customers/index.php">View full customer list</a></p>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
