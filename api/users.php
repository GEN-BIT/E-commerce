<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Admin access required.']);
    exit;
}

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ?');
    $stmt->execute([(int) $_GET['id']]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        exit;
    }
    echo json_encode(['status' => 'ok', 'user' => $user]);
    exit;
}

$users = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
echo json_encode(['status' => 'ok', 'users' => $users]);
