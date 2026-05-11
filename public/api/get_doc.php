<?php
require_once __DIR__ . '/../../app/bootstrap.php';

use App\Auth;

header('Content-Type: application/json');

if (!Auth::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$file = $_GET['file'] ?? '';
$allowed_files = ['GUIDE.md', 'projeto.md'];

if (!in_array($file, $allowed_files)) {
    http_response_code(403);
    echo json_encode(['error' => 'Arquivo não permitido']);
    exit;
}

$path = __DIR__ . '/../../' . $file;

if (file_exists($path)) {
    echo json_encode(['content' => file_get_contents($path)]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Arquivo não encontrado']);
}
