<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role IN ('admin', 'superadmin') LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php');
        exit;
    } else {
        set_flash('red', 'Invalid admin login details.');
    }
}
$page_title = 'Admin Login';
include __DIR__ . '/../includes/header.php';
?>
<div class="auth-wrap">
    <section class="auth-side">
        <span class="badge-source">Admin access</span>
        <h1 class="page-headline" style="font-size:42px;">Manage products, orders and reports from one place.</h1>
        <p>Use the admin account here to review stock, update order status and check report insights for the store.</p>
    </section>
    <section class="auth-card">
        <h2>Admin Login</h2>
        <p class="muted">Enter your admin email and password.</p>
        <form method="post">
            <p><input class="input" type="email" name="email" placeholder="Admin Email" required></p>
            <div class="password-field-wrap">
                <input class="input input-with-button" type="password" id="adminLoginPasswordField" name="password" placeholder="Password" required>
                <button class="toggle-password-btn" type="button" onclick="toggleFieldPassword('adminLoginPasswordField', this)">Show</button>
            </div>
            <div class="detail-actions" style="margin-top:14px;">
                <button class="btn-dark" type="submit">Login</button>
            </div>
        </form>
    </section>
</div>
<script>
function toggleFieldPassword(fieldId, button){
 var input = document.getElementById(fieldId);
 if(!input){return;}
 if(input.type === 'password'){ input.type = 'text'; button.textContent='Hide'; }
 else { input.type='password'; button.textContent='Show'; }
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
