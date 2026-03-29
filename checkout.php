<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();
if ($_SESSION['role'] !== 'customer') {
    header('Location: admin/dashboard.php');
    exit;
}
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch();
$cart_stmt = $pdo->prepare("SELECT ci.product_id, ci.quantity, p.product_name, p.price, p.stock_quantity FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.user_id = ?");
$cart_stmt->execute([$_SESSION['user_id']]);
$items = $cart_stmt->fetchAll();
$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}
if (!$items) {
    set_flash('red', 'Your cart is empty.');
    header('Location: cart.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'Online Banking');
    try {
        $pdo->beginTransaction();
        foreach ($items as $item) {
            if ($item['quantity'] > $item['stock_quantity']) {
                throw new Exception('Not enough stock for ' . $item['product_name']);
            }
        }
        $order = $pdo->prepare("INSERT INTO orders (user_id, delivery_address, payment_method, total_amount, payment_status, order_status) VALUES (?, ?, ?, ?, 'paid', 'pending')");
        $order->execute([$_SESSION['user_id'], $delivery_address, $payment_method, $total]);
        $order_id = $pdo->lastInsertId();
        foreach ($items as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $item_insert = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $item_insert->execute([$order_id, $item['product_id'], $item['quantity'], $item['price'], $subtotal]);
            $stock_update = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $stock_update->execute([$item['quantity'], $item['product_id']]);
        }
        $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?")->execute([$_SESSION['user_id']]);
        $pdo->commit();
        set_flash('green', 'Payment completed and order placed successfully.');
        header('Location: order_history.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash('red', 'Checkout failed: ' . $e->getMessage());
    }
}
$page_title = 'Checkout';
include __DIR__ . '/includes/header.php';
?>
<div class="section-header">
    <div>
        <h1 class="page-headline" style="font-size:40px;">Checkout</h1>
        <p>Complete delivery details and choose a payment method.</p>
    </div>
</div>
<div class="checkout-grid">
    <section class="form-card">
        <h2>Delivery and payment</h2>
        <form method="post">
            <p><label>Delivery address</label><textarea class="input" name="delivery_address" required><?php echo h($user['address']); ?></textarea></p>
            <p><label>Payment method</label>
                <select class="input" name="payment_method">
                    <option value="Online Banking">Online Banking</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="E-Wallet">E-Wallet</option>
                </select>
            </p>
            <button class="btn-dark" type="submit">Pay now</button>
        </form>
    </section>
    <section class="table-card">
        <h2>Order summary</h2>
        <div class="table-scroll">
            <table class="w3-table-all">
                <tr><th>Product</th><th>Qty</th><th>Subtotal</th></tr>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo h($item['product_name']); ?></td>
                        <td><?php echo (int)$item['quantity']; ?></td>
                        <td>RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="detail-price-row" style="margin-top:18px;">
            <div class="detail-price">RM <?php echo number_format($total, 2); ?></div>
            <span class="badge-status">Paid during checkout demo</span>
        </div>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
