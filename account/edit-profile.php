<?php
require_once __DIR__ . '/../config/config.php';
require_login();

$stmt = $pdo->prepare('SELECT name, email, phone, address FROM users WHERE id = ?');
$stmt->execute([current_user_id()]);
$user = $stmt->fetch();

$errors = [];
$success = null;

$name    = $user['name'];
$email   = $user['email'];
$phone   = $user['phone'] ?? '';
$address = $user['address'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($name === '') $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';

        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $stmt->execute([$email, current_user_id()]);
            if ($stmt->fetch()) {
                $errors[] = 'That email is already used by another account.';
            }
        }

        $wantsPasswordChange = $currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '';
        if ($wantsPasswordChange) {
            $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
            $stmt->execute([current_user_id()]);
            $hash = $stmt->fetch()['password_hash'];

            if (!password_verify($currentPassword, $hash)) {
                $errors[] = 'Current password is incorrect.';
            } elseif (strlen($newPassword) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'New passwords do not match.';
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?');
            $stmt->execute([$name, $email, $phone ?: null, $address ?: null, current_user_id()]);

            if ($wantsPasswordChange) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                $stmt->execute([$newHash, current_user_id()]);
            }

            $_SESSION['name'] = $name;
            $success = 'Profile updated.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h1>Edit Profile</h1>

<?php if ($success): ?>
    <p class="form-success"><?= sanitize($success) ?></p>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <p class="form-error"><?= sanitize($error) ?></p>
<?php endforeach; ?>

<form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= sanitize($name) ?>" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= sanitize($email) ?>" required>

    <label for="phone">Phone</label>
    <input type="text" id="phone" name="phone" value="<?= sanitize($phone) ?>">

    <label for="address">Address</label>
    <textarea id="address" name="address" rows="3"><?= sanitize($address) ?></textarea>

    <h2 style="font-size: 1.1rem; margin-top: 1.5rem;">Change Password (optional)</h2>

    <label for="current_password">Current Password</label>
    <input type="password" id="current_password" name="current_password">

    <label for="new_password">New Password</label>
    <input type="password" id="new_password" name="new_password" minlength="6">

    <label for="confirm_password">Confirm New Password</label>
    <input type="password" id="confirm_password" name="confirm_password" minlength="6">

    <button type="submit">Save Changes</button>
</form>

<p><a href="<?= BASE_URL ?>/account/index.php">&larr; Back to account</a></p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
