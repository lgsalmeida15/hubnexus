<?php
require_once __DIR__ . '/../../app/bootstrap.php';

use App\Config;
use App\Auth;

header('Content-Type: application/json');

if (!Auth::isLoggedIn() || !Auth::hasRole(['admin', 'edit'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

// Validação CSRF
$csrf_token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!Auth::validateCsrfToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Erro de validação CSRF']);
    exit;
}

$db = Config::getDatabaseConnection();

$ids = $_POST['ids'] ?? [];
$observacao = $_POST['observacao'] ?? null;
$processado_omie = $_POST['processado_omie'] ?? null;

if (empty($ids)) {
    echo json_encode(['error' => 'Nenhuma transação selecionada']);
    exit;
}

try {
    $sql = "UPDATE rede_transacoes SET ";
    $updates = [];
    $params = [];

    if ($observacao !== null && $observacao !== '') {
        $updates[] = "observacao = ?";
        $params[] = $observacao;
    }

    if ($processado_omie !== null && $processado_omie !== '') {
        $updates[] = "processado_omie = ?";
        $params[] = ($processado_omie === 'true');
    }

    if (empty($updates)) {
        echo json_encode(['error' => 'Nenhum campo para atualizar']);
        exit;
    }

    $sql .= implode(', ', $updates);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql .= " WHERE id IN ($placeholders)";
    
    foreach ($ids as $id) {
        $params[] = (int)$id;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'updated' => count($ids)]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao atualizar: ' . $e->getMessage()]);
}
