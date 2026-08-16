<?php
require_once __DIR__ . '/../config/config.php';
require_login();

$stmt = $pdo->prepare('SELECT name, email, phone, address, role, created_at FROM users WHERE id = ?');
$stmt->execute([current_user_id()]);
$user = $stmt->fetch();

include __DIR__ . '/../includes/header.php';
?>
<h1>Profile</h1>

<table>
    <tbody>
        <tr><th>Name</th><td><?= sanitize($user['name']) ?></td></tr>
        <tr><th>Email</th><td><?= sanitize($user['email']) ?></td></tr>
        <tr><th>Phone</th><td><?= sanitize($user['phone'] ?? '—') ?></td></tr>
        <tr><th>Address</th><td><?= sanitize($user['address'] ?? '—') ?></td></tr>
        <tr><th>Member since</th><td><?= sanitize($user['created_at']) ?></td></tr>
    </tbody>
</table>

<p><a href="<?= BASE_URL ?>/account/edit-profile.php">Edit Profile</a></p>
<p><a href="<?= BASE_URL ?>/account/index.php">&larr; Back to account</a></p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
