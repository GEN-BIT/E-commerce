<?php
/**
 * Wishlist ("save for later") functions.
 * Stage: 7 - Real-world features
 */

function is_in_wishlist(int $userId, int $productId): bool
{
    global $pdo;

    $stmt = $pdo->prepare('SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?');
    $stmt->execute([$userId, $productId]);
    return (bool) $stmt->fetch();
}

function add_to_wishlist(int $userId, int $productId): void
{
    global $pdo;

    $stmt = $pdo->prepare(
        'INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)'
    );
    $stmt->execute([$userId, $productId]);
}

function remove_from_wishlist(int $userId, int $productId): void
{
    global $pdo;

    $stmt = $pdo->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?');
    $stmt->execute([$userId, $productId]);
}

function get_wishlist(int $userId): array
{
    global $pdo;

    $stmt = $pdo->prepare(
        'SELECT p.* FROM wishlist w
         JOIN products p ON p.id = w.product_id
         WHERE w.user_id = ?
         ORDER BY w.created_at DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}
