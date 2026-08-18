<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$errors = [];
$name = $email = $phone = $address = $country = '';
$role = ROLE_CUSTOMER;
$isActive = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $address  = trim($_POST['address'] ?? '');
        $country  = trim($_POST['country'] ?? '');
        $role     = $_POST['role'] ?? ROLE_CUSTOMER;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if ($name === '') $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
        if ($phone === '') $errors[] = 'Phone number is required. Include your country code (e.g. +250).';
        if ($address === '') $errors[] = 'Address is required.';
        if ($country === '') $errors[] = 'Country is required.';
        if ($password === '') $errors[] = 'Password is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';
        if (!in_array($role, [ROLE_CUSTOMER, ROLE_ADMIN], true)) $errors[] = 'Invalid role.';
        if (empty($_FILES['profile_image']['name'])) $errors[] = 'Profile picture is required.';

        $profileImageFilename = null;
        if (empty($errors) && !empty($_FILES['profile_image']['name'])) {
            try {
                $profileImageFilename = handle_image_upload($_FILES['profile_image'], UPLOAD_USERS_DIR);
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with that email already exists.';
            }
        }

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, phone, address, country, profile_image, password_hash, role, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$name, $email, $phone, $address, $country, $profileImageFilename, $hash, $role, $isActive]);

            log_action(current_user_id(), 'user_created', "user_id=" . (int) $pdo->lastInsertId() . " role={$role}");

            redirect('admin/users/index.php?msg=' . urlencode('User created.'));
        }
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Add User</h1>

<?php foreach ($errors as $error): ?>
    <p class="form-error"><?= sanitize($error) ?></p>
<?php endforeach; ?>

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

    <label for="country">Country</label>
    <select id="country" name="country" required>
        <option value="">Select your country</option>
        <?php foreach (get_countries() as $c): ?>
            <option value="<?= sanitize($c) ?>" <?= $country === $c ? 'selected' : '' ?>><?= sanitize($c) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="phone">Phone Number <span class="required">(with country code)</span></label>
    <input type="tel" id="phone" name="phone" value="<?= sanitize($phone) ?>" placeholder="+250 78 000 0000" required autocomplete="tel">

    <label for="address">Address</label>
    <textarea id="address" name="address" rows="2" required><?= sanitize($address) ?></textarea>

    <label for="profile_image">Profile Picture <span class="required">(required)</span></label>
    <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/webp" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required minlength="6">

    <label for="confirm_password">Confirm Password</label>
    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">

    <label>
        <input type="checkbox" name="is_active" <?= $isActive ? 'checked' : '' ?>>
        Active (user can log in)
    </label>

    <button type="submit">Create User</button>
</form>

<p><a href="<?= BASE_URL ?>/admin/users/index.php">&larr; Back to list</a></p>

<?php include __DIR__ . '/../../includes/footer.php'; ?>