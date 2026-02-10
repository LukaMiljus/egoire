<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

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

function sendEmail(string $to, string $subject, string $message, array $options = []): bool
{
    $host       = $options['host'] ?? mailSetting('SMTP_HOST', 'mail.victorysport.cc');
    $username   = $options['username'] ?? mailSetting('SMTP_USERNAME', 'podrska@victorysport.cc');
    $password   = $options['password'] ?? mailSetting('SMTP_PASSWORD', '');
    $port       = (int) ($options['port'] ?? mailSetting('SMTP_PORT', 465));
    $fromEmail  = $options['from_email'] ?? mailSetting('SMTP_FROM_EMAIL', $username);
    $fromName   = $options['from_name'] ?? mailSetting('SMTP_FROM_NAME', 'Victory Sport');
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

function productDisplayPrice(array $product): float
{
    if (!empty($product['on_sale']) && !empty($product['sale_price'])) {
        return (float) $product['sale_price'];
    }

    return (float) $product['price'];
}

function fetchCategories(array $filters = []): array
{
    $sql        = 'SELECT id, name, image, status FROM categories';
    $conditions = [];
    $params     = [];

    if (!empty($filters['ids'])) {
        $placeholders = implode(',', array_fill(0, count($filters['ids']), '?'));
        $conditions[] = "id IN ($placeholders)";
        $params       = array_merge($params, array_map('intval', $filters['ids']));
    }

    if (!empty($filters['active_only'])) {
        $conditions[] = "status = 'active'";
    } elseif (!empty($filters['status'])) {
        $conditions[] = 'status = ?';
        $params[]     = $filters['status'];
    }

    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $orderBy = $filters['order_by'] ?? 'created_at DESC';
    $sql    .= ' ORDER BY ' . $orderBy;

    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int) $filters['limit'];
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

function fetchActiveCategories(): array
{
    return fetchCategories(['active_only' => true]);
}

function fetchProducts(array $filters = []): array
{
    $sql = 'SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE 1=1';
    $params = [];

    if (!empty($filters['active_only'])) {
        $sql .= ' AND p.is_active = 1';
    }

    if (!empty($filters['category_ids'])) {
        $placeholders = implode(',', array_fill(0, count($filters['category_ids']), '?'));
        $sql         .= " AND p.category_id IN ($placeholders)";
        $params       = array_merge($params, $filters['category_ids']);
    }

    if (!empty($filters['ids'])) {
        $placeholders = implode(',', array_fill(0, count($filters['ids']), '?'));
        $sql         .= " AND p.id IN ($placeholders)";
        $params       = array_merge($params, $filters['ids']);
    }

    if (!empty($filters['on_sale'])) {
        $sql .= ' AND p.on_sale = 1';
    }

    if (!empty($filters['search'])) {
        $sql       .= ' AND (p.name LIKE ? OR p.code LIKE ?)';
        $searchTerm = '%' . $filters['search'] . '%';
        $params[]   = $searchTerm;
        $params[]   = $searchTerm;
    }

    $sql .= ' ORDER BY ' . ($filters['order_by'] ?? 'p.created_at DESC');

    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int) $filters['limit'];
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchProductById(int $id, bool $onlyActive = true): ?array
{
    $sql    = 'SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = ?';
    $params = [$id];

    if ($onlyActive) {
        $sql .= ' AND p.is_active = 1';
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    return $product ?: null;
}

function fetchSets(array $filters = []): array
{
    $sql = 'SELECT * FROM sets WHERE 1=1';
    $params = [];

    if (!empty($filters['active_only'])) {
        $sql .= ' AND active = 1';
    }

    if (!empty($filters['ids'])) {
        $placeholders = implode(',', array_fill(0, count($filters['ids']), '?'));
        $sql         .= " AND id IN ($placeholders)";
        $params       = array_merge($params, $filters['ids']);
    }

    $sql .= ' ORDER BY created_at DESC';

    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int) $filters['limit'];
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchSetById(int $id, bool $onlyActive = true): ?array
{
    $sql    = 'SELECT * FROM sets WHERE id = ?';
    $params = [$id];

    if ($onlyActive) {
        $sql .= ' AND active = 1';
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $set = $stmt->fetch(PDO::FETCH_ASSOC);

    return $set ?: null;
}

function fetchSetProducts(int $setId): array
{
    $stmt = db()->prepare('
        SELECT p.*, si.product_id, c.name AS category_name
        FROM set_items si
        JOIN products p ON p.id = si.product_id
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE si.set_id = ?
    ');
    $stmt->execute([$setId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function productStockMap(array $product): array
{
    return [
        'XS'  => (int) ($product['stock_xs'] ?? 0),
        'S'   => (int) ($product['stock_s'] ?? 0),
        'M'   => (int) ($product['stock_m'] ?? 0),
        'L'   => (int) ($product['stock_l'] ?? 0),
        'XL'  => (int) ($product['stock_xl'] ?? 0),
        'XXL' => (int) ($product['stock_xxl'] ?? 0),
    ];
}

function availableSizes(array $product): array
{
    return array_keys(array_filter(productStockMap($product), static fn ($qty) => $qty > 0));
}

function isVideoAsset(?string $path): bool
{
    if (!$path) {
        return false;
    }

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return in_array($extension, ['mp4', 'webm', 'mov', 'ogg'], true);
}

function marketingSubscribe(string $name, string $email): void
{
    $email = trim($email);
    if ($email === '') {
        return;
    }

    $stmt = db()->prepare('SELECT id, unsubscribe_token FROM marketing_subscribers WHERE email = ?');
    $stmt->execute([$email]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        marketingEnsureUnsubscribeToken((int) $existing['id'], $existing['unsubscribe_token'] ?? null);

        $update = db()->prepare('UPDATE marketing_subscribers SET name = :name WHERE id = :id');
        $update->execute([
            ':name' => $name,
            ':id'   => $existing['id'],
        ]);
    } else {
        $token  = marketingGenerateUnsubscribeToken();
        $insert = db()->prepare('INSERT INTO marketing_subscribers (name, email, unsubscribe_token) VALUES (:name, :email, :token)');
        $insert->execute([
            ':name'  => $name,
            ':email' => $email,
            ':token' => $token,
        ]);
    }
}

function marketingGenerateUnsubscribeToken(): string
{
    return bin2hex(random_bytes(16));
}

function marketingEnsureUnsubscribeToken(int $subscriberId, ?string $existingToken = null): string
{
    if ($existingToken) {
        return $existingToken;
    }

    $token = marketingGenerateUnsubscribeToken();
    $stmt  = db()->prepare('UPDATE marketing_subscribers SET unsubscribe_token = :token WHERE id = :id');
    $stmt->execute([
        ':token' => $token,
        ':id'    => $subscriberId,
    ]);

    return $token;
}

function marketingSubscriberByToken(string $token): ?array
{
    if ($token === '') {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM marketing_subscribers WHERE unsubscribe_token = ? LIMIT 1');
    $stmt->execute([$token]);

    $subscriber = $stmt->fetch(PDO::FETCH_ASSOC);
    return $subscriber ?: null;
}

function marketingUnsubscribeByToken(string $token): bool
{
    if ($token === '') {
        return false;
    }

    $stmt = db()->prepare('DELETE FROM marketing_subscribers WHERE unsubscribe_token = ?');
    $stmt->execute([$token]);

    return $stmt->rowCount() > 0;
}

function marketingUnsubscribeByEmail(string $email): bool
{
    $email = trim($email);
    if ($email === '') {
        return false;
    }

    $stmt = db()->prepare('DELETE FROM marketing_subscribers WHERE email = ?');
    $stmt->execute([$email]);

    return $stmt->rowCount() > 0;
}

function buildOrderEmail(string $type, array $order, array $items): string
{
    $intro = [
        'created'  => 'Vaša porudžbina je primljena i biće proverena u najkraćem roku.',
        'accepted' => 'Porudžbina je potvrđena i priprema se za slanje.',
        'canceled' => 'Porudžbina je nažalost odbijena. Za dodatne informacije kontaktirajte nas.',
    ][$type] ?? '';

    $rows = '';
    foreach ($items as $item) {
        $rows .= '<tr>' .
            '<td style="padding:8px;border-bottom:1px solid #eee;">' . htmlspecialchars($item['product_name']) . '</td>' .
            '<td style="padding:8px;border-bottom:1px solid #eee;">' . htmlspecialchars($item['size'] ?? '-') . '</td>' .
            '<td style="padding:8px;border-bottom:1px solid #eee;">' . (int) $item['quantity'] . 'x</td>' .
            '<td style="padding:8px;border-bottom:1px solid #eee;text-align:right;">' . number_format((float) $item['subtotal'], 2) . ' RSD</td>' .
            '</tr>';
    }

    $total = number_format((float) $order['total_price'], 2);

    return "<div style='font-family:Arial,sans-serif;color:#111;'>
        <h2 style='color:#7E38BB;margin-bottom:5px;'>Victory Sport</h2>
        <p style='font-size:15px;'>Poštovani {$order['customer_name']},</p>
        <p style='font-size:15px;'>$intro</p>
        <table style='width:100%;border-collapse:collapse;margin-top:20px;'>
            <thead>
                <tr style='background:#f6f6f6;'>
                    <th style='text-align:left;padding:8px;'>Artikal</th>
                    <th style='text-align:left;padding:8px;'>Veličina</th>
                    <th style='text-align:left;padding:8px;'>Količina</th>
                    <th style='text-align:right;padding:8px;'>Iznos</th>
                </tr>
            </thead>
            <tbody>$rows</tbody>
        </table>
        <p style='text-align:right;font-size:16px;margin-top:10px;'><strong>Ukupno:</strong> $total RSD</p>
        <p style='font-size:13px;color:#666;margin-top:25px;'>Porudžbina #{$order['order_number']} | {$order['created_at']}</p>
        <p style='font-size:13px;color:#666;'>Hvala na poverenju, Victory Sport tim.</p>
    </div>";
}

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
               c.size,
               c.quantity,
               c.created_at,
               p.name,
               p.code,
               p.price,
               p.sale_price,
               p.on_sale,
               p.main_image,
               p.is_active,
               p.category_id,
               cat.name AS category_name
        FROM cart c
        LEFT JOIN products p ON p.id = c.product_id
        LEFT JOIN categories cat ON cat.id = p.category_id
        WHERE c.session_id = ?
        ORDER BY c.created_at ASC, c.id ASC
    ');
    $stmt->execute([$sessionId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function calculateCartTotals(array $items): array
{
    $total    = 0.0;
    $quantity = 0;

    foreach ($items as $item) {
        $unitPrice = isset($item['price']) ? productDisplayPrice($item) : 0;
        $qty       = (int) ($item['quantity'] ?? 0);
        $total    += $unitPrice * $qty;
        $quantity += $qty;
    }

    return [
        'total'    => round($total, 2),
        'quantity' => $quantity,
    ];
}

function addProductToCart(int $productId, string $size, int $quantity, ?string $sessionId = null): array
{
    $sessionId = $sessionId ?? cartSessionId();
    $size      = strtoupper($size);
    $quantity  = max(1, $quantity);

    $product = fetchProductById($productId);
    if (!$product) {
        throw new RuntimeException('Proizvod nije pronađen.');
    }

    $stockColumn = inventoryColumnForSize($size);
    if (!$stockColumn) {
        throw new RuntimeException('Nepodržana veličina.');
    }

    $stockMap = productStockMap($product);
    if (($stockMap[$size] ?? 0) <= 0) {
        throw new RuntimeException('Izabrana veličina nije dostupna.');
    }

    $conn = db();

    $check = $conn->prepare('SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ? AND size = ?');
    $check->execute([$sessionId, $productId, $size]);
    $existing      = $check->fetch(PDO::FETCH_ASSOC);
    $requestedQty  = $quantity;

    if ($existing) {
        $requestedQty = (int) $existing['quantity'] + $quantity;
    }

    if (($stockMap[$size] ?? 0) < $requestedQty) {
        throw new RuntimeException('Tražena količina nije dostupna u toj veličini.');
    }

    if ($existing) {
        $update = $conn->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
        $update->execute([$requestedQty, $existing['id']]);
    } else {
        $insert = $conn->prepare('INSERT INTO cart (session_id, product_id, size, quantity) VALUES (?, ?, ?, ?)');
        $insert->execute([$sessionId, $productId, $size, $quantity]);
    }

    return fetchCartItems($sessionId);
}

function inventoryColumnForSize(string $size): ?string
{
    return match (strtoupper($size)) {
        'XS'  => 'stock_xs',
        'S'   => 'stock_s',
        'M'   => 'stock_m',
        'L'   => 'stock_l',
        'XL'  => 'stock_xl',
        'XXL' => 'stock_xxl',
        default => null,
    };
}

function fetchOrderByNumber(string $orderNumber): ?array
{
    $stmt = db()->prepare('SELECT * FROM orders WHERE order_number = ? LIMIT 1');
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    return $order ?: null;
}

function fetchOrderById(int $orderId): ?array
{
    $stmt = db()->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    return $order ?: null;
}

function fetchOrderItems(int $orderId): array
{
    $stmt = db()->prepare('
        SELECT *
        FROM order_items
        WHERE order_id = ?
        ORDER BY id ASC
    ');
    $stmt->execute([$orderId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    return ['victoryshop@constro.rs'];
}

function buildAdminOrderEmail(array $order, array $items): string
{
    $rows = '';
    foreach ($items as $item) {
        $rows .= '<tr>' .
            '<td style="padding:6px;border-bottom:1px solid #eee;">' . htmlspecialchars($item['product_name']) . '</td>' .
            '<td style="padding:6px;border-bottom:1px solid #eee;">' . htmlspecialchars($item['size'] ?? '-') . '</td>' .
            '<td style="padding:6px;border-bottom:1px solid #eee;">' . (int) $item['quantity'] . '</td>' .
            '<td style="padding:6px;border-bottom:1px solid #eee;text-align:right;">' . number_format((float) $item['subtotal'], 2) . ' RSD</td>' .
            '</tr>';
    }

    $total = number_format((float) $order['total_price'], 2);

    return "<div style='font-family:Arial,sans-serif;color:#111;'>
        <h2 style='color:#7E38BB;margin-bottom:5px;'>Nova porudžbina #{$order['order_number']}</h2>
        <p><strong>Kupac:</strong> " . htmlspecialchars($order['customer_name']) . "</p>
        <p><strong>Email:</strong> " . htmlspecialchars($order['email']) . " | <strong>Telefon:</strong> " . htmlspecialchars($order['phone']) . "</p>
        <p><strong>Adresa:</strong> " . htmlspecialchars($order['address']) . ', ' . htmlspecialchars($order['city']) . "</p>
        <table style='width:100%;border-collapse:collapse;margin-top:20px;'>
            <thead>
                <tr style='background:#f6f6f6;'>
                    <th style='text-align:left;padding:8px;'>Artikal</th>
                    <th style='text-align:left;padding:8px;'>Veličina</th>
                    <th style='text-align:left;padding:8px;'>Količina</th>
                    <th style='text-align:right;padding:8px;'>Iznos</th>
                </tr>
            </thead>
            <tbody>$rows</tbody>
        </table>
        <p style='text-align:right;font-size:15px;margin-top:12px;'><strong>Ukupno:</strong> $total RSD</p>
    </div>";
}
