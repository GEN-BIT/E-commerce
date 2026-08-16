<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$users = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Manage Users</h1>

<?php if (!empty($_GET['msg'])): ?>
    <p class="form-success"><?= sanitize($_GET['msg']) ?></p>
<?php endif; ?>

<table border="1" cellpadding="6" cellspacing="0">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= sanitize($u['name']) ?></td>
            <td><?= sanitize($u['email']) ?></td>
            <td><?= sanitize($u['role']) ?></td>
            <td><?= sanitize($u['created_at']) ?></td>
            <td>
                <a href="<?= BASE_URL ?>/admin/users/view.php?id=<?= $u['id'] ?>">View</a> |
                <a href="<?= BASE_URL ?>/admin/users/edit.php?id=<?= $u['id'] ?>">Edit</a> |
                <?php if ($u['id'] != current_user_id()): ?>
                    <a href="<?= BASE_URL ?>/admin/users/delete.php?id=<?= $u['id'] ?>&csrf_token=<?= generate_csrf_token() ?>"
                       onclick="return confirm('Delete this user?')">Delete</a>
                <?php else: ?>
                    <span title="You cannot delete your own account">Delete</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
