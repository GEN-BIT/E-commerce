<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $siteName = trim($_POST['site_name'] ?? '');
        $currencySymbol = trim($_POST['currency_symbol'] ?? '');
        $currencyCode = trim($_POST['currency_code'] ?? '');
        $siteEmail = trim($_POST['site_email'] ?? '');
        $sitePhone = trim($_POST['site_phone'] ?? '');
        $siteAddress = trim($_POST['site_address'] ?? '');
        $siteDescription = trim($_POST['site_description'] ?? '');
        $seoKeywords = trim($_POST['seo_keywords'] ?? '');
        $maintenanceMode = isset($_POST['maintenance_mode']) ? '1' : '0';
        $allowRegistration = isset($_POST['allow_registration']) ? '1' : '0';
        $emailOrders = isset($_POST['email_orders']) ? '1' : '0';
        $stockAlerts = isset($_POST['stock_alerts']) ? '1' : '0';

        if ($siteName === '') $errors[] = 'Site name is required.';
        if ($currencySymbol === '') $errors[] = 'Currency symbol is required.';
        if ($currencyCode === '') $errors[] = 'Currency code is required.';

        if (empty($errors)) {
            set_setting('site_name', $siteName);
            set_setting('currency_symbol', $currencySymbol);
            set_setting('currency_code', $currencyCode);
            set_setting('site_email', $siteEmail);
            set_setting('site_phone', $sitePhone);
            set_setting('site_address', $siteAddress);
            set_setting('site_description', $siteDescription);
            set_setting('seo_keywords', $seoKeywords);
            set_setting('maintenance_mode', $maintenanceMode);
            set_setting('allow_registration', $allowRegistration);
            set_setting('email_orders', $emailOrders);
            set_setting('stock_alerts', $stockAlerts);

            log_action(current_user_id(), 'settings_updated', "site_name={$siteName}");
            redirect('admin/settings/index.php?msg=' . urlencode('Settings saved.'));
        }
    }
}

$settings = [
    'site_name' => get_setting('site_name', SITE_NAME),
    'currency_symbol' => get_setting('currency_symbol', '$'),
    'currency_code' => get_setting('currency_code', 'RWF'),
    'site_email' => get_setting('site_email', ''),
    'site_phone' => get_setting('site_phone', ''),
    'site_address' => get_setting('site_address', ''),
    'site_description' => get_setting('site_description', ''),
    'seo_keywords' => get_setting('seo_keywords', ''),
    'maintenance_mode' => get_setting('maintenance_mode', '0'),
    'allow_registration' => get_setting('allow_registration', '1'),
    'email_orders' => get_setting('email_orders', '1'),
    'stock_alerts' => get_setting('stock_alerts', '1'),
];

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

    <h2>General</h2>

    <label for="site_name">Site Name</label>
    <input type="text" id="site_name" name="site_name" value="<?= sanitize($settings['site_name']) ?>" required>

    <label for="site_description">Site Description</label>
    <textarea id="site_description" name="site_description" rows="3"><?= sanitize($settings['site_description']) ?></textarea>

    <label for="seo_keywords">SEO Keywords</label>
    <input type="text" id="seo_keywords" name="seo_keywords" value="<?= sanitize($settings['seo_keywords']) ?>" placeholder="ecommerce, online store, products">

    <h2>Currency</h2>

    <label for="currency_symbol">Currency Symbol</label>
    <input type="text" id="currency_symbol" name="currency_symbol" value="<?= sanitize($settings['currency_symbol']) ?>" required maxlength="5">

    <label for="currency_code">Currency Code</label>
    <input type="text" id="currency_code" name="currency_code" value="<?= sanitize($settings['currency_code']) ?>" required maxlength="10" placeholder="RWF, USD, EUR">

    <h2>Contact</h2>

    <label for="site_email">Contact Email</label>
    <input type="email" id="site_email" name="site_email" value="<?= sanitize($settings['site_email']) ?>">

    <label for="site_phone">Contact Phone</label>
    <input type="text" id="site_phone" name="site_phone" value="<?= sanitize($settings['site_phone']) ?>">

    <label for="site_address">Store Address</label>
    <textarea id="site_address" name="site_address" rows="2"><?= sanitize($settings['site_address']) ?></textarea>

    <h2>Features</h2>

    <label>
        <input type="checkbox" name="maintenance_mode" <?= $settings['maintenance_mode'] ? 'checked' : '' ?>>
        Maintenance Mode (disables public site)
    </label>

    <label>
        <input type="checkbox" name="allow_registration" <?= $settings['allow_registration'] ? 'checked' : '' ?>>
        Allow New User Registration
    </label>

    <label>
        <input type="checkbox" name="email_orders" <?= $settings['email_orders'] ? 'checked' : '' ?>>
        Send Order Confirmation Emails
    </label>

    <label>
        <input type="checkbox" name="stock_alerts" <?= $settings['stock_alerts'] ? 'checked' : '' ?>>
        Enable Low Stock Alerts
    </label>

    <button type="submit">Save Settings</button>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
