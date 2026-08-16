<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

if (!verify_csrf_token($_GET['csrf_token'] ?? null)) {
    redirect('admin/users/index.php?msg=' . urlencode('Invalid request.'));
}

$id = (int) ($_GET['id'] ?? 0);

if ($id === current_user_id()) {
    redirect('admin/users/index.php?msg=' . urlencode('You cannot delete your own account.'));
}

$stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
$stmt->execute([$id]);

log_action(current_user_id(), 'user_deleted', "user_id={$id}");

redirect('admin/users/index.php?msg=' . urlencode('User deleted.'));
