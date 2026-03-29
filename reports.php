<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_admin();
$page_title = 'Sales Report';
$total_sales = (float)$pdo->query("SELECT IFNULL(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
$total_orders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$completed_orders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'completed'")->fetchColumn();
$low_stock = $pdo->query("SELECT product_name, stock_quantity FROM products WHERE stock_quantity <= 6 ORDER BY stock_quantity ASC, product_name ASC LIMIT 8")->fetchAll();
$sales_by_product = $pdo->query("SELECT p.product_name, IFNULL(SUM(oi.quantity),0) AS total_qty, IFNULL(SUM(oi.subtotal),0) AS total_sales FROM products p LEFT JOIN order_items oi ON p.id = oi.product_id GROUP BY p.id ORDER BY total_sales DESC, p.product_name ASC LIMIT 8")->fetchAll();
$paymentStatus = [
    'Paid' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'")->fetchColumn(),
    'Pending' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'pending'")->fetchColumn(),
    'Failed' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'failed'")->fetchColumn(),
];
$summary = ['Sales RM\'000' => (int)round($total_sales / 1000), 'Orders' => $total_orders, 'Completed' => $completed_orders];
$maxSummary = max(1, max($summary));
$maxSales = 1; foreach ($sales_by_product as $row) { $maxSales = max($maxSales, (float)$row['total_sales']); }
$maxStock = 1; foreach ($low_stock as $row) { $maxStock = max($maxStock, (int)$row['stock_quantity']); }
$pieTotal = max(1, array_sum($paymentStatus));
$pieColors = ['#22c55e','#f59e0b','#ef4444'];
$radius = 52; $circ = 2 * pi() * $radius; $offset = 0; $pieSlices = []; $i = 0;
foreach ($paymentStatus as $label => $value) {
    $length = ($value / $pieTotal) * $circ;
    $pieSlices[] = ['label'=>$label,'value'=>$value,'color'=>$pieColors[$i],'dash'=>$length . ' ' . ($circ - $length),'offset'=>-$offset];
    $offset += $length; $i++;
}
include __DIR__ . '/../includes/header.php';
?>
<div class="section-header"><div><h1 class="page-headline" style="font-size:40px;">Sales and inventory report</h1><p>Report page rebuilt with more chart-focused visuals using bar charts and pie charts.</p></div></div>
<div class="chart-two-col">
    <section class="chart-card">
        <h2 class="chart-title">Report summary bar chart</h2>
        <div class="chart-sub">Overall performance numbers for this store.</div>
        <div class="simple-bar-chart">
            <?php foreach ($summary as $label => $value): $h = max(20, ($value / $maxSummary) * 190); ?>
            <div class="simple-bar-group">
                <div class="simple-bar secondary" style="height:<?php echo $h; ?>px"><?php echo (int)$value; ?></div>
                <div class="simple-bar-label"><?php echo h($label); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="chart-card">
        <h2 class="chart-title">Payment status pie chart</h2>
        <div class="chart-sub">How paid, pending and failed payments are currently split.</div>
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
            </div>
        </div>
    </section>
</div>
<div class="chart-two-col" style="margin-top:24px;">
    <section class="chart-card">
        <h2 class="chart-title">Sales by product bar chart</h2>
        <div class="chart-sub">Each bar compares sales value by product.</div>
        <div class="chart-stack">
            <?php foreach ($sales_by_product as $row): $width = max(8, ($row['total_sales'] / $maxSales) * 100); ?>
            <div class="chart-row">
                <div class="chart-row-head"><strong><?php echo h($row['product_name']); ?></strong><span>RM <?php echo number_format($row['total_sales'], 2); ?></span></div>
                <div class="muted"><?php echo (int)$row['total_qty']; ?> unit(s) sold</div>
                <div class="chart-bar-bg"><div class="chart-bar-fill" style="width:<?php echo $width; ?>%"></div></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="chart-card">
        <h2 class="chart-title">Low stock bar chart</h2>
        <div class="chart-sub">Products most in need of restocking.</div>
        <div class="simple-bar-chart">
            <?php foreach ($low_stock as $row): $h = max(20, ($row['stock_quantity'] / $maxStock) * 190); ?>
            <div class="simple-bar-group">
                <div class="simple-bar warning" style="height:<?php echo $h; ?>px"><?php echo (int)$row['stock_quantity']; ?></div>
                <div class="simple-bar-label"><?php echo h($row['product_name']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php
$restock_priority = $pdo->query("SELECT product_name, stock_quantity FROM products WHERE stock_quantity <= 5 ORDER BY stock_quantity ASC, product_name ASC LIMIT 6")->fetchAll();
$trend_candidates = $pdo->query("SELECT p.product_name, IFNULL(SUM(oi.quantity),0) AS sold_qty, p.stock_quantity FROM products p LEFT JOIN order_items oi ON p.id = oi.product_id GROUP BY p.id HAVING sold_qty > 0 ORDER BY sold_qty DESC, p.stock_quantity ASC, p.product_name ASC LIMIT 6")->fetchAll();
$executive_points = [];
if (!empty($sales_by_product)) {
    $executive_points[] = $sales_by_product[0]['product_name'] . ' currently leads product revenue at RM ' . number_format($sales_by_product[0]['total_sales'], 2) . '.';
}
if (!empty($restock_priority)) {
    $executive_points[] = 'At least one product has reached urgent restock level, with the lowest item now down to ' . (int)$restock_priority[0]['stock_quantity'] . ' unit(s).';
}
$paidCount = (int)($paymentStatus['Paid'] ?? 0);
if ($total_orders > 0) {
    $executive_points[] = 'Paid orders account for ' . round(($paidCount / max(1, $total_orders)) * 100) . '% of all orders recorded so far.';
}
?>
<section class="table-card" style="margin-top:24px;">
    <div class="section-header" style="margin:0 0 16px;">
        <div>
            <h2>Executive report summary</h2>
            <p>Helpful takeaways for the admin before exporting or presenting the report.</p>
        </div>
        <span class="insight-badge">Report focus</span>
    </div>
    <div class="advice-grid">
        <article class="advice-card">
            <h3>Top priorities</h3>
            <ul class="advice-list">
                <?php if ($executive_points): foreach ($executive_points as $line): ?>
                    <li><?php echo h($line); ?></li>
                <?php endforeach; else: ?>
                    <li>The system needs more store activity before priorities can be summarized.</li>
                <?php endif; ?>
            </ul>
        </article>
        <article class="advice-card">
            <h3>Recommended restock list</h3>
            <ul class="advice-list">
                <?php if ($restock_priority): foreach ($restock_priority as $row): ?>
                    <li><strong><?php echo h($row['product_name']); ?></strong> - replenish soon because only <?php echo (int)$row['stock_quantity']; ?> remain.</li>
                <?php endforeach; else: ?>
                    <li>No product is currently under the urgent restock threshold.</li>
                <?php endif; ?>
            </ul>
        </article>
        <article class="advice-card">
            <h3>Suggested expansion items</h3>
            <ul class="advice-list">
                <?php if ($trend_candidates): foreach ($trend_candidates as $row): ?>
                    <li><?php echo h($row['product_name']); ?> sold <?php echo (int)$row['sold_qty']; ?> unit(s). <?php echo (int)$row['stock_quantity'] <= 6 ? 'Consider buying more for this trend.' : 'Keep monitoring demand for future purchasing.'; ?></li>
                <?php endforeach; else: ?>
                    <li>No trend recommendation is available yet because there are not enough sales records.</li>
                <?php endif; ?>
            </ul>
        </article>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
