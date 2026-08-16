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

<section class="search-hero">
    <div class="search-hero-grid"></div>
    <div class="search-hero-orb search-hero-orb--gold"></div>
    <div class="search-hero-orb search-hero-orb--forest"></div>

    <div class="search-hero-content">
        <h1>Search Products</h1>
        <p class="search-hero-sub">Explore our global catalog with instant results</p>

        <form method="get" action="" class="search-hero-form" role="search">
            <span class="search-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </span>
            <input type="text" name="q" value="<?= sanitize($q) ?>" placeholder="Search products, brands, categories..." class="search-hero-input" autocomplete="off" autofocus>
            <button type="submit" class="search-hero-submit">
                <span class="search-btn-text">Search</span>
            </button>
        </form>

        <?php if ($q !== ''): ?>
            <p class="search-results-meta"><?= count($products) ?> result(s) for "<strong><?= sanitize($q) ?></strong>"</p>
        <?php endif; ?>
    </div>
</section>

<?php if ($q !== ''): ?>
    <div class="site-main" style="padding-top: 0;">
        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <?php include __DIR__ . '/../includes/product-card.php'; ?>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
                <p class="search-empty">No products matched your search. Try a different keyword.</p>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="site-main" style="padding-top: 0;">
        <div class="search-placeholder">
            <div class="search-placeholder-icon">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <p>Start typing to discover products from our global catalog</p>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
