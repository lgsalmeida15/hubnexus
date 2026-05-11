<?php
require_once __DIR__ . '/../../app/bootstrap.php';

use App\Config;
use App\Auth;

header('Content-Type: application/json');

if (!Auth::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$db = Config::getDatabaseConnection();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = ($page - 1) * $limit;

$query = "SELECT rede_empresa_id, nome, rede_parent, omie_empresa, omie_app_key, ativo, auto_redecard, created_at, updated_at FROM empresas WHERE 1=1";
$params = [];

// Conta total
$count_query = "SELECT COUNT(*) as total FROM empresas";
$total_records = $db->query($count_query)->fetch()['total'];
$total_pages = ceil($total_records / $limit);

$query .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$empresas = $stmt->fetchAll();

foreach ($empresas as &$e) {
    $e['created_at_fmt'] = date('d/m/Y H:i', strtotime($e['created_at']));
    $e['updated_at_fmt'] = $e['updated_at'] ? date('d/m/Y H:i', strtotime($e['updated_at'])) : '-';
}

echo json_encode([
    'data' => $empresas,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_records' => $total_records
    ]
]);
