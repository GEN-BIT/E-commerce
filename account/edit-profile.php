<?php
require_once __DIR__ . '/../config/config.php';
require_login();

$stmt = $pdo->prepare('SELECT name, email, phone, address, country, profile_image FROM users WHERE id = ?');
$stmt->execute([current_user_id()]);
$user = $stmt->fetch();

$errors = [];
$success = null;

$name    = $user['name'];
$email   = $user['email'];
$phone   = $user['phone'] ?? '';
$address = $user['address'] ?? '';
$country = $user['country'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $country = trim($_POST['country'] ?? '');

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($name === '') $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
        if ($phone === '') $errors[] = 'Phone number is required.';
        if (!validate_phone_by_country($phone, $country)) $errors[] = 'Phone number does not match the format for ' . $country . '.';
        if ($address === '') $errors[] = 'Address is required.';
        if ($country === '') $errors[] = 'Country is required.';

        $profileImageFilename = $user['profile_image'];
        if (empty($errors) && !empty($_FILES['profile_image']['name'])) {
            try {
                $profileImageFilename = handle_image_upload($_FILES['profile_image'], UPLOAD_USERS_DIR);
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

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
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, phone = ?, address = ?, country = ?, profile_image = ? WHERE id = ?');
            $stmt->execute([$name, $email, $phone ?: null, $address ?: null, $country, $profileImageFilename, current_user_id()]);

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

<?php if ($user['profile_image']): ?>
    <p><img src="<?= sanitize(profile_image_url($user['profile_image'])) ?>" alt="" width="80" style="border-radius:50%;"></p>
<?php endif; ?>

<form method="post" action="" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= sanitize($name) ?>" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= sanitize($email) ?>" required>

    <label for="phone">Phone Number</label>
    <div class="phone-input-wrap">
        <span class="phone-code" id="profile_phone_code"><?= sanitize(get_country_phone_code($country ?: 'Rwanda')) ?></span>
        <input type="tel" id="phone" name="phone" value="<?= sanitize($phone) ?>" required autocomplete="tel">
    </div>
    <p class="muted phone-hint">Enter your local number after the country code.</p>

    <label for="address">Address</label>
    <textarea id="address" name="address" rows="3" required><?= sanitize($address) ?></textarea>

    <label for="country">Country</label>
    <select id="country" name="country" required>
        <option value="">Select your country</option>
        <?php foreach (get_countries() as $c): ?>
            <option value="<?= sanitize($c) ?>" <?= $country === $c ? 'selected' : '' ?>><?= sanitize($c) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="profile_image">Profile Picture</label>
    <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/webp">

    <h2 style="font-size: 1.1rem; margin-top: 1.5rem;">Change Password (optional)</h2>

    <label for="current_password">Current Password</label>
    <input type="password" id="current_password" name="current_password">

    <label for="new_password">New Password</label>
    <input type="password" id="new_password" name="new_password" minlength="6">

    <label for="confirm_password">Confirm New Password</label>
    <input type="password" id="confirm_password" name="confirm_password" minlength="6">

    <button type="submit">Save Changes</button>
</form>

<script>
(function () {
    var countryPhoneCodes = {
        'Afghanistan': '+93', 'Albania': '+355', 'Algeria': '+213', 'Andorra': '+376', 'Angola': '+244',
        'Argentina': '+54', 'Armenia': '+374', 'Australia': '+61', 'Austria': '+43', 'Azerbaijan': '+994',
        'Bahamas': '+1-242', 'Bahrain': '+973', 'Bangladesh': '+880', 'Barbados': '+1-246', 'Belarus': '+375',
        'Belgium': '+32', 'Belize': '+501', 'Benin': '+229', 'Bhutan': '+975', 'Bolivia': '+591',
        'Bosnia and Herzegovina': '+387', 'Botswana': '+267', 'Brazil': '+55', 'Brunei': '+673', 'Bulgaria': '+359',
        'Burkina Faso': '+226', 'Burundi': '+257', 'Cambodia': '+855', 'Cameroon': '+237', 'Canada': '+1',
        'Cape Verde': '+238', 'Central African Republic': '+236', 'Chad': '+235', 'Chile': '+56', 'China': '+86',
        'Colombia': '+57', 'Comoros': '+269', 'Congo': '+242', 'Costa Rica': '+506', 'Croatia': '+385',
        'Cuba': '+53', 'Cyprus': '+357', 'Czech Republic': '+420', 'Denmark': '+45', 'Djibouti': '+253',
        'Dominica': '+1-767', 'Dominican Republic': '+1-809', 'Ecuador': '+593', 'Egypt': '+20', 'El Salvador': '+503',
        'Equatorial Guinea': '+240', 'Eritrea': '+291', 'Estonia': '+372', 'Ethiopia': '+251', 'Fiji': '+679',
        'Finland': '+358', 'France': '+33', 'Gabon': '+241', 'Gambia': '+220', 'Georgia': '+995',
        'Germany': '+49', 'Ghana': '+233', 'Greece': '+30', 'Grenada': '+1-473', 'Guatemala': '+502',
        'Guinea': '+224', 'Guinea-Bissau': '+245', 'Guyana': '+592', 'Haiti': '+509', 'Honduras': '+504',
        'Hungary': '+36', 'Iceland': '+354', 'India': '+91', 'Indonesia': '+62', 'Iran': '+98',
        'Iraq': '+964', 'Ireland': '+353', 'Israel': '+972', 'Italy': '+39', 'Jamaica': '+1-876',
        'Japan': '+81', 'Jordan': '+962', 'Kazakhstan': '+7', 'Kenya': '+254', 'Kiribati': '+686',
        'Kuwait': '+965', 'Kyrgyzstan': '+996', 'Laos': '+856', 'Latvia': '+371', 'Lebanon': '+961',
        'Lesotho': '+266', 'Liberia': '+231', 'Libya': '+218', 'Liechtenstein': '+423', 'Lithuania': '+370',
        'Luxembourg': '+352', 'Madagascar': '+261', 'Malawi': '+265', 'Malaysia': '+60', 'Maldives': '+960',
        'Mali': '+223', 'Malta': '+356', 'Marshall Islands': '+692', 'Mauritania': '+222', 'Mauritius': '+230',
        'Mexico': '+52', 'Micronesia': '+691', 'Moldova': '+373', 'Monaco': '+377', 'Mongolia': '+976',
        'Montenegro': '+382', 'Morocco': '+212', 'Mozambique': '+258', 'Myanmar': '+95', 'Namibia': '+264',
        'Nauru': '+674', 'Nepal': '+977', 'Netherlands': '+31', 'New Zealand': '+64', 'Nicaragua': '+505',
        'Niger': '+227', 'Nigeria': '+234', 'North Korea': '+850', 'North Macedonia': '+389', 'Norway': '+47',
        'Oman': '+968', 'Pakistan': '+92', 'Palau': '+680', 'Palestine': '+970', 'Panama': '+507',
        'Papua New Guinea': '+675', 'Paraguay': '+595', 'Peru': '+51', 'Philippines': '+63', 'Poland': '+48',
        'Portugal': '+351', 'Qatar': '+974', 'Romania': '+40', 'Russia': '+7', 'Rwanda': '+250',
        'Saint Kitts and Nevis': '+1-869', 'Saint Lucia': '+1-758', 'Saint Vincent and the Grenadines': '+1-784', 'Samoa': '+685', 'San Marino': '+378',
        'Sao Tome and Principe': '+239', 'Saudi Arabia': '+966', 'Senegal': '+221', 'Serbia': '+381', 'Seychelles': '+248',
        'Sierra Leone': '+232', 'Singapore': '+65', 'Slovakia': '+421', 'Slovenia': '+386', 'Solomon Islands': '+677',
        'Somalia': '+252', 'South Africa': '+27', 'South Korea': '+82', 'South Sudan': '+211', 'Spain': '+34',
        'Sri Lanka': '+94', 'Sudan': '+249', 'Suriname': '+597', 'Swaziland': '+268', 'Sweden': '+46',
        'Switzerland': '+41', 'Syria': '+963', 'Taiwan': '+886', 'Tajikistan': '+992', 'Tanzania': '+255',
        'Thailand': '+66', 'Timor-Leste': '+670', 'Togo': '+228', 'Tonga': '+676', 'Trinidad and Tobago': '+1-868',
        'Tunisia': '+216', 'Turkey': '+90', 'Turkmenistan': '+993', 'Tuvalu': '+688', 'Uganda': '+256',
        'Ukraine': '+380', 'United Arab Emirates': '+971', 'United Kingdom': '+44', 'United States': '+1', 'Uruguay': '+598',
        'Uzbekistan': '+998', 'Vanuatu': '+678', 'Vatican City': '+379', 'Venezuela': '+58', 'Vietnam': '+84',
        'Yemen': '+967', 'Zambia': '+260', 'Zimbabwe': '+263'
    };

    var countrySelect = document.getElementById('country');
    var phoneCodeEl = document.getElementById('profile_phone_code');
    var phoneInput = document.getElementById('phone');

    if (countrySelect && phoneCodeEl && phoneInput) {
        function updatePhoneCode() {
            var country = countrySelect.value;
            phoneCodeEl.textContent = countryPhoneCodes[country] || '';
        }

        countrySelect.addEventListener('change', updatePhoneCode);
        updatePhoneCode();
    }
})();
</script>

<p><a href="<?= BASE_URL ?>/account/index.php">&larr; Back to account</a></p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
