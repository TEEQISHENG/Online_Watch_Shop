CREATE DATABASE IF NOT EXISTS online_watch_shop;
USE online_watch_shop;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    address TEXT NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin', 'superadmin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    item_condition ENUM('New','Pre-Owned') NOT NULL DEFAULT 'New',
    image_url VARCHAR(255) NOT NULL DEFAULT 'assets/images/default_watch.jpg',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    delivery_address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status VARCHAR(30) NOT NULL DEFAULT 'pending',
    order_status VARCHAR(30) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reset_token VARCHAR(100) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO users (id, full_name, email, phone, address, password_hash, role) VALUES
(1, 'System Superadmin', 'superadmin@watchstore.com', '0123456789', 'Kuala Lumpur', '$2y$12$0HIqE7e1IueN.kS2kv.Tz.rxNoqa0exNRiVECJ5CS9wVEJI4IOlUy', 'superadmin'),
(2, 'Store Admin', 'admin@watchstore.com', '01122334455', 'Melaka', '$2y$12$0HIqE7e1IueN.kS2kv.Tz.rxNoqa0exNRiVECJ5CS9wVEJI4IOlUy', 'admin'),
(3, 'Demo Customer', 'customer@watchstore.com', '0199988877', 'Taman Sri Emas Dua, Melaka', '$2y$12$hGOHyinYWbS3UMfry7cN5.z84mGC5Zp433btFsZQFd.kX7PFI3UOm', 'customer');

INSERT INTO categories (id, category_name) VALUES
(1, 'Luxury'),
(2, 'Sports'),
(3, 'Classic'),
(4, 'Women'),
(5, 'Smart'),
(6, 'Pre-Owned');

INSERT INTO products (id, category_id, product_name, description, price, stock_quantity, item_condition, image_url, is_active) VALUES
(1, 1, 'Rolex Datejust 36', 'Iconic dress-sport silhouette with polished case, fluted bezel styling and versatile everyday appeal.', 38900.00, 3, 'New', 'assets/images/products/rolex_datejust_36.jpg', 1),
(2, 1, 'Omega Seamaster Diver 300M', 'Wave-dial dive watch aesthetic with premium finishing, ceramic accents and strong collector interest.', 28600.00, 4, 'New', 'assets/images/products/omega_seamaster_diver_300m.jpg', 1),
(3, 1, 'Tudor Black Bay 58', 'Compact vintage-inspired diver with clean proportions and a very wearable daily profile.', 17900.00, 5, 'New', 'assets/images/products/tudor_black_bay_58.jpg', 1),
(4, 1, 'Grand Seiko SBGA211 Snowflake', 'High-end spring drive favorite known for its textured dial and lightweight titanium feel.', 26700.00, 2, 'New', 'assets/images/products/grand_seiko_sbga211_snowflake.jpg', 1),
(5, 4, 'Cartier Tank Must', 'Elegant rectangular watch that anchors the women’s and dress-focused edit.', 15700.00, 4, 'New', 'assets/images/products/cartier_tank_must.jpg', 1),
(6, 2, 'Longines HydroConquest 41', 'Sport-luxury dive watch with balanced sizing and a polished bracelet presentation.', 8450.00, 6, 'New', 'assets/images/products/longines_hydroconquest_41.jpg', 1),
(7, 2, 'TAG Heuer Formula 1 Quartz', 'Modern quartz sports watch with a bold dial layout for everyday casual wear.', 8350.00, 7, 'New', 'assets/images/products/tag_heuer_formula_1_quartz.jpg', 1),
(8, 3, 'Tissot PRX Powermatic 80', 'Integrated-bracelet automatic with strong value positioning and premium retro styling.', 3400.00, 8, 'New', 'assets/images/products/tissot_prx_powermatic_80.jpg', 1),
(9, 3, 'Tissot PRX 40mm', 'Quartz PRX with clean integrated-bracelet design and a versatile blue-dial look.', 1800.00, 8, 'New', 'assets/images/products/tissot_prx_40mm.jpg', 1),
(10, 3, 'Seiko Presage SSA457J1', 'Cocktail Time limited edition feel with dressier detailing and automatic movement.', 2900.00, 6, 'New', 'assets/images/products/seiko_presage_ssa457j1.jpg', 1),
(11, 2, 'Seiko 5 Sports SSK003K1 GMT', 'Accessible GMT sports watch for customers who want travel-friendly functionality.', 2200.00, 9, 'New', 'assets/images/products/seiko_5_sports_ssk003k1_gmt.jpg', 1),
(12, 3, 'Citizen Tsuyosa NJ0150-81Z', 'Colorful integrated-bracelet automatic suited to younger buyers and smart-casual styling.', 1650.00, 11, 'New', 'assets/images/products/citizen_tsuyosa_nj0150_81z.jpg', 1),
(13, 3, 'Citizen Tsuyosa NJ0200-50E', 'Compact automatic option with textured dial and an easy everyday stainless steel look.', 2030.00, 9, 'New', 'assets/images/products/citizen_tsuyosa_nj0200_50e.jpg', 1),
(14, 2, 'G-SHOCK GA-2100-1A1', 'Slim all-black analog-digital sports model with strong streetwear appeal.', 549.00, 14, 'New', 'assets/images/products/g_shock_ga_2100_1a1.jpg', 1),
(15, 2, 'G-SHOCK GM-2100-1A', 'Metal-covered take on the octagonal G-SHOCK silhouette for a dressier sport look.', 999.00, 10, 'New', 'assets/images/products/g_shock_gm_2100_1a.jpg', 1),
(16, 3, 'Orient Bambino Version 8', 'Affordable dress watch with domed crystal styling and classic office-ready proportions.', 1450.00, 7, 'New', 'assets/images/products/orient_bambino_version_8.jpg', 1),
(17, 3, 'Hamilton Khaki Field Mechanical', 'Military-inspired field watch with hand-wound character and straightforward legibility.', 2750.00, 5, 'New', 'assets/images/products/hamilton_khaki_field_mechanical.jpg', 1),
(18, 4, 'Swatch Clearly Gent', 'Transparent playful design that broadens the store mix with entry-price fashion appeal.', 410.00, 15, 'New', 'assets/images/products/swatch_clearly_gent.jpg', 1),
(19, 3, 'Fossil Neutra Chronograph', 'Value-driven chronograph with fashion-forward styling and easy gifting appeal.', 899.00, 12, 'New', 'assets/images/products/fossil_neutra_chronograph.jpg', 1),
(20, 4, 'Daniel Wellington Petite Melrose', 'Minimalist ladies watch with rose-tone styling and strong casual gift positioning.', 790.00, 13, 'New', 'assets/images/products/daniel_wellington_petite_melrose.jpg', 1),
(21, 5, 'Apple Watch Series 10 GPS 42mm', 'Mainstream smartwatch pick for wellness, notifications and a more connected daily routine.', 1899.00, 10, 'New', 'assets/images/products/apple_watch_series_10_gps_42mm.jpg', 1),
(22, 5, 'Garmin Venu 3', 'Fitness-forward smartwatch with strong health tracking and premium lifestyle positioning.', 2299.00, 7, 'New', 'assets/images/products/garmin_venu_3.jpg', 1),
(23, 5, 'Samsung Galaxy Watch7 44mm', 'Android-friendly smartwatch option with modern design and daily smart features.', 1199.00, 9, 'New', 'assets/images/products/samsung_galaxy_watch7_44mm.jpg', 1),
(24, 6, 'Pre-Owned Rolex Oyster Perpetual 36', 'Pre-owned luxury staple positioned for buyers entering premium mechanical watches.', 26300.00, 2, 'Pre-Owned', 'assets/images/products/pre_owned_rolex_oyster_perpetual_36.jpg', 1),
(25, 6, 'Pre-Owned Omega Speedmaster Reduced', 'Pre-owned chronograph choice for enthusiasts who want heritage styling at a lower entry point.', 14900.00, 2, 'Pre-Owned', 'assets/images/products/pre_owned_omega_speedmaster_reduced.jpg', 1),
(26, 6, 'Pre-Owned Tudor Royal 38', 'Integrated-bracelet pre-owned option with a polished look and approachable premium pricing.', 7800.00, 3, 'Pre-Owned', 'assets/images/products/pre_owned_tudor_royal_38.jpg', 1),
(27, 6, 'Pre-Owned Longines Master Collection', 'Classic pre-owned dress watch for customers who prefer refined styling over sporty designs.', 5200.00, 3, 'Pre-Owned', 'assets/images/products/pre_owned_longines_master_collection.jpg', 1),
(28, 6, 'Pre-Owned Seiko Prospex Turtle', 'Affordable pre-owned diver with a loyal fan base and practical everyday toughness.', 1450.00, 4, 'Pre-Owned', 'assets/images/products/pre_owned_seiko_prospex_turtle.jpg', 1),
(29, 6, 'Pre-Owned TAG Heuer Aquaracer 300', 'Pre-owned dive watch choice with strong brand recognition and everyday wear value.', 8900.00, 2, 'Pre-Owned', 'assets/images/products/pre_owned_tag_heuer_aquaracer_300.jpg', 1),
(30, 6, 'Pre-Owned Cartier Ballon Bleu 33', 'Pre-owned luxury ladies option for shoppers who want a softer, jewelry-leaning silhouette.', 18500.00, 2, 'Pre-Owned', 'assets/images/products/pre_owned_cartier_ballon_bleu_33.jpg', 1);

INSERT INTO orders (id, user_id, delivery_address, payment_method, total_amount, payment_status, order_status, created_at) VALUES
(1, 3, 'Taman Sri Emas Dua, Melaka', 'Online Banking', 3400.00, 'paid', 'completed', '2026-03-10 10:00:00'),
(2, 3, 'Taman Sri Emas Dua, Melaka', 'Credit Card', 1199.00, 'paid', 'shipping', '2026-03-12 15:30:00'),
(3, 3, 'Taman Sri Emas Dua, Melaka', 'E-Wallet', 1450.00, 'paid', 'pending', '2026-03-14 09:20:00'),
(4, 3, 'Taman Sri Emas Dua, Melaka', 'Online Banking', 26300.00, 'paid', 'completed', '2026-03-15 11:00:00');

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES
(1, 8, 1, 3400.00, 3400.00),
(2, 23, 1, 1199.00, 1199.00),
(3, 28, 1, 1450.00, 1450.00),
(4, 24, 1, 26300.00, 26300.00);
