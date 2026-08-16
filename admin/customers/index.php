<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$customers = $pdo->query(
    "SELECT u.id, u.name, u.email, u.created_at,
            COUNT(o.id) AS order_count,
            COALESCE(SUM(CASE WHEN o.status != 'cancelled' THEN o.total_amount ELSE 0 END), 0) AS total_spent
     FROM users u
     LEFT JOIN orders o ON o.user_id = u.id
     WHERE u.role = 'customer'
     GROUP BY u.id
     ORDER BY total_spent DESC"
)->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Customers</h1>

<table border="1" cellpadding="6" cellspacing="0">
    <thead><tr><th>Name</th><th>Email</th><th>Joined</th><th>Orders</th><th>Total Spent</th></tr></thead>
    <tbody>
    <?php foreach ($customers as $c): ?>
        <tr>
            <td><a href="<?= BASE_URL ?>/admin/users/view.php?id=<?= $c['id'] ?>"><?= sanitize($c['name']) ?></a></td>
            <td><?= sanitize($c['email']) ?></td>
            <td><?= sanitize($c['created_at']) ?></td>
            <td><?= (int) $c['order_count'] ?></td>
            <td><?= format_price((float) $c['total_spent']) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($customers)): ?>
        <tr><td colspan="5">No customers yet.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
