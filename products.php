<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
    $item_condition = trim($_POST['item_condition'] ?? 'New');
    if (!in_array($item_condition, ['New', 'Pre-Owned'])) { $item_condition = 'New'; }
    $image_url = trim($_POST['image_url'] ?? '');
    if ($product_name !== '' && $category_id > 0 && $price > 0) {
        $stmt = $pdo->prepare("INSERT INTO products (category_id, product_name, description, price, stock_quantity, item_condition, image_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$category_id, $product_name, $description, $price, $stock_quantity, $item_condition, $image_url]);
        set_flash('green', 'Product added successfully.');
        header('Location: products.php');
        exit;
    }
}
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();
$products = $pdo->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC")->fetchAll();
$page_title = 'Manage Products';
include __DIR__ . '/../includes/header.php';
?>
<div class="section-header">
    <div>
        <h1 class="page-headline" style="font-size:40px;">Manage products</h1>
        <p>Add or review product data used by the storefront.</p>
    </div>
</div>
<div class="checkout-grid">
    <section class="form-card">
        <h2>Add product</h2>
        <form method="post">
            <div class="form-grid">
                <p><input class="input" type="text" name="product_name" placeholder="Product Name" required></p>
                <p>
                    <select class="input" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['id']; ?>"><?php echo h($cat['category_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p><input class="input" type="number" step="0.01" name="price" placeholder="Price" required></p>
                <p><input class="input" type="number" name="stock_quantity" placeholder="Stock Quantity" required></p>
                <p><select class="input" name="item_condition"><option value="New">New</option><option value="Pre-Owned">Pre-Owned</option></select></p>
            </div>
            <p><textarea class="input" name="description" placeholder="Description"></textarea></p>
            <p><input class="input" type="text" name="image_url" placeholder="Image URL or local path"></p>
            <button class="btn-dark" type="submit">Save product</button>
        </form>
    </section>
    <section class="table-card">
        <h2>Catalog overview</h2>
        <div class="table-scroll">
            <table class="w3-table-all">
                <tr><th>ID</th><th>Name</th><th>Category</th><th>Condition</th><th>Price</th><th>Stock</th></tr>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo (int)$product['id']; ?></td>
                    <td><?php echo h($product['product_name']); ?></td>
                    <td><?php echo h($product['category_name']); ?></td>
                    <td><?php echo h($product['item_condition'] ?? 'New'); ?></td>
                    <td>RM <?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo (int)$product['stock_quantity']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
