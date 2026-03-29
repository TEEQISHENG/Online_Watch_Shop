<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();
if ($_SERVER['role'] ?? '' === 'admin') {}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current_password, $user['password_hash'])) {
        set_flash('red', 'Current password is incorrect.');
    } elseif ($new_password !== $confirm_password) {
        set_flash('red', 'New password and confirm password do not match.');
    } elseif (!valid_password_strength($new_password)) {
        set_flash('red', password_requirements_text());
    } else {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $update->execute([$new_hash, $_SESSION['user_id']]);
        set_flash('green', 'Password changed successfully.');
        header('Location: profile.php');
        exit;
    }
}
$page_title = 'Change Password';
include __DIR__ . '/includes/header.php';
?>
<div class="w3-card w3-white w3-padding">
    <h2>Change Password</h2>
    <form method="post">
        <div class="password-field-wrap" style="margin-bottom:16px;">
            <input class="input input-with-button" type="password" id="currentPasswordField" name="current_password" placeholder="Current Password" required>
            <button class="toggle-password-btn" type="button" onclick="toggleFieldPassword('currentPasswordField', this)">Show</button>
        </div>
        <div class="password-field-wrap" style="margin-bottom:8px;">
            <input class="input input-with-button" type="password" id="newPasswordField" name="new_password" placeholder="New Password" minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$" title="Use at least 8 characters with uppercase, lowercase, number and symbol." required>
            <button class="toggle-password-btn" type="button" onclick="toggleFieldPassword('newPasswordField', this)">Show</button>
        </div>
        <div class="field-note" style="margin-bottom:16px;"><?php echo h(password_requirements_text()); ?></div>
        <div class="password-field-wrap" style="margin-bottom:16px;">
            <input class="input input-with-button" type="password" id="confirmPasswordField" name="confirm_password" placeholder="Confirm New Password" required>
            <button class="toggle-password-btn" type="button" onclick="toggleFieldPassword('confirmPasswordField', this)">Show</button>
        </div>
        <p><button class="w3-button w3-black" type="submit">Update Password</button></p>
    </form>
</div>
<script>
function toggleFieldPassword(fieldId, button){
 var input = document.getElementById(fieldId);
 if(!input){return;}
 if(input.type === 'password'){ input.type = 'text'; button.textContent='Hide'; }
 else { input.type='password'; button.textContent='Show'; }
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
