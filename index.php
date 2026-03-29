<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
$page_title = 'Home - TimePiece Gallery';
$featured = $pdo->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 ORDER BY FIELD(p.item_condition,'New','Pre-Owned'), p.id ASC LIMIT 8")->fetchAll();
$featuredNew = $pdo->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 AND p.item_condition = 'New' ORDER BY p.id ASC LIMIT 4")->fetchAll();
$featuredPreowned = $pdo->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 AND p.item_condition = 'Pre-Owned' ORDER BY p.id ASC LIMIT 4")->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero hero-home">
    <div class="hero-grid hero-grid-home">
        <div class="hero-copy">
            <span class="badge-source">Curated boutique · New arrivals · Pre-owned selection</span>
            <h1>Timeless watches, modern shopping experience.</h1>
            <p>Explore a cleaner online storefront for everyday classics, sports icons, luxury upgrades, women’s watches and selected pre-owned pieces. The layout is now product-led, more commercial and easier to present like a real e-commerce brand.</p>
            <div class="hero-actions">
                <a class="btn-main" href="products.php">Shop all watches</a>
                <a class="btn-outline" href="products.php?condition=Pre-Owned">Browse pre-owned</a>
            </div>
            <div class="trust-strip">
                <?php foreach (array_slice(store_trust_items(), 0, 4) as $item): ?>
                    <span class="trust-chip"><?php echo h($item); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="hero-visual card-box hero-visual-box">
            <div class="hero-visual-copy">
                <div class="eyebrow">Featured today</div>
                <h3>Shop by how you wear your watch</h3>
                <p>From polished daily pieces to rugged sports models and value-driven pre-owned entries, the catalog is arranged to look and feel like a commercial storefront.</p>
            </div>
            <div class="hero-mini-grid">
                <a class="mini-collection-card" href="products.php?category_id=1"><span>Luxury</span><strong>Collector-led icons</strong></a>
                <a class="mini-collection-card" href="products.php?category_id=2"><span>Sports</span><strong>Active everyday wear</strong></a>
                <a class="mini-collection-card" href="products.php?category_id=3"><span>Classic</span><strong>Office to weekend</strong></a>
                <a class="mini-collection-card" href="products.php?condition=Pre-Owned"><span>Pre-Owned</span><strong>Better value entry</strong></a>
            </div>
        </div>
    </div>
</section>

<section class="value-strip">
    <div class="value-item"><strong>New &amp; pre-owned</strong><span>One storefront for both first-hand and pre-owned buying journeys.</span></div>
    <div class="value-item"><strong>Secure checkout</strong><span>Cart, checkout and order history kept simple for easier conversion.</span></div>
    <div class="value-item"><strong>Customer confidence</strong><span>Clear conditions, clean product detail pages and tracked order flow.</span></div>
</section>

<div class="section-header">
    <div>
        <h2>Collections worth exploring</h2>
        <p>The homepage now introduces the store the way modern e-commerce sites do: with curated collections, brand trust and buying guidance.</p>
    </div>
</div>
<section class="feature-grid collections-grid">
    <div class="feature-card feature-card-dark"><h3>Luxury icons</h3><p>Refined models with stronger finishing, collector appeal and premium styling for buyers ready to step up.</p><a class="text-link" href="products.php?category_id=1">Explore luxury</a></div>
    <div class="feature-card"><h3>Daily wear classics</h3><p>Dress watches, integrated-bracelet designs and office-ready pieces that fit a broad range of budgets.</p><a class="text-link" href="products.php?category_id=3">View classics</a></div>
    <div class="feature-card"><h3>Sports &amp; smart</h3><p>Durable options and connected watches for active routines, travel, workouts and everyday convenience.</p><a class="text-link" href="products.php?category_id=2">Shop active</a></div>
</section>

<div class="section-header">
    <div>
        <h2>Best sellers &amp; spotlight models</h2>
        <p>Clean cards, aligned spacing and stronger product imagery make the catalog feel more premium and easier to browse.</p>
    </div>
    <a class="btn-dark" href="products.php">View full catalog</a>
</div>
<div class="product-grid product-grid-home">
<?php foreach ($featured as $row): ?>
    <article class="product-card">
        <div class="product-media product-media-home">
            <img src="<?php echo product_image_url($row['image_url']); ?>" alt="<?php echo h($row['product_name']); ?>">
        </div>
        <div class="product-body">
            <div class="product-topline">
                <span class="badge-pill"><?php echo h($row['category_name']); ?></span>
                <?php echo condition_badge($row['item_condition'] ?? 'New'); ?>
            </div>
            <h3 class="product-title"><?php echo h($row['product_name']); ?></h3>
            <div class="product-model"><?php echo h(product_tagline($row['product_name'], $row['category_name'], $row['item_condition'] ?? 'New')); ?></div>
            <div class="product-price">RM <?php echo number_format($row['price'], 2); ?></div>
            <div class="detail-actions"><a href="product_details.php?id=<?php echo (int)$row['id']; ?>" class="btn-dark" style="flex:1;">View details</a></div>
        </div>
    </article>
<?php endforeach; ?>
</div>

<div class="section-header">
    <div>
        <h2>Why shoppers feel more confident here</h2>
        <p>Instead of internal or school-facing content, the storefront now talks like a real online retailer.</p>
    </div>
</div>
<section class="feature-grid">
    <div class="feature-card"><h3>Product-first browsing</h3><p>Large visual cards, simple filters and clearer detail pages help customers compare quickly without visual clutter.</p></div>
    <div class="feature-card"><h3>Trusted pre-owned section</h3><p>Pre-owned listings are separated with visible condition badges so customers can understand what they are buying at a glance.</p></div>
    <div class="feature-card"><h3>Smooth account experience</h3><p>Login, cart, checkout, delivery address changes and order history are all kept in one straightforward customer journey.</p></div>
</section>

<div class="section-header">
    <div>
        <h2>Shop by condition</h2>
        <p>New stock for current retail shoppers, and curated pre-owned options for value-focused enthusiasts.</p>
    </div>
</div>
<div class="dual-grid">
    <section class="surface showcase-block">
        <div class="showcase-head">
            <div>
                <span class="badge-status">New</span>
                <h3>Fresh arrivals</h3>
                <p class="muted">Current pieces chosen for broad commercial appeal, ranging from entry price points to premium buys.</p>
            </div>
            <a class="text-link" href="products.php?condition=New">See all new</a>
        </div>
        <div class="mini-product-grid">
            <?php foreach ($featuredNew as $row): ?>
                <a class="mini-product" href="product_details.php?id=<?php echo (int)$row['id']; ?>">
                    <img src="<?php echo product_image_url($row['image_url']); ?>" alt="<?php echo h($row['product_name']); ?>">
                    <div>
                        <strong><?php echo h($row['product_name']); ?></strong>
                        <span>RM <?php echo number_format($row['price'], 0); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="surface showcase-block">
        <div class="showcase-head">
            <div>
                <span class="badge-source">Pre-Owned</span>
                <h3>Curated pre-owned picks</h3>
                <p class="muted">A more diverse catalog with recognizable names for customers entering the premium segment at a friendlier price.</p>
            </div>
            <a class="text-link" href="products.php?condition=Pre-Owned">See all pre-owned</a>
        </div>
        <div class="mini-product-grid">
            <?php foreach ($featuredPreowned as $row): ?>
                <a class="mini-product" href="product_details.php?id=<?php echo (int)$row['id']; ?>">
                    <img src="<?php echo product_image_url($row['image_url']); ?>" alt="<?php echo h($row['product_name']); ?>">
                    <div>
                        <strong><?php echo h($row['product_name']); ?></strong>
                        <span>RM <?php echo number_format($row['price'], 0); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<div class="section-header">
    <div>
        <h2>Customer reassurance</h2>
        <p>Short, believable trust messaging that suits a commercial watch store better than system metrics.</p>
    </div>
</div>
<section class="testimonial-grid">
    <div class="testimonial-card"><div class="stars">★★★★★</div><p>“The layout feels clean and premium. I can find luxury pieces and affordable daily wear without getting lost.”</p><strong>Jason, Melaka</strong></div>
    <div class="testimonial-card"><div class="stars">★★★★★</div><p>“I like that the pre-owned section is clearly labelled. It makes the catalog feel more realistic and trustworthy.”</p><strong>Ain, Kuala Lumpur</strong></div>
    <div class="testimonial-card"><div class="stars">★★★★★</div><p>“Checkout and order history are straightforward, which is exactly what I expect from a modern online store.”</p><strong>Faris, Johor</strong></div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
