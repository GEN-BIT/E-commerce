<?php
/** Expects $p (a product row) in scope. Included from index.php, products/index.php,
 *  products/product.php (related), products/search.php, account/wishlist.php. */
$__onSale = !empty($p['compare_at_price']) && $p['compare_at_price'] > $p['price'];
?>
<div class="product-card">
    <?php if ($__onSale): ?><span class="badge badge-cancelled card-sale-badge">SALE</span><?php endif; ?>
    <a href="<?= BASE_URL ?>/products/product.php?id=<?= $p['id'] ?>">
        <?php if ($p['image']): ?>
            <img src="<?= UPLOAD_PRODUCTS_URL . sanitize($p['image']) ?>" alt="<?= sanitize($p['name']) ?>" width="160">
        <?php endif; ?>
        <h3><?= sanitize($p['name']) ?></h3>
    </a>
    <p>
        <?php if ($__onSale): ?>
            <span class="price-sale"><?= format_price((float) $p['price']) ?></span>
            <span class="price-compare-sm"><?= format_price((float) $p['compare_at_price']) ?></span>
        <?php else: ?>
            <?= format_price((float) $p['price']) ?>
        <?php endif; ?>
    </p>
    <p><?= $p['stock'] > 0 ? $p['stock'] . ' in stock' : 'Out of stock' ?></p>
</div>
