<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$terminalId = (int)($_GET['terminal_id'] ?? 0);
if ($terminalId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'terminal_id requis']);
    exit();
}

try {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id_terminal, type, statut FROM terminaux WHERE id_terminal = :id LIMIT 1');
    $stmt->execute([':id' => $terminalId]);
    $t = $stmt->fetch();

    if (!$t) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Terminal introuvable']);
        exit();
    }

    echo json_encode([
        'ok' => true,
        'terminal_id' => (int)$t['id_terminal'],
        'mode' => (string)$t['type'],
        'statut' => (string)$t['statut'],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('terminal_mode_get error: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Erreur serveur']);
}
