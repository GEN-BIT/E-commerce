<?php
require_once __DIR__ . '/config/config.php';

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$featured = $pdo->query(
    'SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8'
)->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <h1><?= sanitize(get_setting('site_name', SITE_NAME)) ?></h1>
    <p>A small, well-kept catalog — browse what's in stock and check out in a couple of clicks.</p>
    <a class="btn-cta" href="<?= BASE_URL ?>/products/index.php">Shop All Products</a>
</section>

<?php if (!empty($categories)): ?>
    <h2>Shop by Category</h2>
    <div class="category-chips">
        <?php foreach ($categories as $c): ?>
            <a class="category-chip" href="<?= BASE_URL ?>/products/index.php?category=<?= $c['id'] ?>">
                <?= sanitize($c['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h2>New Arrivals</h2>
<div class="product-grid">
    <?php foreach ($featured as $p): ?>
        <?php include __DIR__ . '/includes/product-card.php'; ?>
    <?php endforeach; ?>
    <?php if (empty($featured)): ?>
        <p>No products yet — add some from the admin panel.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
