<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : null;

$sql = 'SELECT id, name, price, stock, image FROM products WHERE is_active = 1';
$params = [];

if ($q !== '') {
    $sql .= ' AND name LIKE ?';
    $params[] = '%' . $q . '%';
}
if ($categoryId) {
    $sql .= ' AND category_id = ?';
    $params[] = $categoryId;
}
$sql .= ' ORDER BY name LIMIT 50';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

foreach ($products as &$p) {
    $p['price'] = (float) $p['price'];
    $p['stock'] = (int) $p['stock'];
    $p['image_url'] = $p['image'] ? UPLOAD_PRODUCTS_URL . $p['image'] : null;
}

echo json_encode(['status' => 'ok', 'products' => $products]);
