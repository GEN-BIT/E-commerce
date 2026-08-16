<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$errors = [];
$name = $description = '';
$price = $compare_at_price = $stock = '';
$category_id = null;

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $name             = trim($_POST['name'] ?? '');
        $description      = trim($_POST['description'] ?? '');
        $price            = $_POST['price'] ?? '';
        $compare_at_price = trim($_POST['compare_at_price'] ?? '');
        $stock            = $_POST['stock'] ?? '';
        $category_id      = (int) ($_POST['category_id'] ?? 0);

        if ($name === '') $errors[] = 'Name is required.';
        if (!is_numeric($price) || $price < 0) $errors[] = 'Price must be a valid number.';
        if ($compare_at_price !== '' && (!is_numeric($compare_at_price) || $compare_at_price <= $price)) {
            $errors[] = 'Sale "compare at" price must be a number greater than the price.';
        }
        if (!is_numeric($stock) || $stock < 0) $errors[] = 'Stock must be a valid number.';
        if ($category_id <= 0) $errors[] = 'Please choose a category.';

        $imageFilename = null;
        if (empty($errors) && !empty($_FILES['image']['name'])) {
            try {
                $imageFilename = handle_image_upload($_FILES['image'], UPLOAD_PRODUCTS_DIR);
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        $galleryFiles = [];
        if (empty($errors) && !empty($_FILES['gallery']['name'][0])) {
            $galleryFiles = normalize_multi_file_upload($_FILES['gallery']);
        }

        if (empty($errors)) {
            $slug = slugify($name) . '-' . uniqid();
            $stmt = $pdo->prepare(
                'INSERT INTO products (category_id, name, slug, description, price, compare_at_price, stock, image, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)'
            );
            $stmt->execute([
                $category_id, $name, $slug, $description, $price,
                $compare_at_price !== '' ? $compare_at_price : null, $stock, $imageFilename,
            ]);
            $productId = (int) $pdo->lastInsertId();

            $sort = 0;
            foreach ($galleryFiles as $file) {
                try {
                    $filename = handle_image_upload($file, UPLOAD_PRODUCTS_DIR);
                    if ($filename) {
                        $stmt = $pdo->prepare(
                            'INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)'
                        );
                        $stmt->execute([$productId, $filename, $sort++]);
                    }
                } catch (RuntimeException $e) {
                    // skip a bad gallery file rather than fail the whole product
                }
            }

            log_action(current_user_id(), 'product_created', "product_id={$productId}");
            redirect('admin/products/index.php?msg=' . urlencode('Product added.'));
        }
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Add Product</h1>

<?php foreach ($errors as $error): ?>
    <p class="form-error"><?= sanitize($error) ?></p>
<?php endforeach; ?>

<form method="post" action="" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= sanitize($name) ?>" required>

    <label for="category_id">Category</label>
    <select id="category_id" name="category_id" required>
        <option value="">-- Select --</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $category_id == $c['id'] ? 'selected' : '' ?>>
                <?= sanitize($c['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="4"><?= sanitize($description) ?></textarea>

    <label for="price">Price</label>
    <input type="number" id="price" name="price" step="0.01" min="0" value="<?= sanitize((string) $price) ?>" required>

    <label for="compare_at_price">Sale "Compare At" Price (optional)</label>
    <input type="number" id="compare_at_price" name="compare_at_price" step="0.01" min="0" value="<?= sanitize((string) $compare_at_price) ?>" placeholder="Leave blank if not on sale">

    <label for="stock">Stock</label>
    <input type="number" id="stock" name="stock" min="0" value="<?= sanitize((string) $stock) ?>" required>

    <label for="image">Main Image</label>
    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">

    <label for="gallery">Additional Gallery Images (optional, select multiple)</label>
    <input type="file" id="gallery" name="gallery[]" accept="image/jpeg,image/png,image/webp" multiple>

    <button type="submit">Add Product</button>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
