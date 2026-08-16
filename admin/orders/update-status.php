<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    redirect('admin/orders/index.php?msg=' . urlencode('Invalid request.'));
}

$orderId = (int) ($_POST['order_id'] ?? 0);
$status  = $_POST['status'] ?? '';

try {
    update_order_status($orderId, $status);
    log_action(current_user_id(), 'order_status_changed', "order_id={$orderId} status={$status}");
    redirect('admin/orders/view.php?id=' . $orderId . '&msg=' . urlencode('Status updated.'));
} catch (InvalidArgumentException $e) {
    redirect('admin/orders/view.php?id=' . $orderId . '&msg=' . urlencode($e->getMessage()));
}
