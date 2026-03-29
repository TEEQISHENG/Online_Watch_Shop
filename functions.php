<?php
$__script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$__is_admin_area = strpos($__script_name, '/admin/') !== false;
$__session_name = $__is_admin_area ? 'TPG_ADMIN_SESSION' : 'TPG_CUSTOMER_SESSION';
$__session_path = $__is_admin_area ? rtrim(dirname($__script_name), '/') . '/' : '/';
if ($__session_path === '//') {
    $__session_path = '/';
}
if (session_status() === PHP_SESSION_NONE) {
    session_name($__session_name);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $__session_path,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

function base_url() {
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    if ($dir === '' || $dir === '.') {
        $base = '';
        return $base;
    }

    if (substr($dir, -6) === '/admin') {
        $dir = substr($dir, 0, -6);
    }

    $base = $dir === '' ? '' : $dir;
    return $base;
}

function redirect_to($path) {
    header('Location: ' . base_url() . '/' . ltrim($path, '/'));
    exit;
}

function asset_url($path) {
    return base_url() . '/assets/' . ltrim($path, '/');
}

function product_image_url($path) {
    $path = trim((string)$path);
    if ($path === '') {
        return asset_url('images/default_watch.jpg');
    }
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }
    return base_url() . '/' . ltrim($path, '/');
}

function logout_url() {
    return strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/admin/') !== false
        ? base_url() . '/admin/logout.php'
        : base_url() . '/logout.php';
}

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function display_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        echo '<div class="w3-panel w3-' . htmlspecialchars($flash['type']) . ' w3-padding" style="border-radius:16px;">' . htmlspecialchars($flash['message']) . '</div>';
        unset($_SESSION['flash']);
    }
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function is_admin_logged_in() {
    return !empty($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'superadmin']);
}

function require_login() {
    if (!is_logged_in()) {
        set_flash('red', 'Please login first.');
        redirect_to('login.php');
    }
}

function require_admin() {
    if (!is_admin_logged_in()) {
        set_flash('red', 'Admin access only.');
        redirect_to('admin/login.php');
    }
}

function require_superadmin() {
    if (empty($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
        set_flash('red', 'Superadmin access only.');
        redirect_to('admin/dashboard.php');
    }
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function current_user() {
    return [
        'id' => $_SESSION['user_id'] ?? 0,
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['role'] ?? ''
    ];
}

function order_status_badge($status) {
    $status = strtolower(trim((string)$status));
    $map = [
        'pending' => ['color' => 'orange', 'label' => 'Pending'],
        'paid' => ['color' => 'blue', 'label' => 'Paid'],
        'processing' => ['color' => 'deep-orange', 'label' => 'Processing'],
        'shipped' => ['color' => 'teal', 'label' => 'Shipped'],
        'shipping' => ['color' => 'teal', 'label' => 'Shipping'],
        'out_for_delivery' => ['color' => 'grey', 'label' => 'Out for Delivery'],
        'delivered' => ['color' => 'green', 'label' => 'Delivered'],
        'completed' => ['color' => 'green', 'label' => 'Completed'],
        'cancelled' => ['color' => 'red', 'label' => 'Cancelled']
    ];
    $item = $map[$status] ?? ['color' => 'grey', 'label' => ucfirst(str_replace('_', ' ', $status ?: 'pending'))];
    return '<span class="w3-tag w3-' . $item['color'] . '" style="border-radius:999px;padding:8px 12px;">' . h($item['label']) . '</span>';
}

function condition_badge($condition) {
    $condition = strtolower(trim((string)$condition));
    $map = [
        'new' => ['label' => 'New', 'class' => 'badge-status'],
        'pre-owned' => ['label' => 'Pre-Owned', 'class' => 'badge-source']
    ];
    $item = $map[$condition] ?? ['label' => ucfirst($condition ?: 'New'), 'class' => 'badge-pill'];
    return '<span class="' . $item['class'] . '">' . h($item['label']) . '</span>';
}

function trust_points($condition) {
    $condition = strtolower(trim((string)$condition));
    if ($condition === 'pre-owned') {
        return ['Condition checked by our team', 'Photos and specs reviewed', 'Secure checkout & tracked delivery'];
    }
    return ['Authorized-style boutique presentation', 'Secure payment checkout', 'Tracked delivery across Malaysia'];
}

function order_progress_steps($status) {
    $status = strtolower(trim((string)$status));
    $steps = ['Order placed', 'Payment verified', 'Shipping', 'Completed'];

    if ($status === 'pending') {
        $activeIndex = 0;
    } elseif ($status === 'paid' || $status === 'processing') {
        $activeIndex = 1;
    } elseif ($status === 'shipped' || $status === 'out_for_delivery' || $status === 'shipping') {
        $activeIndex = 2;
    } elseif ($status === 'delivered' || $status === 'completed') {
        $activeIndex = 3;
    } else {
        $activeIndex = 0;
    }

    $html = '<div class="status-track">';
    foreach ($steps as $index => $label) {
        $activeClass = $index <= $activeIndex ? ' active' : '';
        $html .= '<div class="status-step' . $activeClass . '">' . h($label) . '</div>';
    }
    $html .= '</div>';
    return $html;
}


function product_tagline($productName, $categoryName, $condition) {
    $name = strtolower((string)$productName);
    $category = strtolower((string)$categoryName);
    $condition = strtolower((string)$condition);

    if (strpos($name, 'smart') !== false || strpos($name, 'watch') !== false && $category === 'smart') {
        return 'Connected features, health tracking and daily convenience.';
    }
    if ($condition === 'pre-owned') {
        return 'A curated pre-owned selection for buyers who want stronger value without losing character.';
    }
    if ($category === 'luxury') {
        return 'Premium finishing, collector appeal and boutique-style presentation.';
    }
    if ($category === 'sports') {
        return 'Built for active wear, easy readability and everyday reliability.';
    }
    if ($category === 'women') {
        return 'Elegant sizing with gift-friendly styling and everyday comfort.';
    }
    return 'Versatile design with clean styling for office, casual and weekend wear.';
}

function condition_summary($condition) {
    return strtolower((string)$condition) === 'pre-owned'
        ? 'Each pre-owned listing is presented with condition-focused notes, secure checkout and tracked delivery.'
        : 'Each new arrival is displayed with clean product imagery, clear pricing and a straightforward purchase flow.';
}

function store_trust_items() {
    return [
        'Authenticity-focused presentation',
        'Secure checkout process',
        'Tracked shipping updates',
        'New and pre-owned selection',
        'Responsive admin order handling',
        'Customer account and order history'
    ];
}


function password_requirements_text() {
    return 'Use at least 8 characters with uppercase, lowercase, number and symbol.';
}

function valid_password_strength($password) {
    $password = (string)$password;
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password)
        && preg_match('/[^A-Za-z0-9]/', $password);
}

function malaysia_states() {
    return [
        'Johor','Kedah','Kelantan','Melaka','Negeri Sembilan','Pahang','Perak','Perlis',
        'Pulau Pinang','Sabah','Sarawak','Selangor','Terengganu','Kuala Lumpur','Labuan','Putrajaya'
    ];
}

function country_options() {
    return ['Malaysia'];
}

function build_full_address($line1, $line2, $city, $postcode, $state, $country) {
    $parts = [];
    foreach ([$line1, $line2, $city, $postcode, $state, $country] as $part) {
        $part = trim((string)$part);
        if ($part !== '') {
            $parts[] = $part;
        }
    }
    return implode(', ', $parts);
}

function product_story($product) {
    $name = strtolower((string)($product['product_name'] ?? ''));
    $category = strtolower((string)($product['category_name'] ?? ''));
    $condition = strtolower((string)($product['item_condition'] ?? 'new'));
    $brand = 'This model';
    if (strpos($name, 'apple') !== false) {
        $brand = 'This Apple Watch';
    } elseif (strpos($name, 'seiko') !== false) {
        $brand = 'This Seiko watch';
    } elseif (strpos($name, 'casio') !== false || strpos($name, 'g-shock') !== false) {
        $brand = 'This Casio watch';
    } elseif (strpos($name, 'tissot') !== false) {
        $brand = 'This Tissot watch';
    }

    if ($category === 'smart') {
        return $brand . ' gives users a practical mix of fitness tracking, message alerts, daily convenience and a comfortable design for school, work or travel. It suits buyers who want one wearable that feels modern, useful and easy to pair with everyday outfits.';
    }
    if ($category === 'luxury') {
        return $brand . ' stands out through premium finishing, stronger wrist presence and a more refined design language. It is suitable for customers who care about detail, gifting value and a watch that looks polished during meetings, dinners and special occasions.';
    }
    if ($category === 'sports') {
        return $brand . ' is built for active daily use with a clear dial, durable case feel and dependable readability. It works well for buyers who prefer sporty styling, easy time checking and a watch that still feels appropriate outside workout settings.';
    }
    if ($category === 'women') {
        return $brand . ' focuses on elegant proportions, wearable comfort and a finish that pairs easily with casual or dressier looks. It appeals to shoppers searching for a neat gift option or a versatile daily accessory with a softer profile.';
    }
    if ($condition === 'pre-owned') {
        return $brand . ' is presented as a carefully selected pre-owned option for value-conscious customers who still want trusted styling, practical function and a more accessible price point. It is ideal for shoppers who enjoy branded watches without paying full new-market pricing.';
    }
    return $brand . ' offers reliable everyday wear, balanced styling and a presentation that helps shoppers compare features with confidence. It fits customers who want a watch that feels easy to own, easy to match and dependable over long-term use.';
}

function product_advantages($product) {
    $category = strtolower((string)($product['category_name'] ?? ''));
    $condition = strtolower((string)($product['item_condition'] ?? 'new'));
    if ($category === 'smart') {
        return [
            'Health and activity tracking for daily routines',
            'Fast access to calls, messages and app alerts',
            'Comfortable option for work, study and exercise',
            'Modern lifestyle design with easy usability',
            'Useful choice for customers who prefer one all-in-one wearable'
        ];
    }
    if ($category === 'luxury') {
        return [
            'Premium-looking finishing with stronger visual presence',
            'Suitable for gifting, formal wear and collection value',
            'Refined dial styling that feels more upscale on wrist',
            'Boutique-style presentation to improve buyer confidence',
            'A better fit for customers who value status and design detail'
        ];
    }
    if ($condition === 'pre-owned') {
        return [
            'Lower entry price compared with many brand-new listings',
            'Selected presentation aimed at practical value seekers',
            'Good option for buyers exploring branded watches first',
            'Secure checkout and tracked delivery still included',
            'Useful balance between price, styling and everyday function'
        ];
    }
    if ($category === 'sports') {
        return [
            'Readable layout that stays practical during movement',
            'Sporty styling that still works for casual daily wear',
            'Useful option for active buyers and students',
            'Built to feel dependable for repeated daily use',
            'Simple purchase flow with tracked delivery support'
        ];
    }
    if ($category === 'women') {
        return [
            'Elegant proportions for lighter and more comfortable wear',
            'Easy to match with casual, office and dressier outfits',
            'Gift-friendly presentation for birthdays and celebrations',
            'Balanced design focused on comfort and appearance',
            'Practical daily accessory with straightforward checkout support'
        ];
    }
    return [
        'Balanced style for office, class and weekend wear',
        'Easy-to-understand presentation for first-time buyers',
        'Secure checkout with customer account support',
        'Tracked delivery across Malaysia for better order visibility',
        'Reliable everyday option with accessible pricing and function'
    ];
}

function product_fit_for($product) {
    $category = strtolower((string)($product['category_name'] ?? ''));
    if ($category === 'smart') {
        return 'Best for users who want digital convenience, notifications and health features in one device.';
    }
    if ($category === 'luxury') {
        return 'Best for buyers who want a dressier watch with stronger premium appeal and gifting value.';
    }
    if ($category === 'sports') {
        return 'Best for active daily wear, easy dial reading and a more energetic look on wrist.';
    }
    if ($category === 'women') {
        return 'Best for customers who prefer elegant sizing, comfort and versatile outfit matching.';
    }
    return 'Best for customers who want a practical everyday watch with clean styling and dependable use.';
}

function order_status_counts($pdo) {
    $rows = $pdo->query("SELECT order_status, COUNT(*) AS total FROM orders GROUP BY order_status")->fetchAll();
    $counts = ['pending' => 0, 'processing' => 0, 'shipped' => 0, 'completed' => 0, 'cancelled' => 0];
    foreach ($rows as $row) {
        $status = strtolower((string)$row['order_status']);
        if (isset($counts[$status])) {
            $counts[$status] = (int)$row['total'];
        } elseif ($status === 'paid' || $status === 'out_for_delivery' || $status === 'shipping' || $status === 'delivered') {
            if ($status === 'paid') $counts['processing'] += (int)$row['total'];
            elseif ($status === 'out_for_delivery' || $status === 'shipping') $counts['shipped'] += (int)$row['total'];
            elseif ($status === 'delivered') $counts['completed'] += (int)$row['total'];
        }
    }
    return $counts;
}

function is_current_page($path) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return strpos($uri, $path) !== false;
}

function mail_settings() {
    $file = __DIR__ . '/mail.php';
    if (file_exists($file)) {
        $settings = require $file;
        if (is_array($settings)) {
            return $settings;
        }
    }
    return [
        'enabled' => false,
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'secure' => 'tls',
        'username' => '',
        'password' => '',
        'from_email' => '',
        'from_name' => 'TimePiece Gallery'
    ];
}

function smtp_read_line($socket) {
    $data = '';
    while (!feof($socket)) {
        $line = fgets($socket, 515);
        if ($line === false) {
            break;
        }
        $data .= $line;
        if (strlen($line) < 4 || $line[3] === ' ') {
            break;
        }
    }
    return $data;
}

function smtp_expect($socket, $codes) {
    $response = smtp_read_line($socket);
    foreach ((array)$codes as $code) {
        if (strpos($response, (string)$code) === 0) {
            return [true, $response];
        }
    }
    return [false, $response];
}

function smtp_send_cmd($socket, $command, $codes) {
    fwrite($socket, $command . "
");
    return smtp_expect($socket, $codes);
}

function send_app_mail($to, $subject, $body, &$error = '') {
    $cfg = mail_settings();
    if (empty($cfg['enabled']) || empty($cfg['username']) || empty($cfg['password']) || empty($cfg['from_email'])) {
        $error = 'SMTP mail is not configured yet.';
        return false;
    }

    $host = $cfg['host'] ?? 'smtp.gmail.com';
    $port = (int)($cfg['port'] ?? 587);
    $transport = ($cfg['secure'] ?? 'tls') === 'ssl' ? 'ssl://' : '';
    $socket = @stream_socket_client($transport . $host . ':' . $port, $errno, $errstr, 20);
    if (!$socket) {
        $error = 'SMTP connection failed: ' . $errstr;
        return false;
    }
    stream_set_timeout($socket, 20);

    list($ok, $resp) = smtp_expect($socket, [220]);
    if (!$ok) { fclose($socket); $error = trim($resp); return false; }
    list($ok, $resp) = smtp_send_cmd($socket, 'EHLO localhost', [250]);
    if (!$ok) { fclose($socket); $error = trim($resp); return false; }

    if (($cfg['secure'] ?? 'tls') === 'tls') {
        list($ok, $resp) = smtp_send_cmd($socket, 'STARTTLS', [220]);
        if (!$ok) { fclose($socket); $error = trim($resp); return false; }
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            $error = 'Unable to start TLS encryption.';
            return false;
        }
        list($ok, $resp) = smtp_send_cmd($socket, 'EHLO localhost', [250]);
        if (!$ok) { fclose($socket); $error = trim($resp); return false; }
    }

    list($ok, $resp) = smtp_send_cmd($socket, 'AUTH LOGIN', [334]);
    if (!$ok) { fclose($socket); $error = trim($resp); return false; }
    list($ok, $resp) = smtp_send_cmd($socket, base64_encode($cfg['username']), [334]);
    if (!$ok) { fclose($socket); $error = trim($resp); return false; }
    list($ok, $resp) = smtp_send_cmd($socket, base64_encode($cfg['password']), [235]);
    if (!$ok) { fclose($socket); $error = trim($resp); return false; }

    list($ok, $resp) = smtp_send_cmd($socket, 'MAIL FROM:<' . $cfg['from_email'] . '>', [250]);
    if (!$ok) { fclose($socket); $error = trim($resp); return false; }
    list($ok, $resp) = smtp_send_cmd($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
    if (!$ok) { fclose($socket); $error = trim($resp); return false; }
    list($ok, $resp) = smtp_send_cmd($socket, 'DATA', [354]);
    if (!$ok) { fclose($socket); $error = trim($resp); return false; }

    $headers = [
        'From: ' . ($cfg['from_name'] ?: 'TimePiece Gallery') . ' <' . $cfg['from_email'] . '>',
        'To: <' . $to . '>',
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8'
    ];
    $normalizedBody = str_replace(["
.", "
."], ["
..", "
.."], $body);
    $message = implode("
", $headers) . "

" . $normalizedBody . "
.";
    fwrite($socket, $message . "
");
    list($ok, $resp) = smtp_expect($socket, [250]);
    smtp_send_cmd($socket, 'QUIT', [221]);
    fclose($socket);
    if (!$ok) {
        $error = trim($resp);
        return false;
    }
    return true;
}
