<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name'] ?? '');
    if ($category_name !== '') {
        $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->execute([$category_name]);
        set_flash('green', 'Category added successfully.');
        header('Location: categories.php');
        exit;
    }
}
$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
$page_title = 'Manage Categories';
include __DIR__ . '/../includes/header.php';
?>
<div class="w3-row-padding">
    <div class="w3-half">
        <div class="w3-card w3-white w3-padding">
            <h2>Add Category</h2>
            <form method="post">
                <p><input class="w3-input w3-border" type="text" name="category_name" placeholder="Category Name" required></p>
                <p><button class="w3-button w3-black" type="submit">Add Category</button></p>
            </form>
        </div>
    </div>
    <div class="w3-half">
        <div class="table-card">
            <h2>Category List</h2>
            <table class="w3-table-all">
                <tr><th>ID</th><th>Category Name</th></tr>
                <?php foreach ($categories as $cat): ?>
                    <tr><td><?php echo (int)$cat['id']; ?></td><td><?php echo h($cat['category_name']); ?></td></tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
