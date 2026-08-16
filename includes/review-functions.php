<?php
/**
 * Product reviews and ratings.
 * Stage: 7 - Real-world features
 */

function get_product_rating_summary(int $productId): array
{
    global $pdo;

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS review_count, COALESCE(AVG(rating), 0) AS avg_rating
         FROM reviews WHERE product_id = ?'
    );
    $stmt->execute([$productId]);
    $row = $stmt->fetch();

    return [
        'count' => (int) $row['review_count'],
        'average' => round((float) $row['avg_rating'], 1),
    ];
}

function get_product_reviews(int $productId): array
{
    global $pdo;

    $stmt = $pdo->prepare(
        'SELECT r.rating, r.comment, r.created_at, u.name AS reviewer_name
         FROM reviews r JOIN users u ON u.id = r.user_id
         WHERE r.product_id = ?
         ORDER BY r.created_at DESC'
    );
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function user_has_reviewed(int $userId, int $productId): bool
{
    global $pdo;

    $stmt = $pdo->prepare('SELECT id FROM reviews WHERE user_id = ? AND product_id = ?');
    $stmt->execute([$userId, $productId]);
    return (bool) $stmt->fetch();
}

/**
 * Throws InvalidArgumentException on bad rating or duplicate review.
 */
function add_review(int $userId, int $productId, int $rating, ?string $comment): void
{
    global $pdo;

    if ($rating < 1 || $rating > 5) {
        throw new InvalidArgumentException('Rating must be between 1 and 5.');
    }
    if (user_has_reviewed($userId, $productId)) {
        throw new InvalidArgumentException('You already reviewed this product.');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$productId, $userId, $rating, $comment ?: null]);
}

/** Renders a static star row (no interactivity) for a given average rating. */
function render_stars(float $rating): string
{
    $full = (int) floor($rating);
    $half = ($rating - $full) >= 0.5;
    $empty = 5 - $full - ($half ? 1 : 0);

    return str_repeat('★', $full) . ($half ? '½' : '') . str_repeat('☆', $empty);
}
