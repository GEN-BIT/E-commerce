<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$categories = $pdo->query(
    'SELECT c.id, c.name, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name'
)->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Manage Categories</h1>

<p><a href="<?= BASE_URL ?>/admin/categories/add.php">+ Add Category</a></p>

<?php if (!empty($_GET['msg'])): ?>
    <p class="form-success"><?= sanitize($_GET['msg']) ?></p>
<?php endif; ?>

<table border="1" cellpadding="6" cellspacing="0">
    <thead><tr><th>ID</th><th>Name</th><th>Products</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($categories as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= sanitize($c['name']) ?></td>
            <td><?= (int) $c['product_count'] ?></td>
            <td>
                <a href="<?= BASE_URL ?>/admin/categories/edit.php?id=<?= $c['id'] ?>">Edit</a> |
                <?php if ($c['product_count'] == 0): ?>
                    <a href="<?= BASE_URL ?>/admin/categories/delete.php?id=<?= $c['id'] ?>&csrf_token=<?= generate_csrf_token() ?>"
                       onclick="return confirm('Delete this category?')">Delete</a>
                <?php else: ?>
                    <span title="Move or delete its products first">Delete</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($categories)): ?>
        <tr><td colspan="4">No categories yet.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
