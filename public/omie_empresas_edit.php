<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Auth;

Auth::requireRole(['admin', 'edit']);

$db = Config::getDatabaseConnection();
$error = '';
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: omie_empresas.php');
    exit;
}

// Carrega dados atuais
$stmt = $db->prepare("SELECT * FROM empresas_omie WHERE id = ?");
$stmt->execute([$id]);
$empresa = $stmt->fetch();

if (!$empresa) {
    header('Location: omie_empresas.php?message=Empresa Omie não encontrada');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erro de validação CSRF.';
    } else {
        $data = [
            'id' => $id,
            'nome' => $_POST['nome'],
            'app_key' => $_POST['app_key'],
            'ativo' => isset($_POST['ativo']) ? 'true' : 'false'
        ];

        try {
            $sql = "UPDATE empresas_omie SET 
                    nome = :nome, 
                    app_key = :app_key, 
                    ativo = :ativo";
            
            // Só atualiza a senha se for preenchida
            if (!empty($_POST['app_secret'])) {
                $sql .= ", app_secret = :app_secret";
                $data['app_secret'] = $_POST['app_secret'];
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($data);
            
            header('Location: omie_empresas.php?message=Empresa Omie atualizada com sucesso!');
            exit;
        } catch (\PDOException $e) {
            $error = "Erro ao atualizar: " . $e->getMessage();
        }
    }
}

$pageTitle = 'Editar Empresa Omie - HubNexus';
$currentPage = 'hub';

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">Editar Empresa OMIE: <?php echo htmlspecialchars($empresa['nome']); ?></h4>
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
                                <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($empresa['nome']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">App Key</label>
                                <input type="text" name="app_key" class="form-control" value="<?php echo htmlspecialchars($empresa['app_key']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">App Secret</label>
                                <input type="password" name="app_secret" class="form-control" placeholder="Preencha apenas para alterar">
                            </div>
                            <div class="col-md-12 mt-4">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="ativo" id="ativo" <?php echo ($empresa['ativo'] == 'true' || $empresa['ativo'] === true || $empresa['ativo'] == 't' || $empresa['ativo'] == 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label small fw-bold" for="ativo">Empresa Ativa</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold">Atualizar Empresa Omie</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

