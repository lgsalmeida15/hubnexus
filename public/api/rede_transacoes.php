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

// Parâmetros de paginação e filtros
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$data_rede = $_GET['data_rede'] ?? '';
$omie_empresa_id = $_GET['omie_empresa_id'] ?? '';
$processado_filter = $_GET['processado_omie'] ?? '';

// Filtros acumulativos (arrays)
$pv_filters = $_GET['empresa_id'] ?? [];
if (!is_array($pv_filters) && !empty($pv_filters)) $pv_filters = [$pv_filters];

$query = "SELECT t.id, t.data_rede, t.nsu, t.nsu_parcela, t.parcela_numero, t.parcela_total, t.valor, t.taxa, t.status, t.observacao, t.processado_omie, t.rede_empresa_id, t.created_at, e.nome as empresa_nome, eo.nome as omie_nome 
          FROM rede_transacoes t 
          LEFT JOIN empresas e ON t.rede_empresa_id = e.rede_empresa_id 
          LEFT JOIN empresas_omie eo ON e.omie_empresa_id = eo.id
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (t.nsu_parcela LIKE ? OR t.nsu LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($data_rede) {
    $query .= " AND t.data_rede = ?";
    $params[] = $data_rede;
}

if ($omie_empresa_id) {
    $query .= " AND e.omie_empresa_id = ?";
    $params[] = $omie_empresa_id;
}

if ($processado_filter === 'true') {
    $query .= " AND t.processado_omie = TRUE";
} elseif ($processado_filter === 'false') {
    // Considera FALSE ou NULL como não processado
    $query .= " AND (t.processado_omie = FALSE OR t.processado_omie IS NULL)";
}

if (!empty($pv_filters)) {
    $placeholders = implode(',', array_fill(0, count($pv_filters), '?'));
    $query .= " AND t.rede_empresa_id IN ($placeholders)";
    foreach ($pv_filters as $p) $params[] = (int)$p;
}

// Conta total para paginação
$count_query = "SELECT COUNT(*) as total 
                FROM rede_transacoes t 
                LEFT JOIN empresas e ON t.rede_empresa_id = e.rede_empresa_id 
                WHERE 1=1";

// Reutiliza a lógica de filtros para o count
$count_params = [];
if ($search) {
    $count_query .= " AND (t.nsu_parcela LIKE ? OR t.nsu LIKE ?)";
    $count_params[] = "%$search%";
    $count_params[] = "%$search%";
}
if ($data_rede) {
    $count_query .= " AND t.data_rede = ?";
    $count_params[] = $data_rede;
}
if ($omie_empresa_id) {
    $count_query .= " AND e.omie_empresa_id = ?";
    $count_params[] = $omie_empresa_id;
}
if ($processado_filter === 'true') {
    $count_query .= " AND t.processado_omie = TRUE";
} elseif ($processado_filter === 'false') {
    $count_query .= " AND (t.processado_omie = FALSE OR t.processado_omie IS NULL)";
}
if (!empty($pv_filters)) {
    $placeholders = implode(',', array_fill(0, count($pv_filters), '?'));
    $count_query .= " AND t.rede_empresa_id IN ($placeholders)";
    foreach ($pv_filters as $p) $count_params[] = (int)$p;
}

$stmt_count = $db->prepare($count_query);
$stmt_count->execute($count_params);
$total_records = $stmt_count->fetch()['total'];
$total_pages = ceil($total_records / $limit);

// Busca dados paginados
$query .= " ORDER BY t.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$transacoes = $stmt->fetchAll();

// Formata dados para o frontend
foreach ($transacoes as &$t) {
    $t['valor_fmt'] = 'R$ ' . number_format($t['valor'], 2, ',', '.');
    $t['taxa_fmt'] = 'R$ ' . number_format($t['taxa'], 2, ',', '.');
    $t['data_rede_fmt'] = date('d/m/Y', strtotime($t['data_rede']));
    $t['created_at_fmt'] = date('d/m/Y H:i', strtotime($t['created_at']));
}

echo json_encode([
    'data' => $transacoes,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_records' => $total_records,
        'limit' => $limit
    ]
]);
