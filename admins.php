<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_admin();
require_superadmin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($full_name !== '' && $email !== '' && $password !== '') {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check->execute([$email]);
        if ($check->fetch()) {
            set_flash('red', 'Email already exists.');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, address, password_hash, role) VALUES (?, ?, '', '', ?, 'admin')");
            $stmt->execute([$full_name, $email, $hash]);
            set_flash('green', 'New admin added successfully.');
            header('Location: admins.php');
            exit;
        }
    }
}
$admins = $pdo->query("SELECT id, full_name, email, role, created_at FROM users WHERE role IN ('admin', 'superadmin') ORDER BY id DESC")->fetchAll();
$page_title = 'Manage Admin';
include __DIR__ . '/../includes/header.php';
?>
<div class="w3-row-padding">
    <div class="w3-half">
        <div class="w3-card w3-white w3-padding">
            <h2>Add Admin</h2>
            <form method="post">
                <p><input class="w3-input w3-border" type="text" name="full_name" placeholder="Full Name" required></p>
                <p><input class="w3-input w3-border" type="email" name="email" placeholder="Email" required></p>
                <div class="password-field-wrap" style="margin-bottom:8px;">
                <input class="input input-with-button" type="password" id="newAdminPasswordField" name="password" placeholder="Password" minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$" title="Use at least 8 characters with uppercase, lowercase, number and symbol." required>
                <button class="toggle-password-btn" type="button" onclick="toggleFieldPassword('newAdminPasswordField', this)">Show</button>
            </div>
                <div class="field-note"><?php echo h(password_requirements_text()); ?></div>
                <p><button class="w3-button w3-black" type="submit">Add Admin</button></p>
            </form>
        </div>
    </div>
    <div class="w3-half">
        <div class="table-card">
            <h2>Admin List</h2>
            <table class="w3-table-all">
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th></tr>
                <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?php echo (int)$admin['id']; ?></td>
                    <td><?php echo h($admin['full_name']); ?></td>
                    <td><?php echo h($admin['email']); ?></td>
                    <td><?php echo h($admin['role']); ?></td>
                    <td><?php echo h($admin['created_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
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
