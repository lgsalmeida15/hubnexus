<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Auth;

Auth::requireLogin();

// Busca as integrações do cache da sessão (alta performance)
$integracoes = Auth::getPermittedIntegrations();

$pageTitle = 'HubNexus - Integrações';
$currentPage = 'hub';

include 'includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="display-5 fw-bold">Hub de Integrações</h1>
            <p class="lead text-muted">Selecione uma integração para acessar as ferramentas.</p>
            <hr>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($integracoes as $int): ?>
        <div class="col-md-6 col-lg-4">
            <a href="<?php echo htmlspecialchars($int['slug']); ?>.php" class="card h-100 text-decoration-none color-inherit">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi <?php echo htmlspecialchars($int['icone']); ?> display-1 text-primary"></i>
                    </div>
                    <h3 class="card-title fw-bold text-body"><?php echo htmlspecialchars($int['nome']); ?></h3>
                    <p class="card-text text-muted"><?php echo htmlspecialchars($int['descricao']); ?></p>
                </div>
                <div class="card-footer bg-transparent border-0 text-center pb-4">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">Ativo</span>
                </div>
            </a>
        </div>
        <?php endforeach; ?>

        <?php if (empty($integracoes)): ?>
        <div class="col-md-12">
            <div class="alert alert-warning border-0 shadow-sm">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Você não possui permissão para acessar nenhuma integração. Entre em contato com o administrador.
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

