<nav class="navbar">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
        <svg width="22" height="22" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <defs>
            <linearGradient id="navIconGrad" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0" stop-color="#2F5D50"/>
              <stop offset="1" stop-color="#1E3D34"/>
            </linearGradient>
          </defs>
          <path d="M32 4L56 18v28L32 60 8 46V18L32 4z" fill="url(#navIconGrad)" stroke="#C89B3C" stroke-width="5" stroke-linejoin="round"/>
          <path d="M20 22l4-8M44 22l-4-8" stroke="#C89B3C" stroke-width="4" stroke-linecap="round"/>
          <path d="M26 26h12l-2 10H28l-2-10z" fill="#C89B3C"/>
          <path d="M28 26v-4c0-2.2 1.8-4 4-4h0c2.2 0 4 1.8 4 4v4" stroke="#1E3D34" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="32" cy="36" r="3" fill="#1E3D34"/>
          <path d="M16 16l-4-4M48 16l4-4M16 48l-4 4M48 48l4 4" stroke="#2F5D50" stroke-width="3.5" stroke-linecap="round" opacity=".7"/>
        </svg>
        <?= sanitize(get_setting('site_name', SITE_NAME)) ?>
    </a>

    <ul class="navbar-links">
        <li><a href="<?= BASE_URL ?>/products/index.php">Products</a></li>
        <li><a href="<?= BASE_URL ?>/products/deals.php">Deals</a></li>
        <li><a href="<?= BASE_URL ?>/products/best-sellers.php">Best Sellers</a></li>
        <li><a href="<?= BASE_URL ?>/products/search.php" class="nav-search-link" aria-label="Search">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </a></li>
        <li><a href="<?= BASE_URL ?>/cart/index.php">Cart</a></li>
        <?php if (is_logged_in()): ?>
            <?php
                $navUser = $pdo->prepare('SELECT profile_image FROM users WHERE id = ?');
                $navUser->execute([current_user_id()]);
                $navProfile = $navUser->fetchColumn();
            ?>
            <li><a href="<?= BASE_URL ?>/account/wishlist.php">Wishlist</a></li>
            <li>
                <a href="<?= BASE_URL ?>/account/index.php" style="display:inline-flex;align-items:center;gap:.4rem;">
                    <img class="navbar-avatar" src="<?= sanitize(profile_image_url($navProfile ?: null)) ?>" alt="" width="28" height="28">
                    My Account
                </a>
            </li>
            <li><a href="<?= BASE_URL ?>/auth/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="<?= BASE_URL ?>/auth/signin.php" class="btn-signin">Sign In</a></li>
        <?php endif; ?>
    </ul>
</nav>
