<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

// ============================================================
// DATABASE HELPER
// ============================================================

function mailSetting(string $key, $default = null)
{
    $value = $_ENV[$key] ?? getenv($key);

    if (is_string($value)) {
        $value = trim($value);
    }

    return $value === null || $value === '' ? $default : $value;
}

function db(): PDO
{
    global $conn;

    if (!isset($conn) || !$conn instanceof PDO) {
        require __DIR__ . '/libs/connection.php';
    }

    return $conn;
}

// ============================================================
// EMAIL
// ============================================================

function sendEmail(string $to, string $subject, string $message, array $options = []): bool
{
    $host       = $options['host'] ?? mailSetting('SMTP_HOST', 'mail.egoire.com');
    $username   = $options['username'] ?? mailSetting('SMTP_USERNAME', 'podrska@egoire.com');
    $password   = $options['password'] ?? mailSetting('SMTP_PASSWORD', '');
    $port       = (int) ($options['port'] ?? mailSetting('SMTP_PORT', 465));
    $fromEmail  = $options['from_email'] ?? mailSetting('SMTP_FROM_EMAIL', $username);
    $fromName   = $options['from_name'] ?? mailSetting('SMTP_FROM_NAME', 'Egoire');
    $replyTo    = $options['reply_to'] ?? mailSetting('SMTP_REPLY_TO');

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $username;
        $mail->Password   = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $port;
        $mail->CharSet    = PHPMailer::CHARSET_UTF8;
        $mail->Encoding   = PHPMailer::ENCODING_BASE64;

        $mail->setFrom($fromEmail, $fromName);
        if ($replyTo) {
            $mail->addReplyTo($replyTo);
        }

        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Email not sent: ' . $mail->ErrorInfo);
        return false;
    }
}

// ============================================================
// BRANDS
// ============================================================

function fetchBrands(array $filters = []): array
{
    $sql = 'SELECT * FROM brands WHERE 1=1';
    $params = [];

    if (!empty($filters['active_only'])) {
        $sql .= ' AND is_active = 1';
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND name LIKE ?';
        $params[] = '%' . $filters['search'] . '%';
    }

    $sql .= ' ORDER BY ' . ($filters['order_by'] ?? 'sort_order ASC, name ASC');

    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int) $filters['limit'];
    }

    if (!empty($filters['offset'])) {
        $sql .= ' OFFSET ' . (int) $filters['offset'];
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetchBrandById(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM brands WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function fetchBrandBySlug(string $slug): ?array
{
    $stmt = db()->prepare('SELECT * FROM brands WHERE slug = ? AND is_active = 1');
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function saveBrand(array $data, ?int $id = null): int
{
    $conn = db();

    if ($id) {
        $stmt = $conn->prepare('
            UPDATE brands SET name = ?, slug = ?, description = ?, logo = COALESCE(?, logo), is_active = ?, sort_order = ?, updated_at = NOW()
            WHERE id = ?
        ');
        $stmt->execute([
            $data['name'], $data['slug'], $data['description'] ?? null,
            $data['logo'] ?? null, $data['is_active'] ?? 1, $data['sort_order'] ?? 0, $id
        ]);
        return $id;
    }

    $stmt = $conn->prepare('
        INSERT INTO brands (name, slug, description, logo, is_active, sort_order)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $data['name'], $data['slug'], $data['description'] ?? null,
        $data['logo'] ?? null, $data['is_active'] ?? 1, $data['sort_order'] ?? 0
    ]);
    return (int) $conn->lastInsertId();
}

function countBrands(array $filters = []): int
{
    $sql = 'SELECT COUNT(*) FROM brands WHERE 1=1';
    $params = [];

    if (!empty($filters['active_only'])) {
        $sql .= ' AND is_active = 1';
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

// ============================================================
// CATEGORIES
// ============================================================

function fetchCategories(array $filters = []): array
{
    $sql = 'SELECT * FROM categories WHERE 1=1';
    $params = [];

    if (!empty($filters['ids'])) {
        $placeholders = implode(',', array_fill(0, count($filters['ids']), '?'));
        $sql .= " AND id IN ($placeholders)";
        $params = array_merge($params, array_map('intval', $filters['ids']));
    }

    if (!empty($filters['active_only'])) {
        $sql .= " AND status = 'active'";
    } elseif (!empty($filters['status'])) {
        $sql .= ' AND status = ?';
        $params[] = $filters['status'];
    }

    if (array_key_exists('parent_id', $filters)) {
        if ($filters['parent_id'] === null) {
            $sql .= ' AND parent_id IS NULL';
        } else {
            $sql .= ' AND parent_id = ?';
            $params[] = $filters['parent_id'];
        }
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND name LIKE ?';
        $params[] = '%' . $filters['search'] . '%';
    }

    $sql .= ' ORDER BY ' . ($filters['order_by'] ?? 'sort_order ASC, name ASC');

    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int) $filters['limit'];
    }
    if (!empty($filters['offset'])) {
        $sql .= ' OFFSET ' . (int) $filters['offset'];
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetchActiveCategories(): array
{
    return fetchCategories(['active_only' => true]);
}

function fetchCategoryById(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function fetchCategoryBySlug(string $slug): ?array
{
    $stmt = db()->prepare("SELECT * FROM categories WHERE slug = ? AND status = 'active'");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function fetchSubcategories(int $parentId): array
{
    return fetchCategories(['parent_id' => $parentId, 'active_only' => true]);
}

function saveCategory(array $data, ?int $id = null): int
{
    $conn = db();

    if ($id) {
        $stmt = $conn->prepare('
            UPDATE categories SET parent_id = ?, name = ?, slug = ?, description = ?,
            image = COALESCE(?, image), status = ?, sort_order = ?, updated_at = NOW()
            WHERE id = ?
        ');
        $stmt->execute([
            $data['parent_id'] ?? null, $data['name'], $data['slug'],
            $data['description'] ?? null, $data['image'] ?? null,
            $data['status'] ?? 'active', $data['sort_order'] ?? 0, $id
        ]);
        return $id;
    }

    $stmt = $conn->prepare('
        INSERT INTO categories (parent_id, name, slug, description, image, status, sort_order)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $data['parent_id'] ?? null, $data['name'], $data['slug'],
        $data['description'] ?? null, $data['image'] ?? null,
        $data['status'] ?? 'active', $data['sort_order'] ?? 0
    ]);
    return (int) $conn->lastInsertId();
}

function countCategories(array $filters = []): int
{
    $sql = 'SELECT COUNT(*) FROM categories WHERE 1=1';
    $params = [];

    if (!empty($filters['active_only'])) {
        $sql .= " AND status = 'active'";
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

// ============================================================
// PRODUCTS
// ============================================================

function fetchProducts(array $filters = []): array
{
    $sql = 'SELECT p.*, b.name AS brand_name FROM products p LEFT JOIN brands b ON b.id = p.brand_id WHERE 1=1';
    $params = [];

    if (!empty($filters['active_only'])) {
        $sql .= ' AND p.is_active = 1';
    }

    if (!empty($filters['brand_id'])) {
        $sql .= ' AND p.brand_id = ?';
        $params[] = $filters['brand_id'];
    }

    if (!empty($filters['category_id'])) {
        $sql .= ' AND p.id IN (SELECT product_id FROM product_categories WHERE category_id = ?)';
        $params[] = $filters['category_id'];
    }

    if (!empty($filters['category_ids'])) {
        $placeholders = implode(',', array_fill(0, count($filters['category_ids']), '?'));
        $sql .= " AND p.id IN (SELECT product_id FROM product_categories WHERE category_id IN ($placeholders))";
        $params = array_merge($params, $filters['category_ids']);
    }

    if (!empty($filters['ids'])) {
        $placeholders = implode(',', array_fill(0, count($filters['ids']), '?'));
        $sql .= " AND p.id IN ($placeholders)";
        $params = array_merge($params, $filters['ids']);
    }

    if (!empty($filters['on_sale'])) {
        $sql .= ' AND p.on_sale = 1';
    }

    if (!empty($filters['flag'])) {
        $sql .= ' AND p.id IN (SELECT product_id FROM product_flags WHERE flag = ?)';
        $params[] = $filters['flag'];
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ? OR p.short_description LIKE ?)';
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if (!empty($filters['price_min'])) {
        $sql .= ' AND p.price >= ?';
        $params[] = (float) $filters['price_min'];
    }
    if (!empty($filters['price_max'])) {
        $sql .= ' AND p.price <= ?';
        $params[] = (float) $filters['price_max'];
    }

    $sql .= ' ORDER BY ' . ($filters['order_by'] ?? 'p.created_at DESC');

    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int) $filters['limit'];
    }
    if (!empty($filters['offset'])) {
        $sql .= ' OFFSET ' . (int) $filters['offset'];
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function countProducts(array $filters = []): int
{
    $sql = 'SELECT COUNT(DISTINCT p.id) FROM products p WHERE 1=1';
    $params = [];

    if (!empty($filters['active_only'])) {
        $sql .= ' AND p.is_active = 1';
    }

    if (!empty($filters['brand_id'])) {
        $sql .= ' AND p.brand_id = ?';
        $params[] = $filters['brand_id'];
    }

    if (!empty($filters['category_id'])) {
        $sql .= ' AND p.id IN (SELECT product_id FROM product_categories WHERE category_id = ?)';
        $params[] = $filters['category_id'];
    }

    if (!empty($filters['on_sale'])) {
        $sql .= ' AND p.on_sale = 1';
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ?)';
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function fetchProductById(int $id, bool $onlyActive = true): ?array
{
    $sql = 'SELECT p.*, b.name AS brand_name FROM products p LEFT JOIN brands b ON b.id = p.brand_id WHERE p.id = ?';
    $params = [$id];

    if ($onlyActive) {
        $sql .= ' AND p.is_active = 1';
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $product = $stmt->fetch();

    return $product ?: null;
}

function fetchProductBySlug(string $slug): ?array
{
    $stmt = db()->prepare('SELECT p.*, b.name AS brand_name FROM products p LEFT JOIN brands b ON b.id = p.brand_id WHERE p.slug = ? AND p.is_active = 1');
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function fetchProductImages(int $productId): array
{
    $stmt = db()->prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC');
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function fetchProductCategories(int $productId): array
{
    $stmt = db()->prepare('
        SELECT c.* FROM categories c
        JOIN product_categories pc ON pc.category_id = c.id
        WHERE pc.product_id = ?
        ORDER BY c.name ASC
    ');
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function fetchProductFlags(int $productId): array
{
    $stmt = db()->prepare('SELECT flag FROM product_flags WHERE product_id = ?');
    $stmt->execute([$productId]);
    return array_column($stmt->fetchAll(), 'flag');
}

function fetchProductStock(int $productId): ?array
{
    $stmt = db()->prepare('SELECT * FROM product_stock WHERE product_id = ?');
    $stmt->execute([$productId]);
    return $stmt->fetch() ?: null;
}

function productDisplayPrice(array $product): float
{
    if (!empty($product['on_sale']) && !empty($product['sale_price'])) {
        return (float) $product['sale_price'];
    }
    return (float) $product['price'];
}

function saveProduct(array $data, ?int $id = null): int
{
    $conn = db();

    if ($id) {
        $stmt = $conn->prepare('
            UPDATE products SET brand_id = ?, name = ?, slug = ?, short_description = ?,
            description = ?, how_to_use = ?, sku = ?, price = ?, sale_price = ?,
            on_sale = ?, is_active = ?, main_image = COALESCE(?, main_image), updated_at = NOW()
            WHERE id = ?
        ');
        $stmt->execute([
            $data['brand_id'] ?? null, $data['name'], $data['slug'],
            $data['short_description'] ?? null, $data['description'] ?? null,
            $data['how_to_use'] ?? null, $data['sku'] ?? null,
            $data['price'], $data['sale_price'] ?? null,
            $data['on_sale'] ?? 0, $data['is_active'] ?? 1,
            $data['main_image'] ?? null, $id
        ]);
        return $id;
    }

    $stmt = $conn->prepare('
        INSERT INTO products (brand_id, name, slug, short_description, description, how_to_use, sku, price, sale_price, on_sale, is_active, main_image)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $data['brand_id'] ?? null, $data['name'], $data['slug'],
        $data['short_description'] ?? null, $data['description'] ?? null,
        $data['how_to_use'] ?? null, $data['sku'] ?? null,
        $data['price'], $data['sale_price'] ?? null,
        $data['on_sale'] ?? 0, $data['is_active'] ?? 1,
        $data['main_image'] ?? null
    ]);
    $productId = (int) $conn->lastInsertId();

    // Initialize stock record
    $conn->prepare('INSERT INTO product_stock (product_id, quantity) VALUES (?, 0)')->execute([$productId]);

    return $productId;
}

function syncProductCategories(int $productId, array $categoryIds): void
{
    $conn = db();
    $conn->prepare('DELETE FROM product_categories WHERE product_id = ?')->execute([$productId]);

    $stmt = $conn->prepare('INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)');
    foreach (array_unique(array_map('intval', $categoryIds)) as $catId) {
        $stmt->execute([$productId, $catId]);
    }
}

function syncProductFlags(int $productId, array $flags): void
{
    $conn = db();
    $conn->prepare('DELETE FROM product_flags WHERE product_id = ?')->execute([$productId]);

    $allowed = ['new', 'on_sale', 'best_seller'];
    $stmt = $conn->prepare('INSERT INTO product_flags (product_id, flag) VALUES (?, ?)');
    foreach (array_intersect($flags, $allowed) as $flag) {
        $stmt->execute([$productId, $flag]);
    }
}

function updateProductStock(int $productId, int $quantity, ?int $lowThreshold = null): void
{
    $sql = 'INSERT INTO product_stock (product_id, quantity, low_stock_threshold) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)';
    $params = [$productId, $quantity, $lowThreshold ?? 5];

    if ($lowThreshold !== null) {
        $sql = 'INSERT INTO product_stock (product_id, quantity, low_stock_threshold) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), low_stock_threshold = VALUES(low_stock_threshold)';
    }

    db()->prepare($sql)->execute($params);
}

function addProductImage(int $productId, string $imagePath, ?string $altText = null, int $sortOrder = 0): int
{
    $conn = db();
    $stmt = $conn->prepare('INSERT INTO product_images (product_id, image_path, alt_text, sort_order) VALUES (?, ?, ?, ?)');
    $stmt->execute([$productId, $imagePath, $altText, $sortOrder]);
    return (int) $conn->lastInsertId();
}

function deleteProductImage(int $imageId): void
{
    db()->prepare('DELETE FROM product_images WHERE id = ?')->execute([$imageId]);
}

function bestSellerProducts(int $limit = 5): array
{
    $stmt = db()->prepare('
        SELECT p.*, b.name AS brand_name, SUM(oi.quantity) AS total_sold
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        LEFT JOIN brands b ON b.id = p.brand_id
        JOIN orders o ON o.id = oi.order_id AND o.status NOT IN ("canceled")
        WHERE p.is_active = 1
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT ?
    ');
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// ============================================================
// CART
// ============================================================

function cartSessionId(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    return session_id();
}

function fetchCartItems(?string $sessionId = null): array
{
    $sessionId = $sessionId ?? cartSessionId();

    $stmt = db()->prepare('
        SELECT c.id AS cart_id,
               c.product_id,
               c.quantity,
               c.created_at,
               p.name,
               p.slug,
               p.sku,
               p.price,
               p.sale_price,
               p.on_sale,
               p.main_image,
               p.is_active,
               b.name AS brand_name,
               ps.quantity AS stock_qty
        FROM cart c
        LEFT JOIN products p ON p.id = c.product_id
        LEFT JOIN brands b ON b.id = p.brand_id
        LEFT JOIN product_stock ps ON ps.product_id = p.id
        WHERE c.session_id = ?
        ORDER BY c.created_at ASC
    ');
    $stmt->execute([$sessionId]);
    return $stmt->fetchAll();
}

function calculateCartTotals(array $items): array
{
    $total = 0.0;
    $quantity = 0;

    foreach ($items as $item) {
        $unitPrice = isset($item['price']) ? productDisplayPrice($item) : 0;
        $qty = (int) ($item['quantity'] ?? 0);
        $total += $unitPrice * $qty;
        $quantity += $qty;
    }

    return [
        'total'    => round($total, 2),
        'quantity' => $quantity,
    ];
}

function addProductToCart(int $productId, int $quantity, ?string $sessionId = null): array
{
    $sessionId = $sessionId ?? cartSessionId();
    $quantity = max(1, $quantity);

    $product = fetchProductById($productId);
    if (!$product) {
        throw new RuntimeException('Proizvod nije pronađen.');
    }

    $stock = fetchProductStock($productId);
    if ($stock && $stock['track_stock'] && $stock['quantity'] <= 0) {
        throw new RuntimeException('Proizvod nije na stanju.');
    }

    $conn = db();

    $check = $conn->prepare('SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?');
    $check->execute([$sessionId, $productId]);
    $existing = $check->fetch();

    $requestedQty = $quantity;
    if ($existing) {
        $requestedQty = (int) $existing['quantity'] + $quantity;
    }

    // Check stock availability
    if ($stock && $stock['track_stock'] && $stock['quantity'] < $requestedQty) {
        throw new RuntimeException('Tražena količina nije dostupna.');
    }

    if ($existing) {
        $conn->prepare('UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?')
             ->execute([$requestedQty, $existing['id']]);
    } else {
        $userId = currentUserId();
        $conn->prepare('INSERT INTO cart (session_id, user_id, product_id, quantity) VALUES (?, ?, ?, ?)')
             ->execute([$sessionId, $userId, $productId, $quantity]);
    }

    return fetchCartItems($sessionId);
}

function updateCartItemQuantity(int $cartId, int $quantity): void
{
    if ($quantity <= 0) {
        removeCartItem($cartId);
        return;
    }

    db()->prepare('UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ? AND session_id = ?')
        ->execute([$quantity, $cartId, cartSessionId()]);
}

function removeCartItem(int $cartId): void
{
    db()->prepare('DELETE FROM cart WHERE id = ? AND session_id = ?')
        ->execute([$cartId, cartSessionId()]);
}

function clearCart(?string $sessionId = null): void
{
    $sessionId = $sessionId ?? cartSessionId();
    db()->prepare('DELETE FROM cart WHERE session_id = ?')->execute([$sessionId]);
}

function cartItemCount(?string $sessionId = null): int
{
    $sessionId = $sessionId ?? cartSessionId();
    $stmt = db()->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE session_id = ?');
    $stmt->execute([$sessionId]);
    return (int) $stmt->fetchColumn();
}

// ============================================================
// ORDERS
// ============================================================

function createOrder(array $orderData, array $cartItems, array $addressData): array
{
    $conn = db();
    $conn->beginTransaction();

    try {
        $orderNumber = generateOrderNumber();
        $userId = currentUserId();
        $cartTotals = calculateCartTotals($cartItems);

        // Calculate discounts
        $discountAmount = (float) ($orderData['discount_amount'] ?? 0);
        $loyaltyDiscount = (float) ($orderData['loyalty_discount'] ?? 0);
        $giftCardAmount = (float) ($orderData['gift_card_amount'] ?? 0);
        $shippingCost = (float) ($orderData['shipping_cost'] ?? 0);

        $totalPrice = $cartTotals['total'] - $discountAmount - $loyaltyDiscount - $giftCardAmount + $shippingCost;
        $totalPrice = max(0, $totalPrice);

        // Create order
        $stmt = $conn->prepare('
            INSERT INTO orders (order_number, user_id, session_id, status, payment_method,
                subtotal, discount_amount, loyalty_discount, gift_card_amount, shipping_cost,
                total_price, customer_name, email, phone, customer_note, is_gift_bag)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $orderNumber, $userId, cartSessionId(), 'new',
            $orderData['payment_method'] ?? 'cod',
            $cartTotals['total'], $discountAmount, $loyaltyDiscount,
            $giftCardAmount, $shippingCost, $totalPrice,
            sanitize($orderData['customer_name']),
            sanitizeEmail($orderData['email']),
            sanitize($orderData['phone']),
            sanitize($orderData['customer_note'] ?? ''),
            !empty($orderData['is_gift_bag']) ? 1 : 0,
        ]);

        $orderId = (int) $conn->lastInsertId();

        // Create order items (snapshot prices)
        $itemStmt = $conn->prepare('
            INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price, subtotal)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');

        foreach ($cartItems as $item) {
            $unitPrice = productDisplayPrice($item);
            $qty = (int) $item['quantity'];
            $itemStmt->execute([
                $orderId, $item['product_id'], $item['name'],
                $item['sku'] ?? null, $qty, $unitPrice, $unitPrice * $qty
            ]);

            // Decrease stock
            $conn->prepare('UPDATE product_stock SET quantity = GREATEST(0, quantity - ?) WHERE product_id = ? AND track_stock = 1')
                 ->execute([$qty, $item['product_id']]);
        }

        // Save order address snapshot
        $addrStmt = $conn->prepare('
            INSERT INTO order_addresses (order_id, type, first_name, last_name, company, address, address2, city, postal_code, country, phone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $addrStmt->execute([
            $orderId, 'shipping',
            sanitize($addressData['first_name']),
            sanitize($addressData['last_name']),
            sanitize($addressData['company'] ?? ''),
            sanitize($addressData['address']),
            sanitize($addressData['address2'] ?? ''),
            sanitize($addressData['city']),
            sanitize($addressData['postal_code']),
            $addressData['country'] ?? 'Srbija',
            sanitize($addressData['phone'] ?? $orderData['phone']),
        ]);

        // Handle loyalty points earning
        if ($userId) {
            $loyaltySettings = fetchLoyaltySettings();
            if ($loyaltySettings && $loyaltySettings['is_active']) {
                $pointsEarned = (int) floor($totalPrice * (float) $loyaltySettings['points_per_rsd']);
                if ($pointsEarned > 0) {
                    $conn->prepare('UPDATE orders SET loyalty_earned = ? WHERE id = ?')
                         ->execute([$pointsEarned, $orderId]);

                    addLoyaltyPoints($userId, $pointsEarned, 'earn', $orderId, "Porudžbina #$orderNumber");
                }
            }

            // Handle loyalty points spending
            if ($loyaltyDiscount > 0 && !empty($orderData['loyalty_points_spent'])) {
                $pointsSpent = (int) $orderData['loyalty_points_spent'];
                $conn->prepare('UPDATE orders SET loyalty_spent = ? WHERE id = ?')
                     ->execute([$pointsSpent, $orderId]);

                addLoyaltyPoints($userId, -$pointsSpent, 'spend', $orderId, "Iskorišćeno za porudžbinu #$orderNumber");
            }
        }

        // Handle gift card usage
        if ($giftCardAmount > 0 && !empty($orderData['gift_card_code'])) {
            applyGiftCardToOrder($orderData['gift_card_code'], $orderId, $giftCardAmount);
        }

        // Clear cart
        clearCart();

        // Add to email subscribers if opted in (guests)
        if (!$userId && !empty($orderData['marketing_optin'])) {
            $unsubToken = bin2hex(random_bytes(16));
            $conn->prepare('
                INSERT IGNORE INTO email_subscribers (name, email, source, unsubscribe_token)
                VALUES (?, ?, ?, ?)
            ')->execute([sanitize($orderData['customer_name']), sanitizeEmail($orderData['email']), 'checkout', $unsubToken]);
        }

        $conn->commit();

        // Fetch complete order for email
        $order = fetchOrderById($orderId);
        $items = fetchOrderItems($orderId);

        // Send customer email
        $emailBody = buildOrderEmail('created', $order, $items);
        sendEmail($order['email'], "Porudžbina #{$orderNumber} – Egoire", $emailBody);

        // Send admin notification
        $adminBody = buildAdminOrderEmail($order, $items);
        foreach (adminNotificationRecipients() as $adminEmail) {
            sendEmail($adminEmail, "Nova porudžbina #{$orderNumber}", $adminBody);
        }

        return ['success' => true, 'order_number' => $orderNumber, 'order_id' => $orderId];

    } catch (\Throwable $e) {
        $conn->rollBack();
        error_log('Order creation error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Greška pri kreiranju porudžbine.'];
    }
}

function fetchOrderByNumber(string $orderNumber): ?array
{
    $stmt = db()->prepare('SELECT * FROM orders WHERE order_number = ? LIMIT 1');
    $stmt->execute([$orderNumber]);
    return $stmt->fetch() ?: null;
}

function fetchOrderById(int $orderId): ?array
{
    $stmt = db()->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
    $stmt->execute([$orderId]);
    return $stmt->fetch() ?: null;
}

function fetchOrderItems(int $orderId): array
{
    $stmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC');
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function fetchOrderAddress(int $orderId, string $type = 'shipping'): ?array
{
    $stmt = db()->prepare('SELECT * FROM order_addresses WHERE order_id = ? AND type = ?');
    $stmt->execute([$orderId, $type]);
    return $stmt->fetch() ?: null;
}

function fetchOrderNotes(int $orderId): array
{
    $stmt = db()->prepare('SELECT * FROM order_notes WHERE order_id = ? ORDER BY created_at DESC');
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function addOrderNote(int $orderId, string $note, string $author = 'admin'): void
{
    db()->prepare('INSERT INTO order_notes (order_id, author, note) VALUES (?, ?, ?)')
        ->execute([$orderId, $author, sanitize($note)]);
}

function updateOrderStatus(int $orderId, string $newStatus): bool
{
    $allowed = ['new', 'processing', 'shipped', 'delivered', 'canceled'];
    if (!in_array($newStatus, $allowed, true)) {
        return false;
    }

    $order = fetchOrderById($orderId);
    if (!$order) {
        return false;
    }

    $oldStatus = $order['status'];
    if ($oldStatus === $newStatus) {
        return true;
    }

    db()->prepare('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?')
        ->execute([$newStatus, $orderId]);

    // Add admin note
    addOrderNote($orderId, "Status promenjen: " . orderStatusLabel($oldStatus) . " → " . orderStatusLabel($newStatus));

    // Send status change email
    $items = fetchOrderItems($orderId);
    $order['status'] = $newStatus; // update for email

    $emailType = match ($newStatus) {
        'processing' => 'accepted',
        'canceled'   => 'canceled',
        'shipped'    => 'shipped',
        'delivered'  => 'delivered',
        default      => null,
    };

    if ($emailType) {
        $emailBody = buildOrderEmail($emailType, $order, $items);
        sendEmail($order['email'], "Porudžbina #{$order['order_number']} – " . orderStatusLabel($newStatus), $emailBody);
    }

    // If canceled, restore stock
    if ($newStatus === 'canceled' && $oldStatus !== 'canceled') {
        foreach ($items as $item) {
            if ($item['product_id']) {
                db()->prepare('UPDATE product_stock SET quantity = quantity + ? WHERE product_id = ?')
                    ->execute([(int) $item['quantity'], $item['product_id']]);
            }
        }

        // Reverse loyalty points if any
        if ($order['loyalty_earned'] > 0 && $order['user_id']) {
            addLoyaltyPoints((int) $order['user_id'], -$order['loyalty_earned'], 'admin_remove', $orderId, "Porudžbina #{$order['order_number']} otkazana");
        }
        if ($order['loyalty_spent'] > 0 && $order['user_id']) {
            addLoyaltyPoints((int) $order['user_id'], $order['loyalty_spent'], 'admin_add', $orderId, "Povrat poena – porudžbina #{$order['order_number']} otkazana");
        }
    }

    return true;
}

function fetchOrders(array $filters = []): array
{
    $sql = 'SELECT o.* FROM orders o WHERE 1=1';
    $params = [];

    if (!empty($filters['status'])) {
        $sql .= ' AND o.status = ?';
        $params[] = $filters['status'];
    }

    if (!empty($filters['user_id'])) {
        $sql .= ' AND o.user_id = ?';
        $params[] = $filters['user_id'];
    }

    if (!empty($filters['email'])) {
        $sql .= ' AND o.email = ?';
        $params[] = $filters['email'];
    }

    if (!empty($filters['payment_method'])) {
        $sql .= ' AND o.payment_method = ?';
        $params[] = $filters['payment_method'];
    }

    if (!empty($filters['registered_only'])) {
        $sql .= ' AND o.user_id IS NOT NULL';
    }

    if (!empty($filters['guest_only'])) {
        $sql .= ' AND o.user_id IS NULL';
    }

    if (!empty($filters['date_from'])) {
        $sql .= ' AND DATE(o.created_at) >= ?';
        $params[] = $filters['date_from'];
    }

    if (!empty($filters['date_to'])) {
        $sql .= ' AND DATE(o.created_at) <= ?';
        $params[] = $filters['date_to'];
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND (o.order_number LIKE ? OR o.customer_name LIKE ? OR o.email LIKE ?)';
        $term = '%' . $filters['search'] . '%';
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $sql .= ' ORDER BY ' . ($filters['order_by'] ?? 'o.created_at DESC');

    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int) $filters['limit'];
    }
    if (!empty($filters['offset'])) {
        $sql .= ' OFFSET ' . (int) $filters['offset'];
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function countOrders(array $filters = []): int
{
    $sql = 'SELECT COUNT(*) FROM orders WHERE 1=1';
    $params = [];

    if (!empty($filters['status'])) {
        $sql .= ' AND status = ?';
        $params[] = $filters['status'];
    }
    if (!empty($filters['user_id'])) {
        $sql .= ' AND user_id = ?';
        $params[] = $filters['user_id'];
    }
    if (!empty($filters['date_from'])) {
        $sql .= ' AND DATE(created_at) >= ?';
        $params[] = $filters['date_from'];
    }
    if (!empty($filters['date_to'])) {
        $sql .= ' AND DATE(created_at) <= ?';
        $params[] = $filters['date_to'];
    }
    if (!empty($filters['search'])) {
        $sql .= ' AND (order_number LIKE ? OR customer_name LIKE ? OR email LIKE ?)';
        $term = '%' . $filters['search'] . '%';
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function adminNotificationRecipients(): array
{
    $configured = getenv('ORDER_NOTIFICATION_EMAILS');
    if ($configured) {
        $emails = array_filter(array_map('trim', explode(',', $configured)));
        if ($emails) {
            return $emails;
        }
    }
    return ['admin@egoire.com'];
}

// ============================================================
// ORDER EMAIL TEMPLATES
// ============================================================

function buildOrderEmail(string $type, array $order, array $items): string
{
    $intro = [
        'created'   => 'Vaša porudžbina je primljena i biće proverena u najkraćem roku.',
        'accepted'  => 'Porudžbina je potvrđena i priprema se za slanje.',
        'shipped'   => 'Vaša porudžbina je poslata! Očekujte isporuku u narednih 2-5 radnih dana.',
        'delivered'  => 'Vaša porudžbina je uspešno isporučena. Hvala na kupovini!',
        'canceled'  => 'Porudžbina je nažalost otkazana. Za dodatne informacije kontaktirajte nas.',
    ][$type] ?? '';

    $rows = '';
    foreach ($items as $item) {
        $rows .= '<tr>'
            . '<td style="padding:10px 8px;border-bottom:1px solid #f0e6f6;">' . htmlspecialchars($item['product_name']) . '</td>'
            . '<td style="padding:10px 8px;border-bottom:1px solid #f0e6f6;">' . (int) $item['quantity'] . 'x</td>'
            . '<td style="padding:10px 8px;border-bottom:1px solid #f0e6f6;text-align:right;">' . formatPrice((float) $item['subtotal']) . '</td>'
            . '</tr>';
    }

    $total = formatPrice((float) $order['total_price']);
    $name = htmlspecialchars($order['customer_name']);

    return <<<HTML
    <div style="font-family:'Helvetica Neue',Arial,sans-serif;color:#1a1a2e;max-width:600px;margin:0 auto;">
        <div style="background:#1a1a2e;padding:30px;text-align:center;">
            <h1 style="color:#c8a2d4;margin:0;font-size:28px;letter-spacing:2px;">EGOIRE</h1>
        </div>
        <div style="padding:30px;background:#fff;">
            <p style="font-size:16px;">Poštovani {$name},</p>
            <p style="font-size:15px;color:#555;">{$intro}</p>
            <table style="width:100%;border-collapse:collapse;margin:25px 0;">
                <thead>
                    <tr style="background:#f8f4fb;">
                        <th style="text-align:left;padding:10px 8px;color:#7e38bb;">Artikal</th>
                        <th style="text-align:left;padding:10px 8px;color:#7e38bb;">Količina</th>
                        <th style="text-align:right;padding:10px 8px;color:#7e38bb;">Iznos</th>
                    </tr>
                </thead>
                <tbody>{$rows}</tbody>
            </table>
            <p style="text-align:right;font-size:18px;margin-top:15px;"><strong>Ukupno:</strong> {$total}</p>
            <hr style="border:none;border-top:1px solid #f0e6f6;margin:25px 0;">
            <p style="font-size:13px;color:#888;">Porudžbina #{$order['order_number']} | {$order['created_at']}</p>
            <p style="font-size:13px;color:#888;">Hvala na poverenju,<br>Egoire tim</p>
        </div>
    </div>
    HTML;
}

function buildAdminOrderEmail(array $order, array $items): string
{
    $rows = '';
    foreach ($items as $item) {
        $rows .= '<tr>'
            . '<td style="padding:6px;border-bottom:1px solid #eee;">' . htmlspecialchars($item['product_name']) . '</td>'
            . '<td style="padding:6px;border-bottom:1px solid #eee;">' . (int) $item['quantity'] . '</td>'
            . '<td style="padding:6px;border-bottom:1px solid #eee;text-align:right;">' . formatPrice((float) $item['subtotal']) . '</td>'
            . '</tr>';
    }

    $total = formatPrice((float) $order['total_price']);

    return <<<HTML
    <div style="font-family:Arial,sans-serif;color:#111;">
        <h2 style="color:#7E38BB;">Nova porudžbina #{$order['order_number']}</h2>
        <p><strong>Kupac:</strong> {$order['customer_name']}</p>
        <p><strong>Email:</strong> {$order['email']} | <strong>Telefon:</strong> {$order['phone']}</p>
        <p><strong>Tip:</strong> {$order['payment_method']}</p>
        <table style="width:100%;border-collapse:collapse;margin-top:20px;">
            <thead>
                <tr style="background:#f6f6f6;">
                    <th style="text-align:left;padding:8px;">Artikal</th>
                    <th style="text-align:left;padding:8px;">Količina</th>
                    <th style="text-align:right;padding:8px;">Iznos</th>
                </tr>
            </thead>
            <tbody>{$rows}</tbody>
        </table>
        <p style="text-align:right;font-size:15px;margin-top:12px;"><strong>Ukupno:</strong> {$total}</p>
    </div>
    HTML;
}

// ============================================================
// USERS (Admin)
// ============================================================

function fetchUsers(array $filters = []): array
{
    $sql = 'SELECT u.*, ul.points_balance, ul.total_earned, ul.total_spent,
                   (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count,
                   (SELECT COALESCE(SUM(o.total_price), 0) FROM orders o WHERE o.user_id = u.id AND o.status != "canceled") AS total_spent_amount
            FROM users u
            LEFT JOIN user_loyalty ul ON ul.user_id = u.id
            WHERE 1=1';
    $params = [];

    if (!empty($filters['status'])) {
        $sql .= ' AND u.status = ?';
        $params[] = $filters['status'];
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)';
        $term = '%' . $filters['search'] . '%';
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $sql .= ' ORDER BY ' . ($filters['order_by'] ?? 'u.created_at DESC');

    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int) $filters['limit'];
    }
    if (!empty($filters['offset'])) {
        $sql .= ' OFFSET ' . (int) $filters['offset'];
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetchUserById(int $id): ?array
{
    $stmt = db()->prepare('
        SELECT u.*, ul.points_balance, ul.total_earned, ul.total_spent
        FROM users u
        LEFT JOIN user_loyalty ul ON ul.user_id = u.id
        WHERE u.id = ?
    ');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function countUsers(array $filters = []): int
{
    $sql = 'SELECT COUNT(*) FROM users WHERE 1=1';
    $params = [];

    if (!empty($filters['status'])) {
        $sql .= ' AND status = ?';
        $params[] = $filters['status'];
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)';
        $term = '%' . $filters['search'] . '%';
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function updateUserStatus(int $userId, string $status): void
{
    $allowed = ['active', 'blocked'];
    if (!in_array($status, $allowed, true)) {
        return;
    }
    db()->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([$status, $userId]);
}

// ============================================================
// LOYALTY PROGRAM
// ============================================================

function fetchLoyaltySettings(): ?array
{
    $stmt = db()->prepare('SELECT * FROM loyalty_settings ORDER BY id DESC LIMIT 1');
    $stmt->execute();
    return $stmt->fetch() ?: null;
}

function updateLoyaltySettings(array $data): void
{
    db()->prepare('
        UPDATE loyalty_settings SET
            points_per_rsd = ?, rsd_per_point = ?, min_points_redeem = ?,
            expiry_days = ?, is_active = ?
        ORDER BY id DESC LIMIT 1
    ')->execute([
        $data['points_per_rsd'], $data['rsd_per_point'],
        $data['min_points_redeem'], $data['expiry_days'] ?: null,
        $data['is_active'] ?? 1,
    ]);
}

function fetchUserLoyalty(int $userId): ?array
{
    $stmt = db()->prepare('SELECT * FROM user_loyalty WHERE user_id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

function addLoyaltyPoints(int $userId, int $points, string $type, ?int $orderId = null, ?string $description = null): void
{
    $conn = db();

    // Record transaction
    $conn->prepare('INSERT INTO loyalty_transactions (user_id, order_id, type, points, description) VALUES (?, ?, ?, ?, ?)')
         ->execute([$userId, $orderId, $type, $points, $description]);

    // Update balance
    if ($points > 0) {
        $conn->prepare('
            INSERT INTO user_loyalty (user_id, points_balance, total_earned)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE points_balance = points_balance + VALUES(points_balance),
                                    total_earned = total_earned + VALUES(total_earned)
        ')->execute([$userId, $points, $points]);
    } else {
        $absPoints = abs($points);
        $conn->prepare('
            INSERT INTO user_loyalty (user_id, points_balance, total_spent)
            VALUES (?, 0, ?)
            ON DUPLICATE KEY UPDATE points_balance = GREATEST(0, points_balance - ?),
                                    total_spent = total_spent + ?
        ')->execute([$userId, $absPoints, $absPoints, $absPoints]);
    }
}

function fetchLoyaltyTransactions(int $userId, int $limit = 50): array
{
    $stmt = db()->prepare('SELECT * FROM loyalty_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

function topLoyaltyUsers(int $limit = 10): array
{
    $stmt = db()->prepare('
        SELECT u.id, u.first_name, u.last_name, u.email, ul.points_balance, ul.total_earned, ul.total_spent
        FROM user_loyalty ul
        JOIN users u ON u.id = ul.user_id
        WHERE u.status = "active"
        ORDER BY ul.total_earned DESC
        LIMIT ?
    ');
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// ============================================================
// GIFT BAG SYSTEM
// ============================================================

function fetchGiftBagRules(bool $activeOnly = false): array
{
    $sql = 'SELECT * FROM gift_bag_rules';
    if ($activeOnly) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY created_at DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function fetchGiftBagRuleById(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM gift_bag_rules WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function fetchGiftBagDiscounts(int $ruleId): array
{
    $stmt = db()->prepare('SELECT * FROM gift_bag_discounts WHERE rule_id = ? ORDER BY threshold_value ASC');
    $stmt->execute([$ruleId]);
    return $stmt->fetchAll();
}

function calculateGiftBagDiscount(array $cartItems, float $cartTotal): array
{
    $rules = fetchGiftBagRules(true);
    $bestDiscount = 0;
    $appliedRule = null;
    $appliedDiscount = null;
    $productCount = array_sum(array_column($cartItems, 'quantity'));

    foreach ($rules as $rule) {
        // Check minimum requirements
        if ($rule['min_products'] && $productCount < $rule['min_products']) {
            continue;
        }
        if ($rule['min_order_value'] && $cartTotal < (float) $rule['min_order_value']) {
            continue;
        }

        $discounts = fetchGiftBagDiscounts((int) $rule['id']);
        foreach ($discounts as $disc) {
            $meets = false;
            if ($disc['threshold_type'] === 'product_count' && $productCount >= (int) $disc['threshold_value']) {
                $meets = true;
            } elseif ($disc['threshold_type'] === 'order_value' && $cartTotal >= (float) $disc['threshold_value']) {
                $meets = true;
            }

            if ($meets) {
                $amount = $disc['discount_type'] === 'percentage'
                    ? $cartTotal * ((float) $disc['discount_value'] / 100)
                    : (float) $disc['discount_value'];

                if ($amount > $bestDiscount) {
                    $bestDiscount = $amount;
                    $appliedRule = $rule;
                    $appliedDiscount = $disc;
                }
            }
        }
    }

    return [
        'discount_amount' => round($bestDiscount, 2),
        'rule'            => $appliedRule,
        'discount'        => $appliedDiscount,
    ];
}

function saveGiftBagRule(array $data, ?int $id = null): int
{
    $conn = db();

    if ($id) {
        $conn->prepare('
            UPDATE gift_bag_rules SET name = ?, min_products = ?, min_order_value = ?,
            allowed_categories = ?, allowed_brands = ?, is_active = ?
            WHERE id = ?
        ')->execute([
            $data['name'], $data['min_products'] ?? null, $data['min_order_value'] ?? null,
            $data['allowed_categories'] ?? null, $data['allowed_brands'] ?? null,
            $data['is_active'] ?? 1, $id
        ]);
        return $id;
    }

    $conn->prepare('
        INSERT INTO gift_bag_rules (name, min_products, min_order_value, allowed_categories, allowed_brands, is_active)
        VALUES (?, ?, ?, ?, ?, ?)
    ')->execute([
        $data['name'], $data['min_products'] ?? null, $data['min_order_value'] ?? null,
        $data['allowed_categories'] ?? null, $data['allowed_brands'] ?? null,
        $data['is_active'] ?? 1
    ]);
    return (int) $conn->lastInsertId();
}

// ============================================================
// GIFT CARD SYSTEM
// ============================================================

function fetchGiftCardSettings(): ?array
{
    $stmt = db()->prepare('SELECT * FROM gift_card_settings ORDER BY id DESC LIMIT 1');
    $stmt->execute();
    return $stmt->fetch() ?: null;
}

function fetchGiftCardAmounts(): array
{
    $stmt = db()->prepare('SELECT * FROM gift_card_amounts WHERE is_active = 1 ORDER BY sort_order ASC');
    $stmt->execute();
    return $stmt->fetchAll();
}

function generateGiftCardCode(): string
{
    do {
        $code = strtoupper(substr(bin2hex(random_bytes(8)), 0, 16));
        $code = implode('-', str_split($code, 4)); // XXXX-XXXX-XXXX-XXXX
        $stmt = db()->prepare('SELECT id FROM gift_cards WHERE code = ?');
        $stmt->execute([$code]);
    } while ($stmt->fetch());

    return $code;
}

function createGiftCard(float $amount, ?string $purchaserEmail = null, ?string $recipientEmail = null, ?string $recipientName = null, ?string $message = null): array
{
    $settings = fetchGiftCardSettings();
    if (!$settings || !$settings['is_active']) {
        return ['success' => false, 'error' => 'Gift Card sistem nije aktivan.'];
    }

    $code = generateGiftCardCode();
    $expiresAt = $settings['default_expiry_days']
        ? date('Y-m-d', strtotime("+{$settings['default_expiry_days']} days"))
        : null;

    db()->prepare('
        INSERT INTO gift_cards (code, initial_amount, balance, purchaser_email, recipient_email, recipient_name, message, expires_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ')->execute([$code, $amount, $amount, $purchaserEmail, $recipientEmail, $recipientName, $message, $expiresAt]);

    return ['success' => true, 'code' => $code];
}

function fetchGiftCardByCode(string $code): ?array
{
    $code = strtoupper(trim($code));
    $stmt = db()->prepare('SELECT * FROM gift_cards WHERE code = ?');
    $stmt->execute([$code]);
    return $stmt->fetch() ?: null;
}

function validateGiftCard(string $code): array
{
    $card = fetchGiftCardByCode($code);
    if (!$card) {
        return ['valid' => false, 'error' => 'Nevažeći Gift Card kod.'];
    }

    if ($card['status'] === 'used' || $card['status'] === 'expired') {
        return ['valid' => false, 'error' => 'Gift Card je već iskorišćen ili istekao.'];
    }

    if ($card['expires_at'] && strtotime($card['expires_at']) < time()) {
        db()->prepare("UPDATE gift_cards SET status = 'expired' WHERE id = ?")->execute([$card['id']]);
        return ['valid' => false, 'error' => 'Gift Card je istekao.'];
    }

    if ((float) $card['balance'] <= 0) {
        return ['valid' => false, 'error' => 'Gift Card nema raspoloživo stanje.'];
    }

    return ['valid' => true, 'balance' => (float) $card['balance'], 'card' => $card];
}

function applyGiftCardToOrder(string $code, int $orderId, float $amountUsed): void
{
    $card = fetchGiftCardByCode($code);
    if (!$card) return;

    $newBalance = max(0, (float) $card['balance'] - $amountUsed);
    $newStatus = $newBalance <= 0 ? 'used' : 'partially_used';

    db()->prepare('UPDATE gift_cards SET balance = ?, status = ? WHERE id = ?')
        ->execute([$newBalance, $newStatus, $card['id']]);

    db()->prepare('INSERT INTO gift_card_usage (gift_card_id, order_id, amount_used) VALUES (?, ?, ?)')
        ->execute([$card['id'], $orderId, $amountUsed]);
}

function fetchGiftCards(array $filters = []): array
{
    $sql = 'SELECT * FROM gift_cards WHERE 1=1';
    $params = [];

    if (!empty($filters['status'])) {
        $sql .= ' AND status = ?';
        $params[] = $filters['status'];
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND (code LIKE ? OR purchaser_email LIKE ? OR recipient_email LIKE ?)';
        $term = '%' . $filters['search'] . '%';
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $sql .= ' ORDER BY created_at DESC';

    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int) $filters['limit'];
    }
    if (!empty($filters['offset'])) {
        $sql .= ' OFFSET ' . (int) $filters['offset'];
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ============================================================
// MARKETING / EMAIL CAMPAIGNS
// ============================================================

function marketingSubscribe(string $name, string $email, string $source = 'manual'): void
{
    $email = mb_strtolower(trim($email), 'UTF-8');
    if ($email === '') return;

    $stmt = db()->prepare('SELECT id FROM email_subscribers WHERE email = ?');
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        db()->prepare('UPDATE email_subscribers SET name = ?, is_active = 1 WHERE id = ?')
            ->execute([sanitize($name), $existing['id']]);
    } else {
        $token = bin2hex(random_bytes(16));
        db()->prepare('INSERT INTO email_subscribers (name, email, source, unsubscribe_token) VALUES (?, ?, ?, ?)')
            ->execute([sanitize($name), $email, $source, $token]);
    }
}

function marketingUnsubscribeByToken(string $token): bool
{
    if ($token === '') return false;

    $stmt = db()->prepare('UPDATE email_subscribers SET is_active = 0 WHERE unsubscribe_token = ?');
    $stmt->execute([$token]);
    return $stmt->rowCount() > 0;
}

function fetchEmailSubscribers(array $filters = []): array
{
    $sql = 'SELECT es.*, u.first_name AS user_first_name, u.last_name AS user_last_name
            FROM email_subscribers es
            LEFT JOIN users u ON u.id = es.user_id
            WHERE 1=1';
    $params = [];

    if (!empty($filters['active_only'])) {
        $sql .= ' AND es.is_active = 1';
    }

    if (!empty($filters['registered_only'])) {
        $sql .= ' AND es.user_id IS NOT NULL';
    }

    if (!empty($filters['guests_only'])) {
        $sql .= ' AND es.user_id IS NULL';
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND (es.name LIKE ? OR es.email LIKE ?)';
        $term = '%' . $filters['search'] . '%';
        $params[] = $term;
        $params[] = $term;
    }

    $sql .= ' ORDER BY es.created_at DESC';

    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int) $filters['limit'];
    }
    if (!empty($filters['offset'])) {
        $sql .= ' OFFSET ' . (int) $filters['offset'];
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function countEmailSubscribers(array $filters = []): int
{
    $sql = 'SELECT COUNT(*) FROM email_subscribers WHERE 1=1';
    $params = [];

    if (!empty($filters['active_only'])) {
        $sql .= ' AND is_active = 1';
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function createEmailCampaign(string $subject, string $body, string $target = 'all'): int
{
    $conn = db();
    $conn->prepare('INSERT INTO email_campaigns (subject, body, target) VALUES (?, ?, ?)')
         ->execute([sanitize($subject), $body, $target]);
    return (int) $conn->lastInsertId();
}

function sendEmailCampaign(int $campaignId): array
{
    $conn = db();
    $campaign = $conn->prepare('SELECT * FROM email_campaigns WHERE id = ?');
    $campaign->execute([$campaignId]);
    $campaign = $campaign->fetch();

    if (!$campaign || $campaign['status'] === 'sent') {
        return ['success' => false, 'error' => 'Kampanja nije pronađena ili je već poslata.'];
    }

    // Get subscribers based on target
    $filters = ['active_only' => true];
    if ($campaign['target'] === 'registered') {
        $filters['registered_only'] = true;
    } elseif ($campaign['target'] === 'guests') {
        $filters['guests_only'] = true;
    }

    $subscribers = fetchEmailSubscribers($filters);

    $conn->prepare("UPDATE email_campaigns SET status = 'sending', total_recipients = ? WHERE id = ?")
         ->execute([count($subscribers), $campaignId]);

    $sentCount = 0;
    $failedCount = 0;

    foreach ($subscribers as $sub) {
        // Add unsubscribe link to body
        $unsubUrl = baseUrl() . '/unsubscribe?token=' . urlencode($sub['unsubscribe_token']);
        $body = $campaign['body'] . '<p style="font-size:12px;color:#999;margin-top:30px;"><a href="' . $unsubUrl . '">Odjava sa mailing liste</a></p>';

        $status = sendEmail($sub['email'], $campaign['subject'], $body) ? 'sent' : 'failed';

        $conn->prepare('INSERT INTO email_campaign_logs (campaign_id, subscriber_id, email, status) VALUES (?, ?, ?, ?)')
             ->execute([$campaignId, $sub['id'], $sub['email'], $status]);

        $status === 'sent' ? $sentCount++ : $failedCount++;
    }

    $finalStatus = $failedCount === count($subscribers) ? 'failed' : 'sent';

    $conn->prepare("UPDATE email_campaigns SET status = ?, sent_count = ?, failed_count = ?, sent_at = NOW() WHERE id = ?")
         ->execute([$finalStatus, $sentCount, $failedCount, $campaignId]);

    return ['success' => true, 'sent' => $sentCount, 'failed' => $failedCount];
}

function fetchEmailCampaigns(int $limit = 50): array
{
    $stmt = db()->prepare('SELECT * FROM email_campaigns ORDER BY created_at DESC LIMIT ?');
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// ============================================================
// CONTACT MESSAGES
// ============================================================

function createContactMessage(string $name, string $email, ?string $phone, ?string $subject, string $message): int
{
    $conn = db();
    $conn->prepare('INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)')
         ->execute([sanitize($name), sanitizeEmail($email), sanitize($phone ?? ''), sanitize($subject ?? ''), sanitize($message)]);
    return (int) $conn->lastInsertId();
}

function fetchContactMessages(array $filters = []): array
{
    $sql = 'SELECT * FROM contact_messages WHERE 1=1';
    $params = [];

    if (!empty($filters['status'])) {
        $sql .= ' AND status = ?';
        $params[] = $filters['status'];
    }

    $sql .= ' ORDER BY created_at DESC';

    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int) $filters['limit'];
    }
    if (!empty($filters['offset'])) {
        $sql .= ' OFFSET ' . (int) $filters['offset'];
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function updateContactMessageStatus(int $id, string $status): void
{
    $allowed = ['new', 'read', 'replied', 'archived'];
    if (!in_array($status, $allowed, true)) return;
    db()->prepare('UPDATE contact_messages SET status = ? WHERE id = ?')->execute([$status, $id]);
}

// ============================================================
// BLOG
// ============================================================

function fetchBlogPosts(array $filters = []): array
{
    $sql = 'SELECT * FROM blog_posts WHERE 1=1';
    $params = [];

    if (!empty($filters['published_only'])) {
        $sql .= " AND status = 'published' AND published_at <= NOW()";
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND (title LIKE ? OR excerpt LIKE ?)';
        $term = '%' . $filters['search'] . '%';
        $params[] = $term;
        $params[] = $term;
    }

    $sql .= ' ORDER BY ' . ($filters['order_by'] ?? 'published_at DESC, created_at DESC');

    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int) $filters['limit'];
    }
    if (!empty($filters['offset'])) {
        $sql .= ' OFFSET ' . (int) $filters['offset'];
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetchBlogPostBySlug(string $slug): ?array
{
    $stmt = db()->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function fetchBlogPostById(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM blog_posts WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function saveBlogPost(array $data, ?int $id = null): int
{
    $conn = db();

    if ($id) {
        $conn->prepare('
            UPDATE blog_posts SET title = ?, slug = ?, excerpt = ?, body = ?,
            featured_image = COALESCE(?, featured_image), status = ?,
            published_at = ?, updated_at = NOW()
            WHERE id = ?
        ')->execute([
            $data['title'], $data['slug'], $data['excerpt'] ?? null,
            $data['body'], $data['featured_image'] ?? null,
            $data['status'] ?? 'draft', $data['published_at'] ?? null, $id
        ]);
        return $id;
    }

    $conn->prepare('
        INSERT INTO blog_posts (title, slug, excerpt, body, featured_image, status, published_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ')->execute([
        $data['title'], $data['slug'], $data['excerpt'] ?? null,
        $data['body'], $data['featured_image'] ?? null,
        $data['status'] ?? 'draft', $data['published_at'] ?? null
    ]);
    return (int) $conn->lastInsertId();
}

// ============================================================
// PAGES & FAQ
// ============================================================

function fetchPage(string $slug): ?array
{
    $stmt = db()->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'active'");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function fetchFaqs(bool $activeOnly = true): array
{
    $sql = 'SELECT * FROM faq';
    if ($activeOnly) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY sort_order ASC';
    $stmt = db()->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// ============================================================
// ANALYTICS / DASHBOARD
// ============================================================

function dashboardStats(string $period = 'today'): array
{
    $conn = db();

    $dateCondition = match ($period) {
        'today'      => "DATE(created_at) = CURDATE()",
        'this_week'  => "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)",
        'this_month' => "YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())",
        'this_year'  => "YEAR(created_at) = YEAR(CURDATE())",
        default      => "1=1",
    };

    // Orders
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_price), 0) AS total_revenue FROM orders WHERE status != 'canceled' AND $dateCondition");
    $stmt->execute();
    $orderStats = $stmt->fetch();

    // New users
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE $dateCondition");
    $stmt->execute();
    $newUsers = (int) $stmt->fetchColumn();

    // Total users
    $totalUsers = (int) $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Active loyalty users
    $loyaltyUsers = (int) $conn->query("SELECT COUNT(*) FROM user_loyalty WHERE points_balance > 0")->fetchColumn();

    // Total subscribers
    $totalSubscribers = (int) $conn->query("SELECT COUNT(*) FROM email_subscribers WHERE is_active = 1")->fetchColumn();

    return [
        'total_orders'      => (int) $orderStats['total_orders'],
        'total_revenue'     => (float) $orderStats['total_revenue'],
        'new_users'         => $newUsers,
        'total_users'       => $totalUsers,
        'loyalty_users'     => $loyaltyUsers,
        'total_subscribers' => $totalSubscribers,
    ];
}

function ordersByDay(string $dateFrom = '', string $dateTo = ''): array
{
    if (!$dateFrom) $dateFrom = date('Y-m-d', strtotime('-30 days'));
    if (!$dateTo) $dateTo = date('Y-m-d');

    $stmt = db()->prepare("
        SELECT DATE(created_at) AS date, COUNT(*) AS orders, COALESCE(SUM(total_price), 0) AS revenue
        FROM orders
        WHERE status != 'canceled' AND DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    return $stmt->fetchAll();
}

function revenueByPeriod(string $dateFrom, string $dateTo): array
{
    $stmt = db()->prepare("
        SELECT DATE(created_at) AS date, COUNT(*) AS orders, COALESCE(SUM(total_price), 0) AS revenue
        FROM orders
        WHERE status != 'canceled' AND DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    return $stmt->fetchAll();
}

function topCategories(int $limit = 5): array
{
    $stmt = db()->prepare("
        SELECT c.id, c.name, COUNT(oi.id) AS items_sold, COALESCE(SUM(oi.subtotal), 0) AS revenue
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        JOIN product_categories pc ON pc.product_id = p.id
        JOIN categories c ON c.id = pc.category_id
        JOIN orders o ON o.id = oi.order_id AND o.status != 'canceled'
        GROUP BY c.id
        ORDER BY revenue DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function analyticsExportOrders(string $dateFrom, string $dateTo): array
{
    $stmt = db()->prepare("
        SELECT o.order_number, o.customer_name, o.email, o.phone, o.status,
               o.payment_method, o.subtotal, o.discount_amount, o.total_price,
               o.created_at, IF(o.user_id IS NOT NULL, 'Registrovan', 'Gost') AS user_type
        FROM orders o
        WHERE DATE(o.created_at) BETWEEN ? AND ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    return $stmt->fetchAll();
}

function updateAnalyticsDaily(): void
{
    $today = date('Y-m-d');

    $conn = db();

    $stats = $conn->prepare("
        SELECT
            COUNT(*) AS total_orders,
            COALESCE(SUM(total_price), 0) AS total_revenue,
            COALESCE(AVG(total_price), 0) AS avg_order_value,
            SUM(CASE WHEN user_id IS NULL THEN 1 ELSE 0 END) AS guest_orders,
            SUM(CASE WHEN user_id IS NOT NULL THEN 1 ELSE 0 END) AS registered_orders
        FROM orders
        WHERE DATE(created_at) = ? AND status != 'canceled'
    ");
    $stats->execute([$today]);
    $data = $stats->fetch();

    $newUsers = $conn->prepare("SELECT COUNT(*) FROM users WHERE DATE(created_at) = ?");
    $newUsers->execute([$today]);
    $newUsersCount = (int) $newUsers->fetchColumn();

    $newSubs = $conn->prepare("SELECT COUNT(*) FROM email_subscribers WHERE DATE(created_at) = ?");
    $newSubs->execute([$today]);
    $newSubsCount = (int) $newSubs->fetchColumn();

    $conn->prepare("
        INSERT INTO analytics_daily (date, total_orders, total_revenue, new_users, new_subscribers, guest_orders, registered_orders, avg_order_value)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            total_orders = VALUES(total_orders), total_revenue = VALUES(total_revenue),
            new_users = VALUES(new_users), new_subscribers = VALUES(new_subscribers),
            guest_orders = VALUES(guest_orders), registered_orders = VALUES(registered_orders),
            avg_order_value = VALUES(avg_order_value)
    ")->execute([
        $today, $data['total_orders'], $data['total_revenue'],
        $newUsersCount, $newSubsCount, $data['guest_orders'],
        $data['registered_orders'], $data['avg_order_value'],
    ]);
}

// ============================================================
// USER ADDRESSES
// ============================================================

function fetchUserAddresses(int $userId): array
{
    $stmt = db()->prepare('SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function saveUserAddress(int $userId, array $data, ?int $id = null): int
{
    $conn = db();

    // If setting as default, clear other defaults first
    if (!empty($data['is_default'])) {
        $conn->prepare('UPDATE user_addresses SET is_default = 0 WHERE user_id = ?')->execute([$userId]);
    }

    if ($id) {
        $conn->prepare('
            UPDATE user_addresses SET label = ?, first_name = ?, last_name = ?, company = ?,
            address = ?, address2 = ?, city = ?, postal_code = ?, country = ?, phone = ?, is_default = ?
            WHERE id = ? AND user_id = ?
        ')->execute([
            $data['label'] ?? 'default', sanitize($data['first_name']), sanitize($data['last_name']),
            sanitize($data['company'] ?? ''), sanitize($data['address']), sanitize($data['address2'] ?? ''),
            sanitize($data['city']), sanitize($data['postal_code']),
            $data['country'] ?? 'Srbija', sanitize($data['phone'] ?? ''),
            $data['is_default'] ?? 0, $id, $userId
        ]);
        return $id;
    }

    $conn->prepare('
        INSERT INTO user_addresses (user_id, label, first_name, last_name, company, address, address2, city, postal_code, country, phone, is_default)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ')->execute([
        $userId, $data['label'] ?? 'default', sanitize($data['first_name']), sanitize($data['last_name']),
        sanitize($data['company'] ?? ''), sanitize($data['address']), sanitize($data['address2'] ?? ''),
        sanitize($data['city']), sanitize($data['postal_code']),
        $data['country'] ?? 'Srbija', sanitize($data['phone'] ?? ''),
        $data['is_default'] ?? 0
    ]);
    return (int) $conn->lastInsertId();
}

function deleteUserAddress(int $addressId, int $userId): void
{
    db()->prepare('DELETE FROM user_addresses WHERE id = ? AND user_id = ?')->execute([$addressId, $userId]);
}

