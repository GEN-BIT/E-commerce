<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('admin/products/index.php?msg=' . urlencode('Product not found.'));
}

$errors = [];
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$name             = $product['name'];
$description      = $product['description'];
$price            = $product['price'];
$compare_at_price = $product['compare_at_price'];
$stock            = $product['stock'];
$category_id      = $product['category_id'];
$is_active        = $product['is_active'];

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
        $is_active        = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') $errors[] = 'Name is required.';
        if (!is_numeric($price) || $price < 0) $errors[] = 'Price must be a valid number.';
        if ($compare_at_price !== '' && (!is_numeric($compare_at_price) || $compare_at_price <= $price)) {
            $errors[] = 'Sale "compare at" price must be a number greater than the price.';
        }
        if (!is_numeric($stock) || $stock < 0) $errors[] = 'Stock must be a valid number.';
        if ($category_id <= 0) $errors[] = 'Please choose a category.';

        $imageFilename = $product['image'];
        if (empty($errors) && !empty($_FILES['image']['name'])) {
            try {
                $uploaded = handle_image_upload($_FILES['image'], UPLOAD_PRODUCTS_DIR);
                if ($uploaded !== null) {
                    if ($imageFilename && is_file(UPLOAD_PRODUCTS_DIR . $imageFilename)) {
                        unlink(UPLOAD_PRODUCTS_DIR . $imageFilename);
                    }
                    $imageFilename = $uploaded;
                }
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        $galleryFiles = [];
        if (empty($errors) && !empty($_FILES['gallery']['name'][0])) {
            $galleryFiles = normalize_multi_file_upload($_FILES['gallery']);
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                'UPDATE products
                 SET category_id = ?, name = ?, description = ?, price = ?, compare_at_price = ?, stock = ?, image = ?, is_active = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $category_id, $name, $description, $price,
                $compare_at_price !== '' ? $compare_at_price : null, $stock, $imageFilename, $is_active, $id,
            ]);

            $maxSort = (int) $pdo->query(
                'SELECT COALESCE(MAX(sort_order), -1) AS m FROM product_images WHERE product_id = ' . $id
            )->fetch()['m'];

            foreach ($galleryFiles as $file) {
                try {
                    $filename = handle_image_upload($file, UPLOAD_PRODUCTS_DIR);
                    if ($filename) {
                        $maxSort++;
                        $stmt = $pdo->prepare(
                            'INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)'
                        );
                        $stmt->execute([$id, $filename, $maxSort]);
                    }
                } catch (RuntimeException $e) {
                    // skip a bad gallery file rather than fail the whole save
                }
            }

            log_action(current_user_id(), 'product_updated', "product_id={$id}");
            redirect('admin/products/index.php?msg=' . urlencode('Product updated.'));
        }
    }
}

$galleryImages = get_product_images($id);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Edit Product</h1>

<?php if (!empty($_GET['msg'])): ?>
    <p class="form-success"><?= sanitize($_GET['msg']) ?></p>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <p class="form-error"><?= sanitize($error) ?></p>
<?php endforeach; ?>

<?php if ($product['image']): ?>
    <img src="<?= UPLOAD_PRODUCTS_URL . sanitize($product['image']) ?>" alt="" width="120">
<?php endif; ?>

<form method="post" action="" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= sanitize($name) ?>" required>

    <label for="category_id">Category</label>
    <select id="category_id" name="category_id" required>
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

    <label for="image">Replace Main Image (optional)</label>
    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">

    <label for="gallery">Add More Gallery Images (optional, select multiple)</label>
    <input type="file" id="gallery" name="gallery[]" accept="image/jpeg,image/png,image/webp" multiple>

    <label>
        <input type="checkbox" name="is_active" <?= $is_active ? 'checked' : '' ?>>
        Active (visible to customers)
    </label>

    <button type="submit">Save Changes</button>
</form>

<?php if (!empty($galleryImages)): ?>
    <h2>Gallery Images</h2>
    <div class="product-grid">
        <?php foreach ($galleryImages as $img): ?>
            <div class="product-card">
                <img src="<?= UPLOAD_PRODUCTS_URL . sanitize($img['image']) ?>" alt="" width="140">
                <p>
                    <a class="cart-item-remove"
                       href="<?= BASE_URL ?>/admin/products/delete-image.php?id=<?= $img['id'] ?>&product_id=<?= $id ?>&csrf_token=<?= generate_csrf_token() ?>"
                       onclick="return confirm('Remove this gallery image?')">Remove</a>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
