<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
$page_title = 'Order History';
include __DIR__ . '/includes/header.php';
?>
<div class="section-header">
    <div>
        <h1 class="page-headline" style="font-size:40px;">Order history</h1>
        <p>Track completed purchases and open individual order details.</p>
    </div>
</div>
<div class="table-card">
    <div class="table-scroll">
        <table class="w3-table-all">
            <tr><th>Order ID</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th>Action</th></tr>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?php echo (int)$order['id']; ?></td>
                <td><?php echo h($order['created_at']); ?></td>
                <td>RM <?php echo number_format($order['total_amount'], 2); ?></td>
                <td><?php echo strtoupper(h($order['payment_status'])); ?></td>
                <td><?php echo order_status_badge($order['order_status']); ?></td>
                <td><a class="btn-dark" href="order_details.php?id=<?php echo (int)$order['id']; ?>">View</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
