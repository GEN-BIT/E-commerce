<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Login required.']);
    exit;
}

$userId = current_user_id();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        echo json_encode([
            'status' => 'ok',
            'items' => get_cart_items($userId),
            'total' => get_cart_total($userId),
            'count' => get_cart_count($userId),
        ]);
        exit;
    }

    // POST body is expected as JSON: {"action": "add|update|remove", ...}
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    if (!verify_csrf_token($body['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token.']);
        exit;
    }

    $action = $body['action'] ?? '';

    switch ($action) {
        case 'add':
            add_to_cart($userId, (int) ($body['product_id'] ?? 0), (int) ($body['quantity'] ?? 1));
            break;
        case 'update':
            update_cart_item($userId, (int) ($body['cart_item_id'] ?? 0), (int) ($body['quantity'] ?? 1));
            break;
        case 'remove':
            remove_from_cart($userId, (int) ($body['cart_item_id'] ?? 0));
            break;
        case 'clear':
            clear_cart($userId);
            break;
        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Unknown action.']);
            exit;
    }

    echo json_encode([
        'status' => 'ok',
        'items' => get_cart_items($userId),
        'total' => get_cart_total($userId),
        'count' => get_cart_count($userId),
    ]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
