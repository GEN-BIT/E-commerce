<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT id, name, email, role, phone, address, created_at FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    redirect('admin/users/index.php?msg=' . urlencode('User not found.'));
}

$stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ?');
$stmt->execute([$id]);
$orderCount = (int) $stmt->fetch()['cnt'];

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1><?= sanitize($user['name']) ?></h1>

<ul>
    <li>Email: <?= sanitize($user['email']) ?></li>
    <li>Role: <?= sanitize($user['role']) ?></li>
    <li>Phone: <?= sanitize($user['phone'] ?? '—') ?></li>
    <li>Address: <?= sanitize($user['address'] ?? '—') ?></li>
    <li>Joined: <?= sanitize($user['created_at']) ?></li>
    <li>Total Orders: <?= $orderCount ?></li>
</ul>

<p>
    <a href="<?= BASE_URL ?>/admin/users/edit.php?id=<?= $user['id'] ?>">Edit</a> |
    <a href="<?= BASE_URL ?>/admin/users/index.php">Back to list</a>
</p>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
