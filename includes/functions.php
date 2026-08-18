<?php
/**
 * General helper functions
 * Stage: 2/3 - filled in as needed
 */

function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function format_price(float $amount): string
{
    return get_setting('currency_symbol', '$') . number_format($amount, 2);
}

function redirect(string $path): void
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string) $token);
}

function get_setting(string $key, string $default = ''): string
{
    global $pdo;

    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $rows = $pdo->query('SELECT `key`, `value` FROM settings')->fetchAll();
        foreach ($rows as $row) {
            $cache[$row['key']] = $row['value'];
        }
    }

    return $cache[$key] ?? $default;
}

function set_setting(string $key, string $value): void
{
    global $pdo;

    $stmt = $pdo->prepare(
        'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
    );
    $stmt->execute([$key, $value]);
}

/**
 * Normalizes a multi-file $_FILES['field'] (from <input multiple name="field[]">)
 * into a list of single-file arrays, each suitable for handle_image_upload().
 * Skips any slot where no file was actually selected.
 */
function normalize_multi_file_upload(array $filesField): array
{
    $result = [];
    $count = count($filesField['name'] ?? []);

    for ($i = 0; $i < $count; $i++) {
        if (($filesField['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $result[] = [
            'name'     => $filesField['name'][$i],
            'type'     => $filesField['type'][$i],
            'tmp_name' => $filesField['tmp_name'][$i],
            'error'    => $filesField['error'][$i],
            'size'     => $filesField['size'][$i],
        ];
    }

    return $result;
}

function get_product_images(int $productId): array
{
    global $pdo;

    $stmt = $pdo->prepare('SELECT id, image FROM product_images WHERE product_id = ? ORDER BY sort_order, id');
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Validates and moves an uploaded image into $targetDir.
 * Returns the new filename on success, null if no file was uploaded,
 * or throws RuntimeException on validation failure.
 */
function handle_image_upload(array $file, string $targetDir): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no file selected - not an error
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed with error code ' . $file['error']);
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('Image is too large (max 2MB).');
    }

    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        throw new RuntimeException('Only JPEG, PNG, or WEBP images are allowed.');
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    };

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
        throw new RuntimeException('Failed to save uploaded image.');
    }

    return $filename;
}

function status_badge(string $status): string
{
    return '<span class="badge badge-' . sanitize($status) . '">' . sanitize(ucfirst($status)) . '</span>';
}

function bool_badge(bool $value, string $trueLabel = 'Active', string $falseLabel = 'Inactive'): string
{
    return $value
        ? '<span class="badge badge-active">' . sanitize($trueLabel) . '</span>'
        : '<span class="badge badge-inactive">' . sanitize($falseLabel) . '</span>';
}

function profile_image_url(?string $filename): string
{
    if (!$filename) {
        return BASE_URL . '/assets/images/default-avatar.svg';
    }

    return UPLOAD_USERS_URL . sanitize($filename);
}

function get_countries(): array
{
    return [
        'Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Australia', 'Austria',
        'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bhutan',
        'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon',
        'Canada', 'Cape Verde', 'Central African Republic', 'Chad', 'Chile', 'China', 'Colombia', 'Comoros', 'Congo', 'Costa Rica',
        'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'Ecuador', 'Egypt',
        'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Fiji', 'Finland', 'France', 'Gabon', 'Gambia',
        'Georgia', 'Germany', 'Ghana', 'Greece', 'Grenada', 'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti',
        'Honduras', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy',
        'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia',
        'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Madagascar', 'Malawi', 'Malaysia',
        'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Mauritania', 'Mauritius', 'Mexico', 'Micronesia', 'Moldova', 'Monaco',
        'Mongolia', 'Montenegro', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'New Zealand',
        'Nicaragua', 'Niger', 'Nigeria', 'North Korea', 'North Macedonia', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Palestine',
        'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Poland', 'Portugal', 'Qatar', 'Romania', 'Russia',
        'Rwanda', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia',
        'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Korea', 'South Sudan',
        'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Swaziland', 'Sweden', 'Switzerland', 'Syria', 'Taiwan', 'Tajikistan',
        'Tanzania', 'Thailand', 'Timor-Leste', 'Togo', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Tuvalu',
        'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City', 'Venezuela',
        'Vietnam', 'Yemen', 'Zambia', 'Zimbabwe'
    ];
}

function get_country_phone_code(string $country): string
{
    $map = [
        'Afghanistan' => '+93', 'Albania' => '+355', 'Algeria' => '+213', 'Andorra' => '+376', 'Angola' => '+244',
        'Argentina' => '+54', 'Armenia' => '+374', 'Australia' => '+61', 'Austria' => '+43', 'Azerbaijan' => '+994',
        'Bahamas' => '+1-242', 'Bahrain' => '+973', 'Bangladesh' => '+880', 'Barbados' => '+1-246', 'Belarus' => '+375',
        'Belgium' => '+32', 'Belize' => '+501', 'Benin' => '+229', 'Bhutan' => '+975', 'Bolivia' => '+591',
        'Bosnia and Herzegovina' => '+387', 'Botswana' => '+267', 'Brazil' => '+55', 'Brunei' => '+673', 'Bulgaria' => '+359',
        'Burkina Faso' => '+226', 'Burundi' => '+257', 'Cambodia' => '+855', 'Cameroon' => '+237', 'Canada' => '+1',
        'Cape Verde' => '+238', 'Central African Republic' => '+236', 'Chad' => '+235', 'Chile' => '+56', 'China' => '+86',
        'Colombia' => '+57', 'Comoros' => '+269', 'Congo' => '+242', 'Costa Rica' => '+506', 'Croatia' => '+385',
        'Cuba' => '+53', 'Cyprus' => '+357', 'Czech Republic' => '+420', 'Denmark' => '+45', 'Djibouti' => '+253',
        'Dominica' => '+1-767', 'Dominican Republic' => '+1-809', 'Ecuador' => '+593', 'Egypt' => '+20', 'El Salvador' => '+503',
        'Equatorial Guinea' => '+240', 'Eritrea' => '+291', 'Estonia' => '+372', 'Ethiopia' => '+251', 'Fiji' => '+679',
        'Finland' => '+358', 'France' => '+33', 'Gabon' => '+241', 'Gambia' => '+220', 'Georgia' => '+995',
        'Germany' => '+49', 'Ghana' => '+233', 'Greece' => '+30', 'Grenada' => '+1-473', 'Guatemala' => '+502',
        'Guinea' => '+224', 'Guinea-Bissau' => '+245', 'Guyana' => '+592', 'Haiti' => '+509', 'Honduras' => '+504',
        'Hungary' => '+36', 'Iceland' => '+354', 'India' => '+91', 'Indonesia' => '+62', 'Iran' => '+98',
        'Iraq' => '+964', 'Ireland' => '+353', 'Israel' => '+972', 'Italy' => '+39', 'Jamaica' => '+1-876',
        'Japan' => '+81', 'Jordan' => '+962', 'Kazakhstan' => '+7', 'Kenya' => '+254', 'Kiribati' => '+686',
        'Kuwait' => '+965', 'Kyrgyzstan' => '+996', 'Laos' => '+856', 'Latvia' => '+371', 'Lebanon' => '+961',
        'Lesotho' => '+266', 'Liberia' => '+231', 'Libya' => '+218', 'Liechtenstein' => '+423', 'Lithuania' => '+370',
        'Luxembourg' => '+352', 'Madagascar' => '+261', 'Malawi' => '+265', 'Malaysia' => '+60', 'Maldives' => '+960',
        'Mali' => '+223', 'Malta' => '+356', 'Marshall Islands' => '+692', 'Mauritania' => '+222', 'Mauritius' => '+230',
        'Mexico' => '+52', 'Micronesia' => '+691', 'Moldova' => '+373', 'Monaco' => '+377', 'Mongolia' => '+976',
        'Montenegro' => '+382', 'Morocco' => '+212', 'Mozambique' => '+258', 'Myanmar' => '+95', 'Namibia' => '+264',
        'Nauru' => '+674', 'Nepal' => '+977', 'Netherlands' => '+31', 'New Zealand' => '+64', 'Nicaragua' => '+505',
        'Niger' => '+227', 'Nigeria' => '+234', 'North Korea' => '+850', 'North Macedonia' => '+389', 'Norway' => '+47',
        'Oman' => '+968', 'Pakistan' => '+92', 'Palau' => '+680', 'Palestine' => '+970', 'Panama' => '+507',
        'Papua New Guinea' => '+675', 'Paraguay' => '+595', 'Peru' => '+51', 'Philippines' => '+63', 'Poland' => '+48',
        'Portugal' => '+351', 'Qatar' => '+974', 'Romania' => '+40', 'Russia' => '+7', 'Rwanda' => '+250',
        'Saint Kitts and Nevis' => '+1-869', 'Saint Lucia' => '+1-758', 'Saint Vincent and the Grenadines' => '+1-784', 'Samoa' => '+685', 'San Marino' => '+378',
        'Sao Tome and Principe' => '+239', 'Saudi Arabia' => '+966', 'Senegal' => '+221', 'Serbia' => '+381', 'Seychelles' => '+248',
        'Sierra Leone' => '+232', 'Singapore' => '+65', 'Slovakia' => '+421', 'Slovenia' => '+386', 'Solomon Islands' => '+677',
        'Somalia' => '+252', 'South Africa' => '+27', 'South Korea' => '+82', 'South Sudan' => '+211', 'Spain' => '+34',
        'Sri Lanka' => '+94', 'Sudan' => '+249', 'Suriname' => '+597', 'Swaziland' => '+268', 'Sweden' => '+46',
        'Switzerland' => '+41', 'Syria' => '+963', 'Taiwan' => '+886', 'Tajikistan' => '+992', 'Tanzania' => '+255',
        'Thailand' => '+66', 'Timor-Leste' => '+670', 'Togo' => '+228', 'Tonga' => '+676', 'Trinidad and Tobago' => '+1-868',
        'Tunisia' => '+216', 'Turkey' => '+90', 'Turkmenistan' => '+993', 'Tuvalu' => '+688', 'Uganda' => '+256',
        'Ukraine' => '+380', 'United Arab Emirates' => '+971', 'United Kingdom' => '+44', 'United States' => '+1', 'Uruguay' => '+598',
        'Uzbekistan' => '+998', 'Vanuatu' => '+678', 'Vatican City' => '+379', 'Venezuela' => '+58', 'Vietnam' => '+84',
        'Yemen' => '+967', 'Zambia' => '+260', 'Zimbabwe' => '+263'
    ];

    return $map[$country] ?? '';
}

function get_country_phone_pattern(string $country): string
{
    $patterns = [
        'Afghanistan' => '/^[7][0-9]{8}$/', 'Albania' => '/^[6][0-9]{8}$/', 'Algeria' => '/^[5-7][0-9]{8}$/', 'Andorra' => '/^[3-6][0-9]{5}$/', 'Angola' => '/^[9][0-9]{8}$/',
        'Argentina' => '/^[9][0-9]{9}$/', 'Armenia' => '/^[3-8][0-9]{7}$/', 'Australia' => '/^[4-5][0-9]{8}$/', 'Austria' => '/^[6][0-9]{9}$/', 'Azerbaijan' => '/^[5][0-9]{8}$/',
        'Bahamas' => '/^[2-5][0-9]{7}$/', 'Bahrain' => '/^[3][0-9]{7}$/', 'Bangladesh' => '/^[1][0-9]{9}$/', 'Barbados' => '/^[2-5][0-9]{7}$/', 'Belarus' => '/^[2-9][0-9]{8}$/',
        'Belgium' => '/^[4-9][0-9]{8}$/', 'Belize' => '/^[6][0-9]{7}$/', 'Benin' => '/^[9][0-9]{8}$/', 'Bhutan' => '/^[1-7][0-9]{6}$/', 'Bolivia' => '/^[6-7][0-9]{7}$/',
        'Bosnia and Herzegovina' => '/^[3][0-9]{8}$/', 'Botswana' => '/^[7][0-9]{7}$/', 'Brazil' => '/^[1-9][0-9]{8,9}$/', 'Brunei' => '/^[7-8][0-9]{6}$/', 'Bulgaria' => '/^[8][0-9]{8}$/',
        'Burkina Faso' => '/^[6-7][0-9]{7}$/', 'Burundi' => '/^[7][0-9]{7}$/', 'Cambodia' => '/^[1][0-9]{8,9}$/', 'Cameroon' => '/^[6][0-9]{8}$/', 'Canada' => '/^[2-9][0-9]{9}$/',
        'Cape Verde' => '/^[9][0-9]{7}$/', 'Central African Republic' => '/^[7][0-9]{7}$/', 'Chad' => '/^[6-7][0-9]{7}$/', 'Chile' => '/^[9][0-9]{8}$/', 'China' => '/^[1][0-9]{10}$/',
        'Colombia' => '/^[3][0-9]{9}$/', 'Comoros' => '/^[3][0-9]{7}$/', 'Congo' => '/^[0-6][0-9]{7}$/', 'Costa Rica' => '/^[8][0-9]{7}$/', 'Croatia' => '/^[9][0-9]{8}$/',
        'Cuba' => '/^[5][0-9]{7}$/', 'Cyprus' => '/^[9][0-9]{7}$/', 'Czech Republic' => '/^[6-7][0-9]{8}$/', 'Denmark' => '/^[2-9][0-9]{7}$/', 'Djibouti' => '/^[7][0-9]{7}$/',
        'Dominica' => '/^[2-5][0-9]{7}$/', 'Dominican Republic' => '/^[8][0-9]{8}$/', 'Ecuador' => '/^[9][0-9]{8}$/', 'Egypt' => '/^[1][0-9]{9}$/', 'El Salvador' => '/^[7][0-9]{7}$/',
        'Equatorial Guinea' => '/^[2][0-9]{8}$/', 'Eritrea' => '/^[7][0-9]{7}$/', 'Estonia' => '/^[5][0-9]{7,8}$/', 'Ethiopia' => '/^[9][0-9]{8}$/', 'Fiji' => '/^[7][0-9]{6}$/',
        'Finland' => '/^[4][0-9]{8,9}$/', 'France' => '/^[6-7][0-9]{8}$/', 'Gabon' => '/^[0-7][0-9]{7}$/', 'Gambia' => '/^[7][0-9]{7}$/', 'Georgia' => '/^[5][0-9]{8}$/',
        'Germany' => '/^[1][0-9]{10}$/', 'Ghana' => '/^[2][0-9]{8}$/', 'Greece' => '/^[6][0-9]{9}$/', 'Grenada' => '/^[4][0-9]{7}$/', 'Guatemala' => '/^[4][0-9]{7}$/',
        'Guinea' => '/^[6][0-9]{8}$/', 'Guinea-Bissau' => '/^[9][0-9]{7}$/', 'Guyana' => '/^[6][0-9]{6}$/', 'Haiti' => '/^[3][0-9]{7}$/', 'Honduras' => '/^[9][0-9]{7}$/',
        'Hungary' => '/^[2][0-9]{8}$/', 'Iceland' => '/^[6-9][0-9]{7}$/', 'India' => '/^[6-9][0-9]{9}$/', 'Indonesia' => '/^[8][0-9]{9,10}$/', 'Iran' => '/^[9][0-9]{9}$/',
        'Iraq' => '/^[7][0-9]{9}$/', 'Ireland' => '/^[8][0-9]{8}$/', 'Israel' => '/^[5][0-9]{8}$/', 'Italy' => '/^[3][0-9]{9}$/', 'Jamaica' => '/^[2-5][0-9]{7}$/',
        'Japan' => '/^[7-9][0-9]{9}$/', 'Jordan' => '/^[7][0-9]{8}$/', 'Kazakhstan' => '/^[7][0-9]{9}$/', 'Kenya' => '/^[1][0-9]{9}$/', 'Kiribati' => '/^[7][0-9]{5}$/',
        'Kuwait' => '/^[5][0-9]{7}$/', 'Kyrgyzstan' => '/^[5][0-9]{8}$/', 'Laos' => '/^[2][0-9]{8}$/', 'Latvia' => '/^[2][0-9]{8}$/', 'Lebanon' => '/^[3-9][0-9]{7}$/',
        'Lesotho' => '/^[5-8][0-9]{7}$/', 'Liberia' => '/^[4][0-9]{8}$/', 'Libya' => '/^[9][0-9]{8}$/', 'Liechtenstein' => '/^[6][0-9]{7}$/', 'Lithuania' => '/^[6][0-9]{8}$/',
        'Luxembourg' => '/^[6][0-9]{8}$/', 'Madagascar' => '/^[3][0-9]{8}$/', 'Malawi' => '/^[7-8][0-9]{7}$/', 'Malaysia' => '/^[1][0-9]{9}$/', 'Maldives' => '/^[7-9][0-9]{7}$/',
        'Mali' => '/^[6-7][0-9]{7}$/', 'Malta' => '/^[7][0-9]{8}$/', 'Marshall Islands' => '/^[2-8][0-9]{7}$/', 'Mauritania' => '/^[2][0-9]{8}$/', 'Mauritius' => '/^[2][0-9]{8}$/',
        'Mexico' => '/^[1-9][0-9]{9}$/', 'Micronesia' => '/^[3-9][0-9]{6}$/', 'Moldova' => '/^[6][0-9]{7,8}$/', 'Monaco' => '/^[4][0-9]{7}$/', 'Mongolia' => '/^[8][0-9]{7}$/',
        'Montenegro' => '/^[6][0-9]{7}$/', 'Morocco' => '/^[6-7][0-9]{8}$/', 'Mozambique' => '/^[8][0-9]{8}$/', 'Myanmar' => '/^[9][0-9]{7,8}$/', 'Namibia' => '/^[6][0-9]{8}$/',
        'Nauru' => '/^[5-6][0-9]{5}$/', 'Nepal' => '/^[9][0-9]{9}$/', 'Netherlands' => '/^[6][0-9]{8}$/', 'New Zealand' => '/^[2-9][0-9]{8}$/', 'Nicaragua' => '/^[8][0-9]{7}$/',
        'Niger' => '/^[9][0-9]{7}$/', 'Nigeria' => '/^[7-9][0-9]{9}$/', 'North Korea' => '/^[1][0-9]{9}$/', 'North Macedonia' => '/^[7][0-9]{7}$/', 'Norway' => '/^[4-9][0-9]{7}$/',
        'Oman' => '/^[9][0-9]{8}$/', 'Pakistan' => '/^[3][0-9]{9}$/', 'Palau' => '/^[6][0-9]{7}$/', 'Palestine' => '/^[5][0-9]{8}$/', 'Panama' => '/^[5-8][0-9]{7}$/',
        'Papua New Guinea' => '/^[7][0-9]{7}$/', 'Paraguay' => '/^[9][0-9]{8}$/', 'Peru' => '/^[9][0-9]{8}$/', 'Philippines' => '/^[9][0-9]{9}$/', 'Poland' => '/^[5-9][0-9]{8}$/',
        'Portugal' => '/^[9][0-9]{8}$/', 'Qatar' => '/^[3][0-9]{7}$/', 'Romania' => '/^[7][0-9]{8}$/', 'Russia' => '/^[9][0-9]{9}$/', 'Rwanda' => '/^[7][0-9]{8}$/',
        'Saint Kitts and Nevis' => '/^[5-6][0-9]{7}$/', 'Saint Lucia' => '/^[5][0-9]{7}$/', 'Saint Vincent and the Grenadines' => '/^[4][0-9]{7}$/', 'Samoa' => '/^[7-8][0-9]{6}$/', 'San Marino' => '/^[5-7][0-9]{7}$/',
        'Sao Tome and Principe' => '/^[9][0-9]{7}$/', 'Saudi Arabia' => '/^[5][0-9]{8}$/', 'Senegal' => '/^[7][0-9]{8}$/', 'Serbia' => '/^[6][0-9]{8}$/', 'Seychelles' => '/^[2][0-9]{7}$/',
        'Sierra Leone' => '/^[2][0-9]{7}$/', 'Singapore' => '/^[8-9][0-9]{7}$/', 'Slovakia' => '/^[9][0-9]{8}$/', 'Slovenia' => '/^[3][0-9]{8}$/', 'Solomon Islands' => '/^[7][0-9]{5}$/',
        'Somalia' => '/^[6-7][0-9]{7}$/', 'South Africa' => '/^[7-8][0-9]{8}$/', 'South Korea' => '/^[1][0-9]{9}$/', 'South Sudan' => '/^[9][0-9]{8}$/', 'Spain' => '/^[6-9][0-9]{8}$/',
        'Sri Lanka' => '/^[7][0-9]{8}$/', 'Sudan' => '/^[9][0-9]{8}$/', 'Suriname' => '/^[7][0-9]{7}$/', 'Swaziland' => '/^[7-8][0-9]{7}$/', 'Sweden' => '/^[7][0-9]{8}$/',
        'Switzerland' => '/^[7][0-9]{8}$/', 'Syria' => '/^[9][0-9]{8}$/', 'Taiwan' => '/^[9][0-9]{8}$/', 'Tajikistan' => '/^[9][0-9]{8}$/', 'Tanzania' => '/^[6-7][0-9]{8}$/',
        'Thailand' => '/^[8-9][0-9]{8}$/', 'Timor-Leste' => '/^[7][0-9]{7}$/', 'Togo' => '/^[9][0-9]{8}$/', 'Tonga' => '/^[7][0-9]{5}$/', 'Trinidad and Tobago' => '/^[6][0-9]{7}$/',
        'Tunisia' => '/^[2-9][0-9]{7}$/', 'Turkey' => '/^[5][0-9]{9}$/', 'Turkmenistan' => '/^[6][0-9]{8}$/', 'Tuvalu' => '/^[7][0-9]{5}$/', 'Uganda' => '/^[7][0-9]{8}$/',
        'Ukraine' => '/^[3-9][0-9]{8}$/', 'United Arab Emirates' => '/^[5][0-9]{8}$/', 'United Kingdom' => '/^[7][0-9]{9}$/', 'United States' => '/^[2-9][0-9]{9}$/', 'Uruguay' => '/^[9][0-9]{7}$/',
        'Uzbekistan' => '/^[9][0-9]{8}$/', 'Vanuatu' => '/^[5][0-9]{6}$/', 'Vatican City' => '/^[3][0-9]{7}$/', 'Venezuela' => '/^[4][0-9]{9}$/', 'Vietnam' => '/^[9][0-9]{8}$/',
        'Yemen' => '/^[7][0-9]{8}$/', 'Zambia' => '/^[9][0-9]{8}$/', 'Zimbabwe' => '/^[7][0-9]{8}$/'
    ];

    return $patterns[$country] ?? '/^[0-9]{7,15}$/';
}

function validate_phone_by_country(string $phone, string $country): bool
{
    $code = get_country_phone_code($country);
    $pattern = get_country_phone_pattern($country);

    $digits = preg_replace('/[^0-9]/', '', $phone);

    if ($code === '') {
        return (bool) preg_match($pattern, $digits);
    }

    $codeDigits = preg_replace('/[^0-9]/', '', $code);

    if (strpos($digits, $codeDigits) !== 0) {
        return false;
    }

    $localNumber = substr($digits, strlen($codeDigits));

    return (bool) preg_match($pattern, $localNumber);
}

function format_phone_display(string $phone, string $country): string
{
    $code = get_country_phone_code($country);
    if ($code === '') {
        return sanitize($phone);
    }

    $codeDigits = preg_replace('/[^0-9]/', '', $code);
    $digits = preg_replace('/[^0-9]/', '', $phone);

    if (strpos($digits, $codeDigits) === 0) {
        $local = substr($digits, strlen($codeDigits));
        return sanitize($code . ' ' . $local);
    }

    return sanitize($phone);
}
