<?php require_once __DIR__ . "/../config/functions.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? h($page_title) : 'TimePiece Gallery'; ?></title>
    <link rel="stylesheet" href="<?php echo asset_url('style.css'); ?>">
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <button type="button" class="return-button" onclick="window.history.back()" aria-label="Go back" title="Go back"><span class="return-button-icon">&#8592;</span></button>
        <a class="brand" href="<?php echo base_url(); ?>/index.php">
            <span class="brand-mark">TG</span>
            <span class="brand-text">
                <strong>TimePiece Gallery</strong>
                <span>New arrivals, pre-owned finds, secure checkout</span>
            </span>
        </a>
        <nav class="nav-links">
            <a class="nav-link <?php echo is_current_page('/index.php') || str_ends_with($_SERVER['REQUEST_URI'] ?? '', base_url() . '/') ? 'active' : ''; ?>" href="<?php echo base_url(); ?>/index.php">Home</a>
            <a class="nav-link <?php echo is_current_page('/products.php') ? 'active' : ''; ?>" href="<?php echo base_url(); ?>/products.php">Shop</a>
            <?php if (is_logged_in() && !in_array($_SESSION['role'], ['admin', 'superadmin'])): ?>
                <a class="nav-link <?php echo is_current_page('/cart.php') ? 'active' : ''; ?>" href="<?php echo base_url(); ?>/cart.php">Cart</a>
                <a class="nav-link <?php echo is_current_page('/order_history.php') ? 'active' : ''; ?>" href="<?php echo base_url(); ?>/order_history.php">Orders</a>
                <a class="nav-link <?php echo is_current_page('/profile.php') ? 'active' : ''; ?>" href="<?php echo base_url(); ?>/profile.php">Profile</a>
            <?php endif; ?>
            <?php if (is_admin_logged_in()): ?>
                <a class="nav-link <?php echo is_current_page('/admin/') ? 'active' : ''; ?>" href="<?php echo base_url(); ?>/admin/dashboard.php">Admin</a>
            <?php endif; ?>
        </nav>
        <div class="nav-actions">
            <?php if (is_logged_in()): ?>
                <span class="badge-pill">Hello, <?php echo h($_SESSION['user_name']); ?></span>
                <a class="nav-chip alt" href="<?php echo logout_url(); ?>">Logout</a>
            <?php else: ?>
                <a class="nav-chip" href="<?php echo base_url(); ?>/register.php">Register</a>
                <a class="nav-chip" href="<?php echo base_url(); ?>/login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="site-shell">
<?php display_flash(); ?>
