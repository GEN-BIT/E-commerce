<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$stmt = $pdo->query(
    'SELECT p.id, p.name, p.price, p.stock, p.is_active, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     ORDER BY p.created_at DESC'
);
$products = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Manage Products</h1>

<p><a href="<?= BASE_URL ?>/admin/products/add.php">+ Add Product</a></p>

<?php if (!empty($_GET['msg'])): ?>
    <p class="form-success"><?= sanitize($_GET['msg']) ?></p>
<?php endif; ?>

<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Active</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($products as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= sanitize($p['name']) ?></td>
            <td><?= sanitize($p['category_name']) ?></td>
            <td><?= format_price((float) $p['price']) ?></td>
            <td><?= (int) $p['stock'] ?></td>
            <td><?= bool_badge((bool) $p['is_active']) ?></td>
            <td>
                <a href="<?= BASE_URL ?>/admin/products/view.php?id=<?= $p['id'] ?>">View</a> |
                <a href="<?= BASE_URL ?>/admin/products/edit.php?id=<?= $p['id'] ?>">Edit</a> |
                <a href="<?= BASE_URL ?>/admin/products/delete.php?id=<?= $p['id'] ?>&csrf_token=<?= generate_csrf_token() ?>"
                   onclick="return confirm('Delete this product?')">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($products)): ?>
        <tr><td colspan="7">No products yet.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
