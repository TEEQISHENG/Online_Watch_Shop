<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $status = strtolower(trim((string)($_POST['status'] ?? 'pending')));
    $allowed = ['pending','paid','processing','shipped','out_for_delivery','delivered','cancelled'];
    if (!in_array($status, $allowed, true)) {
        $status = 'pending';
    }
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    set_flash('green', 'Order status updated successfully.');
}
header('Location: orders.php');
exit;
?>
