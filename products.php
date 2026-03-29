<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
$page_title = 'Shop';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'name_asc';
$condition = trim($_GET['condition'] ?? '');
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();

$where = ["p.is_active = 1"];
$params = [];
if ($category_id > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $category_id;
}
if ($condition !== '' && in_array($condition, ['New', 'Pre-Owned'])) {
    $where[] = "p.item_condition = ?";
    $params[] = $condition;
}
if ($search !== '') {
    $where[] = "(p.product_name LIKE ? OR p.description LIKE ? OR c.category_name LIKE ?)";
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like);
}

$orderSql = "p.product_name ASC";
switch ($sort) {
    case 'price_asc': $orderSql = 'p.price ASC'; break;
    case 'price_desc': $orderSql = 'p.price DESC'; break;
    case 'stock_desc': $orderSql = 'p.stock_quantity DESC'; break;
    case 'latest': $orderSql = 'p.id DESC'; break;
}

$sql = "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE " . implode(' AND ', $where) . " ORDER BY $orderSql";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<section class="shop-hero surface">
    <div>
        <span class="badge-source">30+ curated watches</span>
        <h1 class="page-headline shop-title">Find your next watch</h1>
        <p>Browse new and pre-owned watches across luxury, sports, classic, women’s and smart categories. The catalog has been reorganized with cleaner cards, steadier spacing and more store-like presentation.</p>
    </div>
    <div class="shop-hero-note">
        <strong>What you can filter:</strong>
        <span>Brand or model search · Category · Condition · Price sorting</span>
    </div>
</section>

<section class="toolbar toolbar-premium">
    <form method="get" class="toolbar-form toolbar-form-premium">
        <input class="input" type="text" name="search" placeholder="Search brand, model or category" value="<?php echo h($search); ?>">
        <select class="input" name="category_id">
            <option value="0">All categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo (int)$cat['id']; ?>" <?php echo $category_id === (int)$cat['id'] ? 'selected' : ''; ?>><?php echo h($cat['category_name']); ?></option>
            <?php endforeach; ?>
        </select>
        <select class="input" name="condition">
            <option value="">All conditions</option>
            <option value="New" <?php echo $condition === 'New' ? 'selected' : ''; ?>>New</option>
            <option value="Pre-Owned" <?php echo $condition === 'Pre-Owned' ? 'selected' : ''; ?>>Pre-Owned</option>
        </select>
        <select class="input" name="sort">
            <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Sort: Name</option>
            <option value="latest" <?php echo $sort === 'latest' ? 'selected' : ''; ?>>Newest first</option>
            <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price low to high</option>
            <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price high to low</option>
            <option value="stock_desc" <?php echo $sort === 'stock_desc' ? 'selected' : ''; ?>>Highest stock</option>
        </select>
        <button class="btn-dark" type="submit">Apply filters</button>
    </form>
</section>

<div class="section-header">
    <div>
        <h2><?php echo count($products); ?> products found</h2>
        <p>Every card now follows the same visual structure so the storefront looks more polished and consistent.</p>
    </div>
</div>

<div class="product-grid product-grid-shop">
<?php foreach ($products as $row): ?>
    <article class="product-card product-card-shop">
        <div class="product-media product-media-shop">
            <img src="<?php echo product_image_url($row['image_url']); ?>" alt="<?php echo h($row['product_name']); ?>">
        </div>
        <div class="product-body product-body-shop">
            <div class="product-topline">
                <span class="badge-pill"><?php echo h($row['category_name']); ?></span>
                <?php echo condition_badge($row['item_condition'] ?? 'New'); ?>
            </div>
            <h3 class="product-title"><?php echo h($row['product_name']); ?></h3>
            <div class="product-model"><?php echo h(product_tagline($row['product_name'], $row['category_name'], $row['item_condition'] ?? 'New')); ?></div>
            <p class="product-description"><?php echo h($row['description']); ?></p>
            <div class="product-service-row">
                <span>Stock <?php echo (int)$row['stock_quantity']; ?></span>
                <span><?php echo strtolower($row['item_condition']) === 'pre-owned' ? 'Condition checked' : 'Ready to ship'; ?></span>
            </div>
            <div class="product-card-bottom">
                <div class="product-price">RM <?php echo number_format($row['price'], 2); ?></div>
                <a href="product_details.php?id=<?php echo (int)$row['id']; ?>" class="btn-dark">View details</a>
            </div>
        </div>
    </article>
<?php endforeach; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
