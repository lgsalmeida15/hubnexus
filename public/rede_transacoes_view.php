<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Auth;

Auth::requireLogin();

$db = Config::getDatabaseConnection();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: rede_transacoes.php');
    exit;
}

// Busca transação detalhada
$stmt = $db->prepare("
    SELECT t.*, e.nome as empresa_nome 
    FROM rede_transacoes t 
    LEFT JOIN empresas e ON t.rede_empresa_id = e.rede_empresa_id 
    WHERE t.id = ?
");
$stmt->execute([$id]);
$t = $stmt->fetch();

if (!$t) {
    header('Location: rede_transacoes.php?message=Transação não encontrada');
    exit;
}

// Ação de Reprocessar (Resetar status)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'retry' && Auth::hasRole(['admin', 'edit'])) {
    $stmt = $db->prepare("UPDATE rede_transacoes SET status = 'PENDENTE', tentativa = 0, erro = NULL WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: rede_transacoes_view.php?id=$id&message=Transação enviada para reprocessamento!");
    exit;
}

$message = $_GET['message'] ?? '';

$pageTitle = 'Detalhes da Transação - HubNexus';
$currentPage = 'hub';

include 'includes/header.php';
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Hub</a></li>
            <li class="breadcrumb-item"><a href="redecard.php">Redecard</a></li>
            <li class="breadcrumb-item"><a href="rede_transacoes.php">Transações</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
        </ol>
    </nav>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Informações da Transação #<?php echo $t['id']; ?></h5>
                    <?php if (Auth::hasRole(['admin', 'edit']) && $t['status'] !== 'PROCESSADO'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="retry">
                        <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reprocessar
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted text-uppercase d-block mb-1">Status Atual</label>
                            <?php 
                                $badge_class = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                                if ($t['status'] === 'PROCESSADO') $badge_class = 'bg-success-subtle text-success border border-success-subtle';
                                if (strpos($t['status'], 'ERRO') !== false) $badge_class = 'bg-danger-subtle text-danger border border-danger-subtle';
                                if ($t['status'] === 'PENDENTE') $badge_class = 'bg-info-subtle text-info border border-info-subtle';
                            ?>
                            <span class="badge <?php echo $badge_class; ?> rounded-pill px-3 py-2"><?php echo htmlspecialchars($t['status']); ?></span>
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted text-uppercase d-block mb-1">NSU Parcela</label>
                            <div class="fw-bold text-primary"><code><?php echo htmlspecialchars($t['nsu_parcela']); ?></code></div>
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted text-uppercase d-block mb-1">Data Rede</label>
                            <div class="fw-bold"><?php echo date('d/m/Y', strtotime($t['data_rede'])); ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted text-uppercase d-block mb-1">Valor Bruto</label>
                            <div class="fw-bold h5 text-primary">R$ <?php echo number_format($t['valor'], 2, ',', '.'); ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted text-uppercase d-block mb-1">Taxa</label>
                            <div class="fw-bold h5 text-danger">R$ <?php echo number_format($t['taxa'], 2, ',', '.'); ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted text-uppercase d-block mb-1">Parcela</label>
                            <div class="fw-bold"><?php echo $t['parcela_numero']; ?> de <?php echo $t['parcela_total']; ?></div>
                        </div>
                    </div>

                    <?php if ($t['erro']): ?>
                    <div class="mt-4">
                        <label class="small fw-bold text-danger text-uppercase d-block mb-1">Último Erro</label>
                        <div class="alert alert-danger border-0 small py-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($t['erro']); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <label class="small fw-bold text-muted text-uppercase d-block mb-2">JSON Bruto (Redecard)</label>
                        <pre class="bg-dark text-light p-3 rounded-3 shadow-inner" style="max-height: 400px; overflow-y: auto;"><code class="small"><?php 
                            $json = json_decode($t['raw_json'], true);
                            echo htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); 
                        ?></code></pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Rastreabilidade</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="small fw-bold text-muted text-uppercase d-block mb-1">Ponto de Venda (PV)</label>
                        <div class="fw-bold"><?php echo htmlspecialchars($t['empresa_nome']); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted text-uppercase d-block mb-1">Worker Responsável</label>
                        <div class="small text-muted"><?php echo htmlspecialchars($t['worker_id'] ?? 'Nenhum'); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted text-uppercase d-block mb-1">Tentativas Realizadas</label>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar <?php echo $t['tentativa'] >= 4 ? 'bg-danger' : 'bg-primary'; ?>" role="progressbar" style="width: <?php echo ($t['tentativa'] / 5) * 100; ?>%"></div>
                        </div>
                        <small class="text-muted"><?php echo $t['tentativa']; ?> de 5 tentativas</small>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label class="small fw-bold text-muted text-uppercase d-block mb-1">ID Recebimento Omie</label>
                        <div class="fw-bold"><?php echo $t['omie_recebimento_id'] ?: '<span class="text-muted small">Pendente</span>'; ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted text-uppercase d-block mb-1">ID Taxa Omie</label>
                        <div class="fw-bold"><?php echo $t['omie_taxa_id'] ?: '<span class="text-muted small">Pendente</span>'; ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted text-uppercase d-block mb-1">Status Omie</label>
                        <div class="fw-bold"><?php echo $t['omie_status'] ?: '<span class="text-muted small">N/A</span>'; ?></div>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 text-muted small p-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Criado em:</span>
                        <span><?php echo date('d/m/Y H:i:s', strtotime($t['created_at'])); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Atualizado em:</span>
                        <span><?php echo date('d/m/Y H:i:s', strtotime($t['updated_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

