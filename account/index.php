<?php
require_once __DIR__ . '/../config/config.php';
require_login();

if (is_admin()) {
    redirect('admin/index.php');
}

$stmt = $pdo->prepare('SELECT name, email, role, created_at FROM users WHERE id = ?');
$stmt->execute([current_user_id()]);
$user = $stmt->fetch();

include __DIR__ . '/../includes/header.php';
?>
<h1>My Account</h1>

<p>Welcome back, <?= sanitize($user['name']) ?>.</p>

<ul>
    <li><a href="<?= BASE_URL ?>/account/profile.php">View Profile</a></li>
    <li><a href="<?= BASE_URL ?>/account/edit-profile.php">Edit Profile</a></li>
    <li><a href="<?= BASE_URL ?>/account/orders.php">My Orders</a></li>
    <li><a href="<?= BASE_URL ?>/account/wishlist.php">My Wishlist</a></li>
    <li><a href="<?= BASE_URL ?>/auth/logout.php">Logout</a></li>
</ul>

<?php include __DIR__ . '/../includes/footer.php'; ?>
