<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$showInactive = isset($_GET['show_inactive']) && $_GET['show_inactive'] === '1';

if ($showInactive) {
    $users = $pdo->query('SELECT id, name, email, role, is_active, created_at FROM users ORDER BY created_at DESC')->fetchAll();
} else {
    $users = $pdo->query('SELECT id, name, email, role, is_active, created_at FROM users WHERE is_active = 1 ORDER BY created_at DESC')->fetchAll();
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Manage Users</h1>

<p><a href="<?= BASE_URL ?>/admin/users/add.php">+ Add User</a></p>

<?php if (!empty($_GET['msg'])): ?>
    <p class="form-success"><?= sanitize($_GET['msg']) ?></p>
<?php endif; ?>

<p>
    <a href="<?= BASE_URL ?>/admin/users/index.php"><?= $showInactive ? 'Show active only' : 'Show all users' ?></a>
</p>

<table border="1" cellpadding="6" cellspacing="0">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= sanitize($u['name']) ?></td>
            <td><?= sanitize($u['email']) ?></td>
            <td><?= sanitize($u['role']) ?></td>
            <td><?= $u['is_active'] ? bool_badge(true, 'Active', 'Active') : bool_badge(false, 'Inactive', 'Inactive') ?></td>
            <td><?= sanitize($u['created_at']) ?></td>
            <td>
                <a href="<?= BASE_URL ?>/admin/users/view.php?id=<?= $u['id'] ?>">View</a> |
                <a href="<?= BASE_URL ?>/admin/users/edit.php?id=<?= $u['id'] ?>">Edit</a> |
                <?php if ($u['id'] != current_user_id()): ?>
                    <a href="<?= BASE_URL ?>/admin/users/delete.php?id=<?= $u['id'] ?>&csrf_token=<?= generate_csrf_token() ?>"
                       onclick="return confirm('Permanently delete this user and all their data? This cannot be undone.')">Delete</a>
                <?php else: ?>
                    <span title="You cannot delete your own account">Delete</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
