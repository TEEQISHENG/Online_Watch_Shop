<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

$email = trim($_GET['email'] ?? ($_POST['email'] ?? ''));
$token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $token = trim($_POST['token'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    if (!valid_password_strength($new_password)) {
        set_flash('red', password_requirements_text());
    } else {
        $stmt = $pdo->prepare("SELECT u.id, pr.id AS reset_id FROM users u JOIN password_resets pr ON u.id = pr.user_id WHERE u.email = ? AND pr.reset_token = ? AND pr.expires_at >= NOW() LIMIT 1");
        $stmt->execute([$email, $token]);
        $row = $stmt->fetch();

        if ($row) {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $row['id']]);
            $pdo->prepare("DELETE FROM password_resets WHERE id = ?")->execute([$row['reset_id']]);
            set_flash('green', 'Password reset successful. Please login.');
            header('Location: login.php');
            exit;
        } else {
            set_flash('red', 'Invalid reset link or the link has expired.');
        }
    }
}
$page_title = 'Reset Password';
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap" style="grid-template-columns:1fr;">
    <section class="auth-card">
        <h2>Reset Password</h2>
        <p class="muted">Use the reset link from your email, then choose a new password that follows the same strength rule as account registration.</p>
        <form method="post" autocomplete="off">
            <p><input class="input" type="email" name="email" placeholder="Email" value="<?php echo h($email); ?>" required></p>
            <p><input class="input" type="text" name="token" placeholder="Reset Token" value="<?php echo h($token); ?>" required></p>
            <div class="password-field-wrap">
                <input class="input input-with-button" type="password" id="resetPasswordField" name="new_password" placeholder="New Password" minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$" title="Use at least 8 characters with uppercase, lowercase, number and symbol." required>
                <button class="toggle-password-btn" type="button" onclick="toggleResetPassword()" id="toggleResetBtn">Show</button>
            </div>
            <div class="field-note"><?php echo h(password_requirements_text()); ?></div>
            <p style="margin-top:16px;"><button class="btn-dark" type="submit">Reset Password</button></p>
        </form>
    </section>
</div>
<script>
function toggleResetPassword() {
    var input = document.getElementById('resetPasswordField');
    var btn = document.getElementById('toggleResetBtn');
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Hide';
    } else {
        input.type = 'password';
        btn.textContent = 'Show';
    }
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
