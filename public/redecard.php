<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Auth;

Auth::requireLogin();

$pageTitle = 'Redecard - HubNexus';
$currentPage = 'hub';

include 'includes/header.php';
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Hub</a></li>
            <li class="breadcrumb-item active" aria-current="page">Redecard</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="fw-bold">Integração Redecard</h1>
            <p class="lead text-muted">Gerencie as configurações e ferramentas da Redecard.</p>
            <hr>
        </div>
    </div>

    <div class="row g-4">
        <!-- Card Gerenciar Empresas Rede -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-building fs-1 text-primary"></i>
                    </div>
                    <h4 class="card-title fw-bold">Ponto de Venda (Rede)</h4>
                    <p class="card-text text-muted">Cadastre e configure os pontos de venda para conciliação.</p>
                    <a href="empresas.php" class="btn btn-primary w-100 mt-3 rounded-pill">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Card Gerenciar Empresas Omie -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-box-seam fs-1 text-primary"></i>
                    </div>
                    <h4 class="card-title fw-bold">Empresas (Omie)</h4>
                    <p class="card-text text-muted">Gerencie as credenciais e status de coleta do ERP Omie.</p>
                    <a href="omie_empresas.php" class="btn btn-primary w-100 mt-3 rounded-pill">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Card Gerenciar Transações -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-list-check fs-1 text-primary"></i>
                    </div>
                    <h4 class="card-title fw-bold">Transações</h4>
                    <p class="card-text text-muted">Acompanhe o status de conciliação de cada parcela enviada.</p>
                    <a href="rede_transacoes.php" class="btn btn-primary w-100 mt-3 rounded-pill">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Card Documentação Técnica -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-file-earmark-medical fs-1 text-success"></i>
                    </div>
                    <h4 class="card-title fw-bold">Documentação</h4>
                    <p class="card-text text-muted">Arquitetura do motor de automação e workflows.</p>
                    <a href="docs.php" class="btn btn-success w-100 mt-3 rounded-pill">Ver Manual</a>
                </div>
            </div>
        </div>

        <!-- Placeholder para Relatórios -->
        <div class="col-md-4">
            <div class="card h-100 opacity-75">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-graph-up fs-1 text-secondary"></i>
                    </div>
                    <h4 class="card-title fw-bold text-secondary">Relatórios</h4>
                    <p class="card-text text-muted">Visualização de conciliações e divergências.</p>
                    <button class="btn btn-secondary w-100 mt-3 rounded-pill" disabled>Em breve</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

