<?php
require_once __DIR__ . '/../config/config.php';

// Kept as its own route per the project's URL structure, but the listing
// logic lives in products/index.php (which already supports ?category=).
$categoryId = (int) ($_GET['id'] ?? 0);
redirect('products/index.php?category=' . $categoryId);
