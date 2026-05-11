<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Auth;

Auth::requireRole(['admin', 'edit']);

$db = Config::getDatabaseConnection();
$error = '';
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: empresas.php');
    exit;
}

// Carrega dados atuais
$stmt = $db->prepare("SELECT * FROM empresas WHERE rede_empresa_id = ?");
$stmt->execute([$id]);
$empresa = $stmt->fetch();

if (!$empresa) {
    header('Location: empresas.php?message=Empresa não encontrada');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erro de validação CSRF.';
    } else {
        $data = [
        'rede_empresa_id_old' => $id,
        'rede_empresa_id_new' => $_POST['rede_empresa_id'],
        'nome' => $_POST['nome'],
        'rede_parent' => $_POST['rede_parent'],
        'rede_api_key' => $_POST['rede_api_key'],
        'omie_app_key' => $_POST['omie_app_key'],
        'omie_empresa' => $_POST['omie_empresa'],
        'omie_empresa_id' => !empty($_POST['omie_empresa_id']) ? $_POST['omie_empresa_id'] : null,
        'omie_cliente_fornecedor' => $_POST['omie_cliente_fornecedor'],
        'omie_categoria_taxa' => $_POST['omie_categoria_taxa'],
        'omie_id_conta_corrente' => !empty($_POST['omie_id_conta_corrente']) ? $_POST['omie_id_conta_corrente'] : null,
        'ativo' => isset($_POST['ativo']) ? 'true' : 'false',
        'auto_redecard' => isset($_POST['auto_redecard']) ? 'true' : 'false',
        'rede_subsidiaries' => $_POST['rede_subsidiaries']
    ];

    try {
        $sql = "UPDATE empresas SET 
            rede_empresa_id = :rede_empresa_id_new,
            nome = :nome, 
            rede_parent = :rede_parent, 
            rede_api_key = :rede_api_key, 
            omie_app_key = :omie_app_key, 
            omie_empresa = :omie_empresa, 
            omie_empresa_id = :omie_empresa_id, 
            omie_cliente_fornecedor = :omie_cliente_fornecedor, 
            omie_categoria_taxa = :omie_categoria_taxa, 
            omie_id_conta_corrente = :omie_id_conta_corrente,
            ativo = :ativo, 
            auto_redecard = :auto_redecard,
            rede_subsidiaries = :rede_subsidiaries,
            updated_at = CURRENT_TIMESTAMP";
        
        // Só atualiza as senhas se forem preenchidas
        if (!empty($_POST['rede_api_secret'])) {
            $sql .= ", rede_api_secret = :rede_api_secret";
            $data['rede_api_secret'] = $_POST['rede_api_secret'];
        }
        
        if (!empty($_POST['omie_app_secret'])) {
            $sql .= ", omie_app_secret = :omie_app_secret";
            $data['omie_app_secret'] = $_POST['omie_app_secret'];
        }

        $sql .= " WHERE rede_empresa_id = :rede_empresa_id_old";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
        
        header('Location: empresas.php?message=Ponto de Venda atualizado com sucesso!');
        exit;
    } catch (\PDOException $e) {
        $error = "Erro ao atualizar: " . $e->getMessage();
    }
}
}

$pageTitle = 'Editar Ponto de Venda - HubNexus';
$currentPage = 'hub';

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">Editar Ponto de Venda: <?php echo htmlspecialchars($empresa['nome']); ?></h4>
                        <a href="empresas.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Voltar</a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 small"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <?php echo Auth::csrfInput(); ?>
                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Informações Básicas e Rede</h6>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">PV (Rede Empresa ID)</label>
                                <input type="number" name="rede_empresa_id" class="form-control" value="<?php echo htmlspecialchars($empresa['rede_empresa_id']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nome do PV</label>
                                <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($empresa['nome']); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Rede Parent</label>
                                <input type="text" name="rede_parent" class="form-control" value="<?php echo htmlspecialchars($empresa['rede_parent'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Rede API Key</label>
                                <input type="text" name="rede_api_key" class="form-control" value="<?php echo htmlspecialchars($empresa['rede_api_key'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Rede API Secret</label>
                                <input type="password" name="rede_api_secret" class="form-control" placeholder="Preencha apenas para alterar">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Rede Subsidiaries</label>
                                <input type="text" name="rede_subsidiaries" class="form-control" value="<?php echo htmlspecialchars($empresa['rede_subsidiaries'] ?? ''); ?>">
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Configurações Omie</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Omie App Key</label>
                                <input type="text" name="omie_app_key" class="form-control" value="<?php echo htmlspecialchars($empresa['omie_app_key'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Omie App Secret</label>
                                <input type="password" name="omie_app_secret" class="form-control" placeholder="Preencha apenas para alterar">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Omie Empresa (Nome)</label>
                                <input type="text" name="omie_empresa" class="form-control" value="<?php echo htmlspecialchars($empresa['omie_empresa'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Omie Empresa ID</label>
                                <input type="number" name="omie_empresa_id" class="form-control" value="<?php echo htmlspecialchars($empresa['omie_empresa_id'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Omie Cliente/Fornecedor ID</label>
                                <input type="text" name="omie_cliente_fornecedor" class="form-control" value="<?php echo htmlspecialchars($empresa['omie_cliente_fornecedor'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Omie Categoria Taxa</label>
                                <input type="text" name="omie_categoria_taxa" class="form-control" value="<?php echo htmlspecialchars($empresa['omie_categoria_taxa'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Omie ID Conta Corrente</label>
                                <input type="number" name="omie_id_conta_corrente" class="form-control" value="<?php echo htmlspecialchars($empresa['omie_id_conta_corrente'] ?? ''); ?>">
                            </div>

                            <div class="col-12 mt-4">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" name="ativo" id="ativo" <?php echo ($empresa['ativo'] == 'true' || $empresa['ativo'] === true) ? 'checked' : ''; ?>>
                                            <label class="form-check-label small fw-bold" for="ativo">PV Ativo (Geral)</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="auto_redecard" id="auto_redecard" <?php echo ($empresa['auto_redecard'] == 'true' || $empresa['auto_redecard'] === true || $empresa['auto_redecard'] == 't' || $empresa['auto_redecard'] == 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label small fw-bold" for="auto_redecard">Automação Redecard Ativa</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold">Atualizar Ponto de Venda</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

