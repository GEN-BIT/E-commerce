<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/mailer.php';

$loginErrors = [];
$registerErrors = [];
$forgotErrors = [];
$forgotSuccess = null;
$devFallbackLink = null;

$loginEmail = '';
$registerName = $registerEmail = '';

$activeTab = $_GET['tab'] ?? 'login';
$token = $_GET['token'] ?? $_POST['token'] ?? '';
if ($token !== '') {
    $activeTab = 'reset';
}

$action = $_POST['auth_action'] ?? '';

// ---------------------------------------------------------------
// LOGIN
// ---------------------------------------------------------------
if ($action === 'login') {
    $activeTab = 'login';
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $loginErrors[] = 'Invalid form submission. Please try again.';
    } else {
        $loginEmail = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($loginEmail === '' || $password === '') {
            $loginErrors[] = 'Email and password are required.';
        } else {
            $stmt = $pdo->prepare('SELECT id, name, password_hash, role FROM users WHERE email = ?');
            $stmt->execute([$loginEmail]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $loginErrors[] = 'Invalid email or password.';
                log_action(null, 'login_failed', "email={$loginEmail}");
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['role']    = $user['role'];
                $_SESSION['name']    = $user['name'];

                log_action((int) $user['id'], 'login_success', "role={$user['role']}");

                redirect($user['role'] === ROLE_ADMIN ? 'admin/index.php' : 'account/index.php');
            }
        }
    }
}

// ---------------------------------------------------------------
// REGISTER
// ---------------------------------------------------------------
if ($action === 'register') {
    $activeTab = 'register';
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $registerErrors[] = 'Invalid form submission. Please try again.';
    } else {
        $registerName  = trim($_POST['name'] ?? '');
        $registerEmail = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if ($registerName === '') $registerErrors[] = 'Name is required.';
        if (!filter_var($registerEmail, FILTER_VALIDATE_EMAIL)) $registerErrors[] = 'A valid email is required.';
        if (strlen($password) < 6) $registerErrors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $registerErrors[] = 'Passwords do not match.';

        if (empty($registerErrors)) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$registerEmail]);
            if ($stmt->fetch()) {
                $registerErrors[] = 'An account with that email already exists.';
            }
        }

        if (empty($registerErrors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$registerName, $registerEmail, $hash, ROLE_CUSTOMER]);

            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            $_SESSION['role']    = ROLE_CUSTOMER;
            $_SESSION['name']    = $registerName;

            log_action((int) $_SESSION['user_id'], 'register_success', '');
            redirect('account/index.php');
        }
    }
}

// ---------------------------------------------------------------
// FORGOT PASSWORD - step 1: request the link
// ---------------------------------------------------------------
if ($action === 'forgot_request') {
    $activeTab = 'forgot';
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $forgotErrors[] = 'Invalid form submission. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $forgotSuccess = "If that email is registered, we've sent a reset link to it.";

        if ($user) {
            $rawToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);

            $stmt = $pdo->prepare(
                'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)'
            );
            $stmt->execute([$user['id'], $tokenHash, $expiresAt]);

            $resetLink = BASE_URL . '/auth/signin.php?tab=reset&token=' . $rawToken;

            $emailBody = '<p>We received a request to reset your password.</p>'
                . '<p><a href="' . htmlspecialchars($resetLink) . '">Click here to set a new password</a></p>'
                . '<p>This link expires in 1 hour. If you did not request this, you can ignore this email.</p>';

            $sent = send_email($email, 'Reset your password', $emailBody);
            log_action($user['id'], 'password_reset_requested', $sent ? 'email sent' : 'email failed - showing dev-mode link');

            if (!$sent) {
                $devFallbackLink = $resetLink;
            }
        }
    }
}

// ---------------------------------------------------------------
// FORGOT PASSWORD - step 2: set the new password
// ---------------------------------------------------------------
$resetTokenValid = false;
if ($token !== '') {
    $tokenHash = hash('sha256', $token);
    $stmt = $pdo->prepare('SELECT id, user_id, expires_at FROM password_resets WHERE token_hash = ?');
    $stmt->execute([$tokenHash]);
    $reset = $stmt->fetch();
    $resetTokenValid = $reset && strtotime($reset['expires_at']) > time();

    if (!$resetTokenValid) {
        $forgotErrors[] = 'This reset link is invalid or has expired. Please request a new one.';
        $activeTab = 'forgot';
    }
}

if ($action === 'forgot_reset') {
    $activeTab = 'reset';
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $forgotErrors[] = 'Invalid form submission. Please try again.';
    } elseif (!$resetTokenValid) {
        $forgotErrors[] = 'This reset link is invalid or has expired. Please request a new one.';
        $activeTab = 'forgot';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (strlen($password) < 6) $forgotErrors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $forgotErrors[] = 'Passwords do not match.';

        if (empty($forgotErrors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([$hash, $reset['user_id']]);

            $stmt = $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?');
            $stmt->execute([$reset['user_id']]);

            log_action((int) $reset['user_id'], 'password_reset_completed', '');
            redirect('auth/signin.php?tab=login&msg=' . urlencode('Password updated. Please log in.'));
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="auth-shell">
    <div class="auth-bg-grid"></div>
    <div class="auth-bg-orb auth-bg-orb--gold"></div>
    <div class="auth-bg-orb auth-bg-orb--forest"></div>

    <div class="auth-card">
        <div class="auth-tabs" role="tablist">
            <button type="button" class="auth-tab-btn <?= $activeTab === 'login' ? 'active' : '' ?>" data-tab="login">Login</button>
            <button type="button" class="auth-tab-btn <?= $activeTab === 'register' ? 'active' : '' ?>" data-tab="register">Create Account</button>
        </div>

        <?php if (!empty($_GET['msg'])): ?>
            <p class="form-success"><?= sanitize($_GET['msg']) ?></p>
        <?php endif; ?>

        <div class="auth-panels">
            <!-- LOGIN -->
            <div class="auth-panel <?= $activeTab === 'login' ? 'active' : '' ?>" data-panel="login">
                <?php foreach ($loginErrors as $error): ?>
                    <p class="form-error"><?= sanitize($error) ?></p>
                <?php endforeach; ?>

                <form method="post" action="<?= BASE_URL ?>/auth/signin.php">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="auth_action" value="login">

                    <label for="login_email">Email</label>
                    <input type="email" id="login_email" name="email" value="<?= sanitize($loginEmail) ?>" required autocomplete="email">

                    <label for="login_password">Password</label>
                    <input type="password" id="login_password" name="password" required autocomplete="current-password">

                    <button type="submit">Login</button>
                </form>

                <p class="auth-switch">
                    <a href="#" class="auth-switch-link" data-tab="forgot">Forgot password?</a>
                </p>
            </div>

            <!-- REGISTER -->
            <div class="auth-panel <?= $activeTab === 'register' ? 'active' : '' ?>" data-panel="register">
                <p class="auth-panel-title">Join the future of shopping</p>

                <?php foreach ($registerErrors as $error): ?>
                    <p class="form-error"><?= sanitize($error) ?></p>
                <?php endforeach; ?>

                <form method="post" action="<?= BASE_URL ?>/auth/signin.php">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="auth_action" value="register">

                    <label for="register_name">Full Name</label>
                    <input type="text" id="register_name" name="name" value="<?= sanitize($registerName) ?>" required autocomplete="name">

                    <label for="register_email">Email</label>
                    <input type="email" id="register_email" name="email" value="<?= sanitize($registerEmail) ?>" required autocomplete="email">

                    <label for="register_password">Password</label>
                    <input type="password" id="register_password" name="password" required minlength="6" autocomplete="new-password">

                    <label for="register_confirm">Confirm Password</label>
                    <input type="password" id="register_confirm" name="confirm_password" required minlength="6" autocomplete="new-password">

                    <button type="submit">Create Account</button>
                </form>
            </div>

            <!-- FORGOT PASSWORD (request link) -->
            <div class="auth-panel <?= $activeTab === 'forgot' ? 'active' : '' ?>" data-panel="forgot">
                <p class="auth-panel-title">Reset your password</p>

                <?php foreach ($forgotErrors as $error): ?>
                    <p class="form-error"><?= sanitize($error) ?></p>
                <?php endforeach; ?>

                <?php if ($forgotSuccess): ?>
                    <p class="form-success"><?= sanitize($forgotSuccess) ?></p>
                    <?php if ($devFallbackLink): ?>
                        <p class="muted">Email delivery isn't configured yet — link: <a href="<?= sanitize($devFallbackLink) ?>"><?= sanitize($devFallbackLink) ?></a></p>
                    <?php endif; ?>
                <?php endif; ?>

                <form method="post" action="<?= BASE_URL ?>/auth/signin.php">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="auth_action" value="forgot_request">

                    <label for="forgot_email">Email</label>
                    <input type="email" id="forgot_email" name="email" required autocomplete="email">

                    <button type="submit">Send Reset Link</button>
                </form>

                <p class="auth-switch"><a href="#" class="auth-switch-link" data-tab="login">Back to login</a></p>
            </div>

            <!-- RESET PASSWORD (from emailed link) -->
            <div class="auth-panel <?= $activeTab === 'reset' ? 'active' : '' ?>" data-panel="reset">
                <p class="auth-panel-title">Set a new password</p>

                <?php if ($resetTokenValid): ?>
                    <form method="post" action="<?= BASE_URL ?>/auth/signin.php">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="auth_action" value="forgot_reset">
                        <input type="hidden" name="token" value="<?= sanitize($token) ?>">

                        <label for="reset_password">New Password</label>
                        <input type="password" id="reset_password" name="password" required minlength="6" autocomplete="new-password">

                        <label for="reset_confirm">Confirm New Password</label>
                        <input type="password" id="reset_confirm" name="confirm_password" required minlength="6" autocomplete="new-password">

                        <button type="submit">Set New Password</button>
                    </form>
                <?php else: ?>
                    <p class="muted">Request a new reset link from the "Forgot password?" tab.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    function init() {
        var buttons = document.querySelectorAll('.auth-tab-btn, .auth-switch-link');
        var panels = document.querySelectorAll('.auth-panel');
        var currentName = 'login';

        var activePanel = document.querySelector('.auth-panel.active');
        if (activePanel && activePanel.dataset && activePanel.dataset.panel) {
            currentName = activePanel.dataset.panel;
        }

        function switchTo(name) {
            if (name === currentName) return;

            var oldPanel = document.querySelector('.auth-panel.active');
            var newPanel = null;

            panels.forEach(function (p) {
                if (p.dataset.panel === name) {
                    newPanel = p;
                }
            });

            if (!oldPanel || !newPanel || oldPanel === newPanel) return;

            oldPanel.classList.remove('active');
            newPanel.classList.add('active');
            currentName = name;
        }

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                if (btn.classList.contains('auth-switch-link')) e.preventDefault();
                var name = btn.dataset.tab;
                if (name) switchTo(name);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
