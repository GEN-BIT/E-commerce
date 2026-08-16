<?php
require_once __DIR__ . '/../config/config.php';

$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : null;
$categoryName = null;

if ($categoryId) {
    $stmt = $pdo->prepare('SELECT name FROM categories WHERE id = ?');
    $stmt->execute([$categoryId]);
    $categoryName = $stmt->fetchColumn();
}

$perPage = 12;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

if ($categoryId) {
    $countStmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM products WHERE is_active = 1 AND category_id = ?');
    $countStmt->execute([$categoryId]);
} else {
    $countStmt = $pdo->query('SELECT COUNT(*) AS cnt FROM products WHERE is_active = 1');
}
$totalProducts = (int) $countStmt->fetch()['cnt'];
$totalPages = max(1, (int) ceil($totalProducts / $perPage));

if ($categoryId) {
    $stmt = $pdo->prepare(
        "SELECT * FROM products WHERE is_active = 1 AND category_id = ?
         ORDER BY created_at DESC LIMIT $perPage OFFSET $offset"
    );
    $stmt->execute([$categoryId]);
} else {
    $stmt = $pdo->query(
        "SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT $perPage OFFSET $offset"
    );
}
$products = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h1><?= $categoryName ? sanitize($categoryName) : 'All Products' ?></h1>

<nav class="category-filter" aria-label="Category filter">
    <a href="<?= BASE_URL ?>/products/index.php" class="<?= !$categoryId ? 'active' : '' ?>">All</a>
    <?php foreach ($categories as $c): ?>
        <a href="<?= BASE_URL ?>/products/index.php?category=<?= $c['id'] ?>" class="<?= $categoryId === (int) $c['id'] ? 'active' : '' ?>">
            <?= sanitize($c['name']) ?>
        </a>
    <?php endforeach; ?>
    <a href="<?= BASE_URL ?>/products/search.php">Search</a>
</nav>

<?php if (!empty($_GET['msg'])): ?>
    <p class="form-success"><?= sanitize($_GET['msg']) ?></p>
<?php endif; ?>

<div class="product-grid">
    <?php foreach ($products as $p): ?>
        <?php include __DIR__ . '/../includes/product-card.php'; ?>
    <?php endforeach; ?>
    <?php if (empty($products)): ?>
        <p>No products found.</p>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
    <nav class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php
                $params = ['page' => $i];
                if ($categoryId) $params['category'] = $categoryId;
                $url = BASE_URL . '/products/index.php?' . http_build_query($params);
            ?>
            <a href="<?= $url ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
