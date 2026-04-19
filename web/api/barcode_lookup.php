<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$code = trim((string)($_GET['code'] ?? ''));
if ($code === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Code-barres requis']);
    exit();
}

try {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id_produit, nom, code_barre, prix, stock FROM produits WHERE code_barre = :code AND actif = 1 LIMIT 1');
    $stmt->execute([':code' => $code]);
    $p = $stmt->fetch();

    if (!$p) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Produit introuvable']);
        exit();
    }

    echo json_encode([
        'ok' => true,
        'produit' => [
            'id_produit' => (int)$p['id_produit'],
            'nom' => (string)$p['nom'],
            'code_barre' => (string)$p['code_barre'],
            'prix' => (float)$p['prix'],
            'stock' => (int)$p['stock'],
        ],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('barcode_lookup error: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Erreur serveur']);
}
