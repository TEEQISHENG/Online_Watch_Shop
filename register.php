<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

$name = '';
$email = '';
$phone = '';
$address_line1 = '';
$address_line2 = '';
$city = '';
$postcode = '';
$state = 'Johor';
$country = 'Malaysia';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address_line1 = trim($_POST['address_line1'] ?? '');
    $address_line2 = trim($_POST['address_line2'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postcode = trim($_POST['postcode'] ?? '');
    $state = trim($_POST['state'] ?? 'Johor');
    $country = 'Malaysia';
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $phone === '' || $address_line1 === '' || $city === '' || $postcode === '' || $state === '' || $password === '') {
        set_flash('red', 'Please fill in all required fields.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('red', 'Please enter a valid email address.');
    } elseif (!preg_match('/^\+?[0-9\s\-]{9,20}$/', $phone)) {
        set_flash('red', 'Please enter a valid phone number.');
    } elseif (!valid_password_strength($password)) {
        set_flash('red', password_requirements_text());
    } elseif (!in_array($state, malaysia_states(), true)) {
        set_flash('red', 'Please choose a valid Malaysian state.');
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check->execute([$email]);
        if ($check->fetch()) {
            set_flash('red', 'Email already exists.');
        } else {
            $address = build_full_address($address_line1, $address_line2, $city, $postcode, $state, $country);
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, address, password_hash, role) VALUES (?, ?, ?, ?, ?, 'customer')");
            $stmt->execute([$name, $email, $phone, $address, $password_hash]);
            set_flash('green', 'Registration successful. Please login.');
            header('Location: login.php');
            exit;
        }
    }
}
$page_title = 'Register';
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
    <section class="auth-side">
        <span class="badge-source">Customer onboarding flow</span>
        <h1 class="page-headline" style="font-size:42px;">Create your account for cart, checkout and order tracking.</h1>
        <p>This registration page now gives clearer guidance for password strength, shipping details and contact formatting so customers understand exactly what to enter before checkout.</p>
    </section>
    <section class="auth-card">
        <h2>Customer Registration</h2>
        <form method="post" autocomplete="on">
            <div class="form-grid register-grid-2">
                <div>
                    <input class="input" type="text" name="name" placeholder="Full Name" value="<?php echo h($name); ?>" required>
                    <div class="field-note">Example: LEE MIN YU</div>
                </div>
                <div>
                    <input class="input" type="email" name="email" placeholder="Email" value="<?php echo h($email); ?>" required>
                    <div class="field-note">Use an active email because password reset can be sent there.</div>
                </div>
                <div>
                    <input class="input" type="text" name="phone" placeholder="Phone Number" value="<?php echo h($phone); ?>" required>
                    <div class="field-note">International format example: +60 12-345 6789</div>
                </div>
                <div>
                    <div class="password-field-wrap">
                        <input class="input input-with-button" type="password" id="password" name="password" placeholder="Password" minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$" title="Use at least 8 characters with uppercase, lowercase, number and symbol." required>
                        <button class="toggle-password-btn" type="button" onclick="togglePasswordVisibility()" id="togglePasswordBtn">Show</button>
                    </div>
                    <div class="field-note"><?php echo h(password_requirements_text()); ?></div>
                </div>
            </div>

            <div class="form-grid register-grid-2" style="margin-top:16px;">
                <div>
                    <input class="input" type="text" name="address_line1" placeholder="Address Line 1" value="<?php echo h($address_line1); ?>" required>
                    <div class="field-note">Example: 47, Jalan Tiara 2, Taman Tiara</div>
                </div>
                <div>
                    <input class="input" type="text" name="address_line2" placeholder="Address Line 2 (optional)" value="<?php echo h($address_line2); ?>">
                    <div class="field-note">Apartment, block, unit or landmark if needed.</div>
                </div>
                <div>
                    <input class="input" type="text" name="city" placeholder="City" value="<?php echo h($city); ?>" required>
                </div>
                <div>
                    <input class="input" type="text" name="postcode" placeholder="Postcode" value="<?php echo h($postcode); ?>" required>
                </div>
                <div>
                    <select class="input" name="state" required>
                        <?php foreach (malaysia_states() as $item): ?>
                            <option value="<?php echo h($item); ?>" <?php echo $state === $item ? 'selected' : ''; ?>><?php echo h($item); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="field-note">Select your Malaysian state from the list.</div>
                </div>
            </div>
            <button class="btn-dark" type="submit">Create account</button>
        </form>
    </section>
</div>
<script>
function togglePasswordVisibility() {
    var input = document.getElementById('password');
    var btn = document.getElementById('togglePasswordBtn');
    if (!input || !btn) return;
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
