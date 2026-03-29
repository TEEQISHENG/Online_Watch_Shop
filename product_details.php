<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? LIMIT 1");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    set_flash('red', 'Product not found.');
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    if (in_array($_SESSION['role'], ['admin', 'superadmin'])) {
        set_flash('red', 'Admin account cannot use customer cart.');
        header('Location: product_details.php?id=' . $id);
        exit;
    }
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($quantity < 1) $quantity = 1;
    if ($quantity > (int)$product['stock_quantity']) $quantity = (int)$product['stock_quantity'];
    $check = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
    $check->execute([$_SESSION['user_id'], $id]);
    $cart = $check->fetch();
    if ($cart) {
        $newQty = $cart['quantity'] + $quantity;
        $update = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $update->execute([$newQty, $cart['id']]);
    } else {
        $insert = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert->execute([$_SESSION['user_id'], $id, $quantity]);
    }
    set_flash('green', 'Product added to cart successfully.');
    header('Location: cart.php');
    exit;
}

$relatedStmt = $pdo->prepare("SELECT id, product_name, price, image_url FROM products WHERE is_active = 1 AND category_id = ? AND id != ? ORDER BY id ASC LIMIT 3");
$relatedStmt->execute([$product['category_id'], $id]);
$related = $relatedStmt->fetchAll();
$advantages = product_advantages($product);
$page_title = $product['product_name'];
include __DIR__ . '/includes/header.php';
?>
<div class="section-header">
    <div>
        <div class="detail-actions" style="justify-content:flex-start;margin-bottom:10px;">
            <span class="badge-pill"><?php echo h($product['category_name']); ?></span>
            <?php echo condition_badge($product['item_condition'] ?? 'New'); ?>
        </div>
        <h1 class="page-headline detail-title"><?php echo h($product['product_name']); ?></h1>
        <p><?php echo h(product_tagline($product['product_name'], $product['category_name'], $product['item_condition'] ?? 'New')); ?></p>
    </div>
</div>

<div class="detail-layout detail-layout-premium">
    <div class="detail-gallery card-box detail-gallery-premium">
        <img src="<?php echo product_image_url($product['image_url']); ?>" alt="<?php echo h($product['product_name']); ?>">
    </div>
    <div class="detail-card detail-card-premium">
        <div class="product-topline">
            <span class="badge-status">Stock <?php echo (int)$product['stock_quantity']; ?></span>
            <span class="badge-source">Tracked delivery</span>
        </div>
        <div class="detail-price-row">
            <div>
                <div class="detail-price">RM <?php echo number_format($product['price'], 2); ?></div>
                <div class="muted" style="margin-top:8px;"><?php echo h(condition_summary($product['item_condition'] ?? 'New')); ?></div>
            </div>
            <div class="badge-pill"><?php echo h($product['item_condition'] ?? 'New'); ?></div>
        </div>
        <p class="product-description detail-description"><?php echo h(product_story($product)); ?></p>
        <div class="detail-note" style="margin:16px 0 18px;">Customer fit: <?php echo h(product_fit_for($product)); ?></div>
        <div class="detail-spec-grid detail-spec-grid-premium" style="margin:18px 0 18px;">
            <?php foreach ($advantages as $spec): ?>
                <div class="spec-chip"><?php echo h($spec); ?></div>
            <?php endforeach; ?>
        </div>
        <form method="post" style="margin-top:22px;">
            <div class="detail-actions detail-actions-premium">
                <div style="width:130px;">
                    <label class="muted">Quantity</label>
                    <input type="number" class="input" name="quantity" value="1" min="1" max="<?php echo (int)$product['stock_quantity']; ?>">
                </div>
                <button type="submit" class="btn-dark">Add to cart</button>
                <a href="products.php" class="btn-soft">Back to shop</a>
            </div>
        </form>
        <div class="service-inline-grid">
            <div class="service-inline-card"><strong>Product value</strong><span><?php echo h($advantages[0]); ?></span></div>
            <div class="service-inline-card"><strong>Buyer benefit</strong><span><?php echo h($advantages[1]); ?></span></div>
            <div class="service-inline-card"><strong>Store support</strong><span>Secure checkout, profile access and tracked order updates after purchase.</span></div>
        </div>
    </div>
</div>

<?php if ($related): ?>
<div class="section-header">
    <div>
        <h2>You may also like</h2>
        <p>Additional picks from the same category.</p>
    </div>
</div>
<div class="product-grid product-grid-home">
<?php foreach ($related as $row): ?>
    <article class="product-card">
        <div class="product-media product-media-home"><img src="<?php echo product_image_url($row['image_url']); ?>" alt="<?php echo h($row['product_name']); ?>"></div>
        <div class="product-body">
            <h3 class="product-title"><?php echo h($row['product_name']); ?></h3>
            <div class="product-price">RM <?php echo number_format($row['price'], 2); ?></div>
            <a href="product_details.php?id=<?php echo (int)$row['id']; ?>" class="btn-dark">View details</a>
        </div>
    </article>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
