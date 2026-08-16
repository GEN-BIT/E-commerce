<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/mailer.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    redirect('checkout/index.php?msg=' . urlencode('Invalid request.'));
}

$address = trim($_POST['address'] ?? '');
$phone   = trim($_POST['phone'] ?? '');

if ($address === '') {
    redirect('checkout/index.php?msg=' . urlencode('Delivery address is required.'));
}

try {
    $orderId = create_order(current_user_id(), $address, $phone ?: null);
    log_action(current_user_id(), 'order_placed', "order_id={$orderId}");

    $order = get_order($orderId, current_user_id());
    $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
    $stmt->execute([current_user_id()]);
    $userEmail = $stmt->fetch()['email'];

    $rows = '';
    foreach ($order['items'] as $item) {
        $rows .= '<tr><td>' . htmlspecialchars($item['name']) . '</td><td>' . (int) $item['quantity']
            . '</td><td>' . format_price((float) $item['price']) . '</td></tr>';
    }
    $emailBody = "<p>Thanks for your order #{$orderId}!</p>"
        . '<table border="1" cellpadding="6" cellspacing="0"><tr><th>Product</th><th>Qty</th><th>Price</th></tr>'
        . $rows . '</table>'
        . '<p><strong>Total: ' . format_price((float) $order['total_amount']) . '</strong></p>'
        . '<p>Shipping to: ' . htmlspecialchars($address) . '</p>';

    send_email($userEmail, "Order Confirmation #{$orderId}", $emailBody);

    redirect('checkout/success.php?order_id=' . $orderId);
} catch (RuntimeException $e) {
    redirect('checkout/index.php?msg=' . urlencode($e->getMessage()));
}
