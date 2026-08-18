<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT id, name, email, role, phone, address, country, profile_image, is_active, created_at FROM users WHERE id = ?');
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

<?php if ($user['profile_image']): ?>
    <p><img src="<?= sanitize(profile_image_url($user['profile_image'])) ?>" alt="<?= sanitize($user['name']) ?>" width="100" style="border-radius:50%;"></p>
<?php endif; ?>

<ul>
    <li>Email: <?= sanitize($user['email']) ?></li>
    <li>Role: <?= sanitize($user['role']) ?></li>
    <li>Phone: <?= sanitize($user['phone'] ?? '—') ?></li>
    <li>Address: <?= sanitize($user['address'] ?? '—') ?></li>
    <li>Country: <?= sanitize($user['country'] ?? '—') ?></li>
    <li>Status: <?= $user['is_active'] ? bool_badge(true, 'Active', 'Active') : bool_badge(false, 'Inactive', 'Inactive') ?></li>
    <li>Joined: <?= sanitize($user['created_at']) ?></li>
    <li>Total Orders: <?= $orderCount ?></li>
</ul>

<p>
    <a href="<?= BASE_URL ?>/admin/users/edit.php?id=<?= $user['id'] ?>">Edit</a> |
    <?php if ($user['id'] != current_user_id()): ?>
        <a href="<?= BASE_URL ?>/admin/users/delete.php?id=<?= $user['id'] ?>&csrf_token=<?= generate_csrf_token() ?>"
           onclick="return confirm('Permanently delete this user and all their data? This cannot be undone.')">Delete</a>
    <?php endif; ?>
    |
    <a href="<?= BASE_URL ?>/admin/users/index.php">Back to list</a>
</p>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
