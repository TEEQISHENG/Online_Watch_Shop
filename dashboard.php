<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_admin();
$page_title = 'Admin Dashboard';
$total_customers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$total_products = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$total_orders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_sales = (float)$pdo->query("SELECT IFNULL(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
$top_products = $pdo->query("SELECT p.product_name, IFNULL(SUM(oi.quantity),0) AS sold_qty FROM products p LEFT JOIN order_items oi ON p.id = oi.product_id GROUP BY p.id ORDER BY sold_qty DESC, p.product_name ASC LIMIT 6")->fetchAll();
$low_stock = $pdo->query("SELECT product_name, stock_quantity FROM products WHERE is_active = 1 ORDER BY stock_quantity ASC, product_name ASC LIMIT 6")->fetchAll();
$recent_orders = $pdo->query("SELECT o.id, o.total_amount, o.order_status, o.created_at, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT 5")->fetchAll();
$order_statuses = [
    'Pending' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn(),
    'Processing' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'processing'")->fetchColumn(),
    'Shipped' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'shipped'")->fetchColumn(),
    'Completed' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'completed'")->fetchColumn(),
    'Cancelled' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'cancelled'")->fetchColumn(),
];
$overview = [
    'Customers' => $total_customers,
    'Products' => $total_products,
    'Orders' => $total_orders,
    'Sales (RM\'000)' => (int)round($total_sales / 1000),
];
$maxOverview = max(1, max($overview));
$maxSold = 1; foreach ($top_products as $row) { $maxSold = max($maxSold, (int)$row['sold_qty']); }
$maxLow = 1; foreach ($low_stock as $row) { $maxLow = max($maxLow, (int)$row['stock_quantity']); }
$pieTotal = max(1, array_sum($order_statuses));
$pieColors = ['#f59e0b','#3b82f6','#14b8a6','#22c55e','#ef4444'];
$radius = 52; $circ = 2 * pi() * $radius; $offset = 0; $pieSlices = []; $i = 0;
foreach ($order_statuses as $label => $value) {
    $length = ($value / $pieTotal) * $circ;
    $pieSlices[] = ['label'=>$label,'value'=>$value,'color'=>$pieColors[$i],'dash'=>$length . ' ' . ($circ - $length),'offset'=>-$offset];
    $offset += $length; $i++;
}
include __DIR__ . '/../includes/header.php';
?>
<div class="section-header"><div><h1 class="page-headline" style="font-size:40px;">Admin dashboard</h1><p>All summary blocks below are rebuilt into clearer bar charts and pie charts for easier reading.</p></div></div>
<div class="chart-two-col">
    <section class="chart-card">
        <h2 class="chart-title">Store overview bar chart</h2>
        <div class="chart-sub">Quick comparison of customers, products, orders and sales.</div>
        <div class="simple-bar-chart">
            <?php foreach ($overview as $label => $value): $h = max(20, ($value / $maxOverview) * 190); ?>
            <div class="simple-bar-group">
                <div class="simple-bar" style="height:<?php echo $h; ?>px"><?php echo (int)$value; ?></div>
                <div class="simple-bar-label"><?php echo h($label); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="chart-card">
        <h2 class="chart-title">Order status pie chart</h2>
        <div class="chart-sub">Distribution of all order statuses in the current system.</div>
        <div class="pie-wrap">
            <svg viewBox="0 0 140 140" class="pie-svg" aria-hidden="true">
                <circle cx="70" cy="70" r="52" stroke="#e5e7eb"></circle>
                <?php foreach ($pieSlices as $slice): ?>
                    <circle cx="70" cy="70" r="52" stroke="<?php echo $slice['color']; ?>" stroke-dasharray="<?php echo $slice['dash']; ?>" stroke-dashoffset="<?php echo $slice['offset']; ?>"></circle>
                <?php endforeach; ?>
            </svg>
            <div class="legend-list">
                <?php foreach ($pieSlices as $slice): ?>
                    <div class="legend-item"><span class="legend-swatch" style="background:<?php echo $slice['color']; ?>"></span><?php echo h($slice['label']); ?>: <?php echo (int)$slice['value']; ?></div>
                <?php endforeach; ?>
                <div class="pie-center-note">Total orders: <?php echo $total_orders; ?></div>
            </div>
        </div>
    </section>
</div>
<div class="chart-two-col" style="margin-top:24px;">
    <section class="chart-card">
        <h2 class="chart-title">Top selling products bar chart</h2>
        <div class="chart-sub">Horizontal bar view for the products with the highest sold quantity.</div>
        <div class="chart-stack">
            <?php foreach ($top_products as $row): $width = max(8, ($row['sold_qty'] / $maxSold) * 100); ?>
                <div class="chart-row">
                    <div class="chart-row-head"><strong><?php echo h($row['product_name']); ?></strong><span><?php echo (int)$row['sold_qty']; ?> sold</span></div>
                    <div class="chart-bar-bg"><div class="chart-bar-fill" style="width:<?php echo $width; ?>%"></div></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="chart-card">
        <h2 class="chart-title">Low stock bar chart</h2>
        <div class="chart-sub">Vertical bars highlight which products are closest to stock-out level.</div>
        <div class="simple-bar-chart">
            <?php foreach ($low_stock as $row): $h = max(20, ($row['stock_quantity'] / $maxLow) * 190); ?>
            <div class="simple-bar-group">
                <div class="simple-bar danger" style="height:<?php echo $h; ?>px"><?php echo (int)$row['stock_quantity']; ?></div>
                <div class="simple-bar-label"><?php echo h($row['product_name']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<section class="table-card" style="margin-top:24px;">
    <h2>Recent orders</h2>
    <div class="table-scroll">
        <table class="w3-table-all">
            <tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>
            <?php foreach ($recent_orders as $order): ?>
            <tr>
                <td>#<?php echo (int)$order['id']; ?></td>
                <td><?php echo h($order['full_name']); ?></td>
                <td>RM <?php echo number_format($order['total_amount'], 2); ?></td>
                <td><?php echo order_status_badge($order['order_status']); ?></td>
                <td><?php echo h($order['created_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</section>
<div class="feature-grid" style="margin-top:22px;">
    <a class="feature-card" href="categories.php" style="text-decoration:none;"><h3>Manage categories</h3><p>Add or organize category data for filtering and display.</p></a>
    <a class="feature-card" href="products.php" style="text-decoration:none;"><h3>Manage products</h3><p>Add new items, set prices and maintain stock quantity.</p></a>
    <a class="feature-card" href="customers.php" style="text-decoration:none;"><h3>View customers</h3><p>Review registered customer accounts and account activity.</p></a>
    <a class="feature-card" href="orders.php" style="text-decoration:none;"><h3>Manage orders</h3><p>Update order status and monitor paid transactions.</p></a>
    <a class="feature-card" href="reports.php" style="text-decoration:none;"><h3>Generate reports</h3><p>Open deeper bar chart and pie chart report sections quickly.</p></a>
    <a class="feature-card" href="admins.php" style="text-decoration:none;"><h3>Manage admin</h3><p>Superadmin can add more admin users for the backend module.</p></a>
</div>

<?php
$restock_need = $pdo->query("SELECT product_name, stock_quantity FROM products WHERE stock_quantity <= 5 ORDER BY stock_quantity ASC, product_name ASC LIMIT 5")->fetchAll();
$fast_moving = $pdo->query("SELECT p.product_name, IFNULL(SUM(oi.quantity),0) AS sold_qty, p.stock_quantity FROM products p LEFT JOIN order_items oi ON p.id = oi.product_id GROUP BY p.id ORDER BY sold_qty DESC, p.stock_quantity ASC, p.product_name ASC LIMIT 5")->fetchAll();
$understocked_trending = [];
foreach ($fast_moving as $item) {
    if ((int)$item['sold_qty'] >= 1 && (int)$item['stock_quantity'] <= 6) {
        $understocked_trending[] = $item;
    }
}
$report_highlights = [];
if (!empty($restock_need)) {
    $report_highlights[] = 'Restock priority should start with ' . $restock_need[0]['product_name'] . ' because it has only ' . (int)$restock_need[0]['stock_quantity'] . ' unit(s) left.';
}
if (!empty($fast_moving)) {
    $report_highlights[] = $fast_moving[0]['product_name'] . ' is currently the strongest seller based on ordered quantity.';
}
if ($total_orders > 0) {
    $report_highlights[] = 'Completed orders currently represent the largest finished outcome in the order pipeline, which suggests recent delivery flow is healthy.';
}
?>
<section class="table-card" style="margin-top:24px;">
    <div class="section-header" style="margin:0 0 16px;">
        <div>
            <h2>Admin advice and smart suggestions</h2>
            <p>Quick actions the system can surface for restocking, trend tracking and buying decisions.</p>
        </div>
        <span class="insight-badge">Auto insight panel</span>
    </div>
    <div class="advice-grid">
        <article class="advice-card">
            <h3>Restock now</h3>
            <p>These products are closest to running out and should be reviewed first for replenishment.</p>
            <ul class="advice-list">
                <?php if ($restock_need): foreach ($restock_need as $item): ?>
                    <li><strong><?php echo h($item['product_name']); ?></strong> - only <?php echo (int)$item['stock_quantity']; ?> left.</li>
                <?php endforeach; else: ?>
                    <li>No urgent restock item is currently below the warning threshold.</li>
                <?php endif; ?>
            </ul>
        </article>
        <article class="advice-card">
            <h3>Trending products to consider buying more</h3>
            <p>The system highlights fast-moving products and lets the admin decide whether to increase stock.</p>
            <ul class="advice-list">
                <?php if ($understocked_trending): foreach ($understocked_trending as $item): ?>
                    <li><strong><?php echo h($item['product_name']); ?></strong> sold <?php echo (int)$item['sold_qty']; ?> unit(s) and has <?php echo (int)$item['stock_quantity']; ?> left. Consider adding more stock.</li>
                <?php endforeach; else: ?>
                    <li>No fast-moving item is currently both trending and low in stock.</li>
                <?php endif; ?>
            </ul>
        </article>
        <article class="advice-card">
            <h3>Key operational highlights</h3>
            <p>Short summaries make it easier to generate a useful report without reading every table manually.</p>
            <ul class="advice-list">
                <?php if ($report_highlights): foreach ($report_highlights as $line): ?>
                    <li><?php echo h($line); ?></li>
                <?php endforeach; else: ?>
                    <li>More order data is needed before the system can produce stronger recommendations.</li>
                <?php endif; ?>
            </ul>
        </article>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
