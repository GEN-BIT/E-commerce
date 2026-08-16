<nav class="navbar">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12.6 2H20a2 2 0 0 1 2 2v7.4a2 2 0 0 1-.59 1.41l-8.5 8.5a2 2 0 0 1-2.82 0l-7.4-7.4a2 2 0 0 1 0-2.82l8.5-8.5A2 2 0 0 1 12.6 2Z"
                  stroke="#C89B3C" stroke-width="1.8" stroke-linejoin="round"/>
            <circle cx="16.5" cy="7.5" r="1.6" fill="#C89B3C"/>
        </svg>
        <?= sanitize(get_setting('site_name', SITE_NAME)) ?>
    </a>

    <form action="<?= BASE_URL ?>/products/search.php" method="get" class="navbar-search">
        <input type="text" name="q" placeholder="Search products..." value="<?= sanitize($_GET['q'] ?? '') ?>">
        <button type="submit">Search</button>
    </form>

    <ul class="navbar-links">
        <li><a href="<?= BASE_URL ?>/products/index.php">Products</a></li>
        <li><a href="<?= BASE_URL ?>/products/deals.php">Deals</a></li>
        <li><a href="<?= BASE_URL ?>/products/best-sellers.php">Best Sellers</a></li>
        <li><a href="<?= BASE_URL ?>/cart/index.php">Cart</a></li>
        <?php if (is_logged_in()): ?>
            <li><a href="<?= BASE_URL ?>/account/wishlist.php">Wishlist</a></li>
            <li><a href="<?= BASE_URL ?>/account/index.php">My Account</a></li>
            <li><a href="<?= BASE_URL ?>/auth/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="<?= BASE_URL ?>/auth/signin.php" class="btn-signin">Sign In</a></li>
        <?php endif; ?>
    </ul>
</nav>
