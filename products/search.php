<?php
require_once __DIR__ . '/../config/config.php';

$q = trim($_GET['q'] ?? '');
$products = [];

if ($q !== '') {
    $stmt = $pdo->prepare(
        "SELECT * FROM products WHERE is_active = 1 AND name LIKE ? ORDER BY name LIMIT 50"
    );
    $stmt->execute(['%' . $q . '%']);
    $products = $stmt->fetchAll();
}

include __DIR__ . '/../includes/header.php';
?>
<h1>Search Products</h1>

<form method="get" action="">
    <input type="text" name="q" value="<?= sanitize($q) ?>" placeholder="Search products...">
    <button type="submit">Search</button>
</form>

<?php if ($q !== ''): ?>
    <p><?= count($products) ?> result(s) for "<?= sanitize($q) ?>"</p>
<?php endif; ?>

<div class="product-grid">
    <?php foreach ($products as $p): ?>
        <?php include __DIR__ . '/../includes/product-card.php'; ?>
    <?php endforeach; ?>
    <?php if ($q !== '' && empty($products)): ?>
        <p>No products matched your search.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
