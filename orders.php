<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_admin();
$page_title = 'Manage Orders';
$stmt = $pdo->query("SELECT orders.*, users.full_name FROM orders JOIN users ON orders.user_id = users.id ORDER BY orders.id DESC");
$orders = $stmt->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="section-header">
    <div>
        <h1 class="page-headline" style="font-size:40px;">Manage orders</h1>
        <p>Update customer order progress and keep storefront tracking in sync.</p>
    </div>
</div>
<section class="table-card">
    <div class="table-scroll">
        <table class="w3-table-all">
            <tr><th>ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Update</th></tr>
            <?php foreach($orders as $order): ?>
            <tr>
                <td>#<?= (int)$order['id'] ?></td>
                <td><?= h($order['full_name']) ?></td>
                <td>RM <?= number_format($order['total_amount'],2) ?></td>
                <td><?= order_status_badge($order['order_status']) ?></td>
                <td>
                    <form method="POST" action="update_order_status.php" style="display:flex;gap:8px;align-items:center;">
                        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                        <select class="input" name="status" style="min-width:190px;">
                            <?php $current = strtolower((string)$order['order_status']); ?>
                            <?php $options = ['pending'=>'Pending','paid'=>'Paid','processing'=>'Processing','shipped'=>'Shipped','out_for_delivery'=>'Out for Delivery','delivered'=>'Delivered','cancelled'=>'Cancelled']; ?>
                            <?php foreach ($options as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $current === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn-dark" type="submit">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
