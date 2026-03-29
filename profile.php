<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();
if ($_SESSION['role'] !== 'customer') {
    header('Location: admin/dashboard.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $update = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
    $update->execute([$name, $phone, $address, $_SESSION['user_id']]);
    $_SESSION['user_name'] = $name;
    set_flash('green', 'Profile updated successfully.');
    header('Location: profile.php');
    exit;
}
$page_title = 'Edit Profile';
include __DIR__ . '/includes/header.php';
?>
<div class="w3-card w3-white w3-padding">
    <h2>Edit Profile</h2>
    <form method="post">
        <p><label>Full Name</label><input class="w3-input w3-border" type="text" name="name" value="<?php echo h($user['full_name']); ?>" required></p>
        <p><label>Email</label><input class="w3-input w3-border" type="email" value="<?php echo h($user['email']); ?>" readonly></p>
        <p><label>Phone</label><input class="w3-input w3-border" type="text" name="phone" value="<?php echo h($user['phone']); ?>" required></p>
        <p><label>Address</label><textarea class="w3-input w3-border" name="address" required><?php echo h($user['address']); ?></textarea></p>
        <p><button class="w3-button w3-black" type="submit">Save Changes</button> <a class="w3-button w3-blue" href="change_password.php">Change Password</a></p>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
