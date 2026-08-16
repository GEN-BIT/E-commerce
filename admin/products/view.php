<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category_name
     FROM products p JOIN categories c ON c.id = p.category_id
     WHERE p.id = ?'
);
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('admin/products/index.php?msg=' . urlencode('Product not found.'));
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1><?= sanitize($product['name']) ?></h1>

<?php if ($product['image']): ?>
    <img src="<?= UPLOAD_PRODUCTS_URL . sanitize($product['image']) ?>" alt="<?= sanitize($product['description'] ?? $product['name']) ?>" title="<?= sanitize($product['description'] ?? $product['name']) ?>" width="200">
<?php endif; ?>

<ul>
    <li>Category: <?= sanitize($product['category_name']) ?></li>
    <li>Price: <?= format_price((float) $product['price']) ?></li>
    <li>Stock: <?= (int) $product['stock'] ?></li>
    <li>Active: <?= bool_badge((bool) $product['is_active']) ?></li>
    <li>Description: <?= sanitize($product['description'] ?? '') ?></li>
</ul>

<p>
    <a href="<?= BASE_URL ?>/admin/products/edit.php?id=<?= $product['id'] ?>">Edit</a> |
    <a href="<?= BASE_URL ?>/admin/products/index.php">Back to list</a>
</p>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
