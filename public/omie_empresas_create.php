<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Auth;

Auth::requireRole(['admin', 'edit']);

$db = Config::getDatabaseConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erro de validação CSRF.';
    } else {
        $data = [
            'nome' => $_POST['nome'],
            'app_key' => $_POST['app_key'],
            'app_secret' => $_POST['app_secret'],
            'ativo' => isset($_POST['ativo']) ? 'true' : 'false'
        ];

        try {
            $sql = "INSERT INTO empresas_omie (nome, app_key, app_secret, ativo, created_at) 
                    VALUES (:nome, :app_key, :app_secret, :ativo, CURRENT_TIMESTAMP)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($data);
            
            header('Location: omie_empresas.php?message=Empresa Omie criada com sucesso!');
            exit;
        } catch (\PDOException $e) {
            $error = "Erro ao salvar: " . $e->getMessage();
        }
    }
}

$pageTitle = 'Nova Empresa Omie - HubNexus';
$currentPage = 'hub';

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">Cadastrar Nova Empresa OMIE</h4>
                        <a href="omie_empresas.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Voltar</a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 small"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <?php echo Auth::csrfInput(); ?>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Nome da Empresa (Alias Omie)</label>
                                <input type="text" name="nome" class="form-control" placeholder="Ex: ULO01" required autofocus>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">App Key</label>
                                <input type="text" name="app_key" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">App Secret</label>
                                <input type="password" name="app_secret" class="form-control" required>
                            </div>
                            <div class="col-md-12 mt-4">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="ativo" id="ativo" checked>
                                            <label class="form-check-label small fw-bold" for="ativo">Empresa Ativa</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold">Salvar Empresa Omie</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

