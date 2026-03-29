<?php
$host = 'localhost';
$username = 'root';
$password = '';

$database_options = [
    'online_watch_shop',
    'watch_store_fyp',
    'online watch shop'
];

$pdo = null;
$last_error = '';

foreach ($database_options as $dbname) {
    try {
        $testPdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $testPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $testPdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $stmt = $testPdo->query("SHOW TABLES LIKE 'products'");
        if ($stmt->fetch()) {
            $pdo = $testPdo;
            break;
        }
    } catch (PDOException $e) {
        $last_error = $e->getMessage();
    }
}

if (!$pdo) {
    die('Database connected, but required tables were not found. Please import database/watch_store.sql into phpMyAdmin. Supported database names: online_watch_shop, watch_store_fyp, online watch shop. Last error: ' . $last_error);
}
?>
