<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

if (!verify_csrf_token($_GET['csrf_token'] ?? null)) {
    redirect('admin/users/index.php?msg=' . urlencode('Invalid request.'));
}

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('UPDATE users SET is_active = 1 WHERE id = ?');
$stmt->execute([$id]);

log_action(current_user_id(), 'user_restored', "user_id={$id}");

redirect('admin/users/index.php?msg=' . urlencode('User reactivated.'));
