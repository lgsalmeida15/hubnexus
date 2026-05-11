<?php
require_once __DIR__ . '/../../app/bootstrap.php';

use App\Config;
use App\Auth;

header('Content-Type: application/json');

if (!Auth::isLoggedIn() || !Auth::hasRole('admin')) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$db = Config::getDatabaseConnection();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = ($page - 1) * $limit;

$query = "SELECT id, nome, email, perfil, permissoes FROM usuarios WHERE 1=1";
$params = [];

// Conta total
$count_query = "SELECT COUNT(*) as total FROM usuarios";
$total_records = $db->query($count_query)->fetch()['total'];
$total_pages = ceil($total_records / $limit);

$query .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

foreach ($usuarios as &$u) {
    $u['permissoes_fmt'] = json_decode($u['permissoes'] ?? '[]', true);
}

echo json_encode([
    'data' => $usuarios,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_records' => $total_records
    ]
]);
