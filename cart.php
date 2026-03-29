<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();
if ($_SESSION['role'] !== 'customer') {
    header('Location: admin/dashboard.php');
    exit;
}

if (isset($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
    $stmt->execute([$removeId, $_SESSION['user_id']]);
    set_flash('green', 'Item removed from cart.');
    header('Location: cart.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] ?? [] as $cartId => $qty) {
        $qty = max(1, (int)$qty);
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$qty, (int)$cartId, $_SESSION['user_id']]);
    }
    set_flash('green', 'Cart updated successfully.');
    header('Location: cart.php');
    exit;
}

$stmt = $pdo->prepare("SELECT ci.id, ci.quantity, p.product_name, p.price, p.image_url, p.stock_quantity FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.user_id = ? ORDER BY ci.id DESC");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();
$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}
$page_title = 'Shopping Cart';
include __DIR__ . '/includes/header.php';
?>
<div class="section-header">
    <div>
        <h1 class="page-headline" style="font-size:40px;">Shopping cart</h1>
        <p>Review selected products before moving to checkout.</p>
    </div>
    <a class="btn-soft" href="products.php">Continue shopping</a>
</div>

<div class="checkout-grid">
    <section class="table-card">
        <h2>Cart items</h2>
        <?php if (!$items): ?>
            <p class="muted">Your cart is empty.</p>
        <?php else: ?>
            <form method="post">
                <div class="table-scroll">
                    <table class="w3-table-all">
                        <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th>Action</th></tr>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo h($item['product_name']); ?></td>
                                <td>RM <?php echo number_format($item['price'], 2); ?></td>
                                <td><input class="input" type="number" name="quantity[<?php echo (int)$item['id']; ?>]" value="<?php echo (int)$item['quantity']; ?>" min="1" max="<?php echo (int)$item['stock_quantity']; ?>"></td>
                                <td>RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td><a class="btn-soft" href="cart.php?remove=<?php echo (int)$item['id']; ?>">Remove</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <div class="detail-actions" style="margin-top:18px;">
                    <button class="btn-dark" type="submit" name="update_cart">Update cart</button>
                    <a class="btn-soft" href="checkout.php">Proceed to checkout</a>
                </div>
            </form>
        <?php endif; ?>
    </section>
    <aside class="detail-card">
        <h2>Order summary</h2>
        <div class="summary-grid" style="grid-template-columns:1fr; margin-top:14px;">
            <div class="kpi-card"><div class="kpi-label">Items</div><div class="kpi-value"><?php echo count($items); ?></div></div>
            <div class="kpi-card"><div class="kpi-label">Total</div><div class="kpi-value">RM <?php echo number_format($total, 2); ?></div></div>
        </div>
    </aside>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
