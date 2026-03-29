<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_admin();
$customers = $pdo->query("SELECT id, full_name, email, phone, address, created_at FROM users WHERE role = 'customer' ORDER BY id DESC")->fetchAll();
$page_title = 'Customer List';
include __DIR__ . '/../includes/header.php';
?>
<div class="table-card">
    <h2>Customer List</h2>
    <table class="w3-table-all">
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Joined Date</th></tr>
        <?php foreach ($customers as $customer): ?>
        <tr>
            <td><?php echo (int)$customer['id']; ?></td>
            <td><?php echo h($customer['full_name']); ?></td>
            <td><?php echo h($customer['email']); ?></td>
            <td><?php echo h($customer['phone']); ?></td>
            <td><?php echo h($customer['address']); ?></td>
            <td><?php echo h($customer['created_at']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
