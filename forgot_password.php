<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
$page_title = 'Forgot Password';
$mail_log_file = __DIR__ . '/storage_mail_log.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        set_flash('red', 'Email not found.');
    } else {
        $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);
        $token = bin2hex(random_bytes(24));
        $pdo->prepare("INSERT INTO password_resets (user_id, reset_token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))")->execute([$user['id'], $token]);

        $resetLink = base_url() . '/reset_password.php?token=' . urlencode($token) . '&email=' . urlencode($email);
        $mailBody = "Reset Password Request

Hello " . ($user['full_name'] ?: 'Customer') . ",

"
            . "We received a request to reset your TimePiece Gallery password.
"
            . "Open the link below to continue:
" . $resetLink . "

"
            . "This link will expire in 1 hour.

"
            . "If you did not request this, you can ignore this email.
";

        $error = '';
        if (send_app_mail($email, 'Reset your TimePiece Gallery password', $mailBody, $error)) {
            set_flash('green', 'If the email exists in our system, a secure reset link has been sent to the inbox.');
        } else {
            $logText = "[" . date('Y-m-d H:i:s') . "] To: {$email}
{$mailBody}
SMTP Error: {$error}
--------------------------
";
            file_put_contents($mail_log_file, $logText, FILE_APPEND);
            set_flash('orange', 'Reset request saved securely. Email sending is not configured yet on this localhost setup.');
        }
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
    <section class="auth-side">
        <span class="badge-source">Private reset flow</span>
        <h1 class="page-headline" style="font-size:42px;">Send a secure reset link by email.</h1>
        <p>The reset link is handled privately instead of being shown on the page. This keeps the recovery flow cleaner and more secure for customers.</p>
    </section>
    <section class="auth-card">
        <h2>Forgot Password</h2>
        <p class="muted">Enter your account email. The reset link will be sent by email only. It is no longer printed openly on this page.</p>
        <form method="post" autocomplete="on">
            <p><input class="input" type="email" name="email" placeholder="Enter your email" required></p>
            <p><button class="btn-dark" type="submit">Send Reset Email</button></p>
        </form>
        <div class="field-note">Only the account owner should receive the reset email. For real Gmail delivery on localhost, the store SMTP sender still needs valid Gmail credentials and an App Password.</div>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
