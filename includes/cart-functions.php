<?php
/**
 * Cart helper functions
 * Stage: 5 - Cart + Orders
 *
 * Cart is server-side, tied to the logged-in user (see cart/cart_items tables).
 * All functions assume $pdo is available and the user is logged in.
 */

function get_or_create_cart_id(int $userId): int
{
    global $pdo;

    $stmt = $pdo->prepare('SELECT id FROM cart WHERE user_id = ?');
    $stmt->execute([$userId]);
    $cart = $stmt->fetch();

    if ($cart) {
        return (int) $cart['id'];
    }

    $stmt = $pdo->prepare('INSERT INTO cart (user_id) VALUES (?)');
    $stmt->execute([$userId]);
    return (int) $pdo->lastInsertId();
}

function add_to_cart(int $userId, int $productId, int $quantity): void
{
    global $pdo;

    if ($quantity < 1) {
        throw new InvalidArgumentException('Quantity must be at least 1.');
    }

    $stmt = $pdo->prepare('SELECT stock, is_active FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product || !$product['is_active']) {
        throw new InvalidArgumentException('Product not available.');
    }

    $cartId = get_or_create_cart_id($userId);

    $stmt = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?');
    $stmt->execute([$cartId, $productId]);
    $existing = $stmt->fetch();

    $newQuantity = $quantity + ($existing ? (int) $existing['quantity'] : 0);

    if ($newQuantity > (int) $product['stock']) {
        throw new InvalidArgumentException('Only ' . $product['stock'] . ' left in stock.');
    }

    if ($existing) {
        $stmt = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?');
        $stmt->execute([$newQuantity, $existing['id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)');
        $stmt->execute([$cartId, $productId, $quantity]);
    }
}

function update_cart_item(int $userId, int $cartItemId, int $quantity): void
{
    global $pdo;

    if ($quantity < 1) {
        remove_from_cart($userId, $cartItemId);
        return;
    }

    $stmt = $pdo->prepare(
        'SELECT ci.id, p.stock
         FROM cart_items ci
         JOIN cart c ON c.id = ci.cart_id
         JOIN products p ON p.id = ci.product_id
         WHERE ci.id = ? AND c.user_id = ?'
    );
    $stmt->execute([$cartItemId, $userId]);
    $item = $stmt->fetch();

    if (!$item) {
        throw new InvalidArgumentException('Cart item not found.');
    }

    if ($quantity > (int) $item['stock']) {
        throw new InvalidArgumentException('Only ' . $item['stock'] . ' left in stock.');
    }

    $stmt = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?');
    $stmt->execute([$quantity, $cartItemId]);
}

function remove_from_cart(int $userId, int $cartItemId): void
{
    global $pdo;

    $stmt = $pdo->prepare(
        'DELETE ci FROM cart_items ci
         JOIN cart c ON c.id = ci.cart_id
         WHERE ci.id = ? AND c.user_id = ?'
    );
    $stmt->execute([$cartItemId, $userId]);
}

function clear_cart(int $userId): void
{
    global $pdo;

    $stmt = $pdo->prepare(
        'DELETE ci FROM cart_items ci
         JOIN cart c ON c.id = ci.cart_id
         WHERE c.user_id = ?'
    );
    $stmt->execute([$userId]);
}

function get_cart_items(int $userId): array
{
    global $pdo;

    $stmt = $pdo->prepare(
        'SELECT ci.id AS cart_item_id, ci.quantity, p.id AS product_id, p.name, p.price, p.image, p.stock
         FROM cart_items ci
         JOIN cart c ON c.id = ci.cart_id
         JOIN products p ON p.id = ci.product_id
         WHERE c.user_id = ?
         ORDER BY ci.id'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function get_cart_total(int $userId): float
{
    $total = 0.0;
    foreach (get_cart_items($userId) as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function get_cart_count(int $userId): int
{
    $count = 0;
    foreach (get_cart_items($userId) as $item) {
        $count += (int) $item['quantity'];
    }
    return $count;
}
