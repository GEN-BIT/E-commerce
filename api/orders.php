<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Login required.']);
    exit;
}

$userId = current_user_id();

if (isset($_GET['id'])) {
    $order = get_order((int) $_GET['id'], $userId);
    if (!$order) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
        exit;
    }
    echo json_encode(['status' => 'ok', 'order' => $order]);
    exit;
}

echo json_encode(['status' => 'ok', 'orders' => get_orders_for_user($userId)]);
