<?php
/**
 * Order helper functions
 * Stage: 5 - Cart + Orders
 */

/**
 * Creates an order from the user's current cart inside a DB transaction:
 * validates stock, inserts order + order_items, decrements stock, clears cart.
 * Returns the new order id. Throws RuntimeException on failure (e.g. insufficient stock).
 */
function create_order(int $userId, string $shippingAddress, ?string $shippingPhone): int
{
    global $pdo;

    $items = get_cart_items($userId);
    if (empty($items)) {
        throw new RuntimeException('Your cart is empty.');
    }

    $pdo->beginTransaction();

    try {
        // Re-check stock inside the transaction to avoid race conditions
        foreach ($items as $item) {
            $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ? FOR UPDATE');
            $stmt->execute([$item['product_id']]);
            $current = $stmt->fetch();

            if (!$current || (int) $current['stock'] < (int) $item['quantity']) {
                throw new RuntimeException(
                    'Not enough stock for "' . $item['name'] . '". Please update your cart.'
                );
            }
        }

        $total = array_reduce($items, fn($carry, $i) => $carry + $i['price'] * $i['quantity'], 0.0);

        $stmt = $pdo->prepare(
            'INSERT INTO orders (user_id, total_amount, status, shipping_address, shipping_phone)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $total, ORDER_PENDING, $shippingAddress, $shippingPhone]);
        $orderId = (int) $pdo->lastInsertId();

        $itemStmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)'
        );
        $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');

        foreach ($items as $item) {
            $itemStmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
            $stockStmt->execute([$item['quantity'], $item['product_id']]);
        }

        clear_cart($userId);

        $pdo->commit();
        return $orderId;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function get_orders_for_user(int $userId): array
{
    global $pdo;

    $stmt = $pdo->prepare(
        'SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Fetches an order, scoped to $userId unless $userId is null (admin access).
 */
function get_order(int $orderId, ?int $userId = null): ?array
{
    global $pdo;

    if ($userId !== null) {
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
        $stmt->execute([$orderId, $userId]);
    } else {
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$orderId]);
    }

    $order = $stmt->fetch();
    if (!$order) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT oi.quantity, oi.price, p.name, p.image
         FROM order_items oi JOIN products p ON p.id = oi.product_id
         WHERE oi.order_id = ?'
    );
    $stmt->execute([$orderId]);
    $order['items'] = $stmt->fetchAll();

    return $order;
}

function update_order_status(int $orderId, string $status): void
{
    global $pdo;

    $valid = [ORDER_PENDING, ORDER_CONFIRMED, ORDER_PROCESSING, ORDER_SHIPPED, ORDER_DELIVERED, ORDER_CANCELLED];
    if (!in_array($status, $valid, true)) {
        throw new InvalidArgumentException('Invalid order status.');
    }

    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $orderId]);
}
