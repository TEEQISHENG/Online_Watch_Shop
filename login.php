<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        if (in_array($user['role'], ['admin', 'superadmin'])) {
            set_flash('red', 'Please use the admin login page for admin accounts.');
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit;
        }
    } else {
        set_flash('red', 'Invalid email or password.');
    }
}
$page_title = 'Login';
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
    <section class="auth-side">
        <span class="badge-source">Secure account access</span>
        <h1 class="page-headline" style="font-size:42px;">Login to continue shopping or manage your account.</h1>
        <p>Customers can browse, add to cart and check out here. Admin users can continue from the dedicated admin login page.</p>
    </section>
    <section class="auth-card">
        <h2>User Login</h2>
        <p class="muted">Enter your registered email and password.</p>
        <form method="post">
            <p><input class="input" type="email" name="email" placeholder="Email" required></p>
            <div class="password-field-wrap">
                <input class="input input-with-button" type="password" id="loginPasswordField" name="password" placeholder="Password" required>
                <button class="toggle-password-btn" type="button" onclick="toggleFieldPassword('loginPasswordField', this)">Show</button>
            </div>
            <div class="detail-actions" style="margin-top:14px;">
                <button class="btn-dark" type="submit">Login</button>
                <a class="btn-soft" href="forgot_password.php">Forgot password?</a>
            </div>
        </form>
        <p class="muted" style="margin-top:20px;">Admin can also use the dedicated <a href="admin/login.php">admin login page</a>.</p>
    </section>
</div>
<script>
function toggleFieldPassword(fieldId, button){
    var input = document.getElementById(fieldId);
    if(!input){return;}
    if(input.type === 'password'){ input.type = 'text'; button.textContent = 'Hide'; }
    else { input.type = 'password'; button.textContent = 'Show'; }
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
