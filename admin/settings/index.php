<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$errors = [];
$siteName = get_setting('site_name', SITE_NAME);
$currencySymbol = get_setting('currency_symbol', '$');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $siteName = trim($_POST['site_name'] ?? '');
        $currencySymbol = trim($_POST['currency_symbol'] ?? '');

        if ($siteName === '') $errors[] = 'Site name is required.';
        if ($currencySymbol === '') $errors[] = 'Currency symbol is required.';

        if (empty($errors)) {
            set_setting('site_name', $siteName);
            set_setting('currency_symbol', $currencySymbol);
            log_action(current_user_id(), 'settings_updated', "site_name={$siteName}");
            redirect('admin/settings/index.php?msg=' . urlencode('Settings saved.'));
        }
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Store Settings</h1>

<?php if (!empty($_GET['msg'])): ?>
    <p class="form-success"><?= sanitize($_GET['msg']) ?></p>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <p class="form-error"><?= sanitize($error) ?></p>
<?php endforeach; ?>

<form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

    <label for="site_name">Site Name</label>
    <input type="text" id="site_name" name="site_name" value="<?= sanitize($siteName) ?>" required>

    <label for="currency_symbol">Currency Symbol</label>
    <input type="text" id="currency_symbol" name="currency_symbol" value="<?= sanitize($currencySymbol) ?>" required maxlength="5">

    <button type="submit">Save Settings</button>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
