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
