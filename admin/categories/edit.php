<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    redirect('admin/categories/index.php?msg=' . urlencode('Category not found.'));
}

$errors = [];
$name = $category['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $errors[] = 'Name is required.';
        }

        $imageFilename = $category['image'];
        if (empty($errors) && !empty($_FILES['image']['name'])) {
            try {
                $uploaded = handle_image_upload($_FILES['image'], UPLOAD_CATEGORIES_DIR);
                if ($uploaded !== null) {
                    if ($imageFilename && is_file(UPLOAD_CATEGORIES_DIR . $imageFilename)) {
                        unlink(UPLOAD_CATEGORIES_DIR . $imageFilename);
                    }
                    $imageFilename = $uploaded;
                }
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (empty($errors)) {
            $slug = slugify($name);
            $stmt = $pdo->prepare('UPDATE categories SET name = ?, slug = ?, image = ? WHERE id = ?');
            $stmt->execute([$name, $slug, $imageFilename, $id]);

            redirect('admin/categories/index.php?msg=' . urlencode('Category updated.'));
        }
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Edit Category</h1>

<?php foreach ($errors as $error): ?>
    <p class="form-error"><?= sanitize($error) ?></p>
<?php endforeach; ?>

<?php if ($category['image']): ?>
    <img src="<?= UPLOAD_CATEGORIES_URL . sanitize($category['image']) ?>" alt="" width="120">
<?php endif; ?>

<form method="post" action="" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= sanitize($name) ?>" required>

    <label for="image">Replace Image (optional)</label>
    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">

    <button type="submit">Save Changes</button>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
