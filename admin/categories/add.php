<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$errors = [];
$name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $errors[] = 'Name is required.';
        }

        if (empty($errors)) {
            $slug = slugify($name);

            $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                $errors[] = 'A category with a similar name already exists.';
            }
        }

        if (empty($errors)) {
            $imageFilename = null;
            if (!empty($_FILES['image']['name'])) {
                try {
                    $imageFilename = handle_image_upload($_FILES['image'], UPLOAD_CATEGORIES_DIR);
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare('INSERT INTO categories (name, slug, image) VALUES (?, ?, ?)');
                $stmt->execute([$name, $slug, $imageFilename]);

                redirect('admin/categories/index.php?msg=' . urlencode('Category added.'));
            }
        }
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<h1>Add Category</h1>

<?php foreach ($errors as $error): ?>
    <p class="form-error"><?= sanitize($error) ?></p>
<?php endforeach; ?>

<form method="post" action="" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= sanitize($name) ?>" required>

    <label for="image">Image (optional)</label>
    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">

    <button type="submit">Add Category</button>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
