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
$isActive = (bool) $user['is_active'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role  = $_POST['role'] ?? ROLE_CUSTOMER;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
        if (!in_array($role, [ROLE_CUSTOMER, ROLE_ADMIN], true)) $errors[] = 'Invalid role.';

        // Don't let an admin demote themselves and get locked out
        if ($id === current_user_id() && $role !== ROLE_ADMIN) {
            $errors[] = 'You cannot remove your own admin role.';
        }

        // Don't let an admin deactivate themselves
        if ($id === current_user_id() && !$isActive) {
            $errors[] = 'You cannot deactivate your own account.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $errors[] = 'That email is already used by another account.';
            }
        }

        $profileImageFilename = $user['profile_image'];
        if (empty($errors) && !empty($_FILES['profile_image']['name'])) {
            try {
                $profileImageFilename = handle_image_upload($_FILES['profile_image'], UPLOAD_USERS_DIR);
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, role = ?, is_active = ?, profile_image = ? WHERE id = ?');
            $stmt->execute([$name, $email, $role, $isActive, $profileImageFilename, $id]);

            log_action(current_user_id(), 'user_updated', "user_id={$id} role={$role} active=" . ($isActive ? '1' : '0'));

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

<?php if ($user['profile_image']): ?>
    <p><img src="<?= sanitize(profile_image_url($user['profile_image'])) ?>" alt="" width="80" style="border-radius:50%;"></p>
<?php endif; ?>

<form method="post" action="" enctype="multipart/form-data">
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

    <label for="profile_image">Profile Picture</label>
    <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/webp">

    <label>
        <input type="checkbox" name="is_active" <?= $isActive ? 'checked' : '' ?>>
        Active (user can log in)
    </label>

    <button type="submit">Save Changes</button>
</form>

<p><a href="<?= BASE_URL ?>/admin/users/index.php">&larr; Back to list</a></p>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
