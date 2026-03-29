<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$order_stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
$order_stmt->execute([$id, $_SESSION['user_id']]);
$order = $order_stmt->fetch();
if (!$order) {
    set_flash('red', 'Order not found.');
    header('Location: order_history.php');
    exit;
}
$item_stmt = $pdo->prepare("SELECT oi.*, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$item_stmt->execute([$id]);
$items = $item_stmt->fetchAll();
$page_title = 'Order Details';
include __DIR__ . '/includes/header.php';
?>
<div class="section-header">
    <div>
        <h1 class="page-headline" style="font-size:40px;">Order #<?php echo (int)$order['id']; ?></h1>
        <p>Payment method: <?php echo h($order['payment_method']); ?> · Delivery address: <?php echo h($order['delivery_address']); ?></p>
    </div>
    <?php echo order_status_badge($order['order_status']); ?>
</div>
<?php echo order_progress_steps($order['order_status']); ?>
<div class="table-card">
    <div class="table-scroll">
        <table class="w3-table-all">
            <tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo h($item['product_name']); ?></td>
                <td><?php echo (int)$item['quantity']; ?></td>
                <td>RM <?php echo number_format($item['unit_price'], 2); ?></td>
                <td>RM <?php echo number_format($item['subtotal'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div class="detail-price-row" style="margin-top:18px;">
        <div class="detail-price">RM <?php echo number_format($order['total_amount'], 2); ?></div>
        <div class="muted">Paid status: <?php echo strtoupper(h($order['payment_status'])); ?></div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
