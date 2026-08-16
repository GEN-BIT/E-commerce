<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    redirect('admin/users/index.php?msg=' . urlencode('User not found.'));
}

$errors = [];
$name  = $user['name'];
$email = $user['email'];
$role  = $user['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role  = $_POST['role'] ?? ROLE_CUSTOMER;

        if ($name === '') $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
        if (!in_array($role, [ROLE_CUSTOMER, ROLE_ADMIN], true)) $errors[] = 'Invalid role.';

        // Don't let an admin demote themselves and get locked out
        if ($id === current_user_id() && $role !== ROLE_ADMIN) {
            $errors[] = 'You cannot remove your own admin role.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $errors[] = 'That email is already used by another account.';
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
            $stmt->execute([$name, $email, $role, $id]);

            log_action(current_user_id(), 'user_updated', "user_id={$id} role={$role}");

            redirect('admin/users/index.php?msg=' . urlencode('User updated.'));
        }
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Edit User</h1>

<?php foreach ($errors as $error): ?>
    <p class="form-error"><?= sanitize($error) ?></p>
<?php endforeach; ?>

<form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= sanitize($name) ?>" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= sanitize($email) ?>" required>

    <label for="role">Role</label>
    <select id="role" name="role">
        <option value="customer" <?= $role === 'customer' ? 'selected' : '' ?>>Customer</option>
        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
    </select>

    <button type="submit">Save Changes</button>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
