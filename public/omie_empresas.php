<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Auth;

Auth::requireLogin();

$db = App\Config::getDatabaseConnection();

// Processar exclusão via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    Auth::requireRole(['admin', 'edit']);
    
    if (!Auth::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['error' => 'Erro CSRF']);
        exit;
    }

    $id = $_POST['id'] ?? null;
    if ($id) {
        $stmt = $db->prepare("DELETE FROM empresas_omie WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }
}

$message = $_GET['message'] ?? '';

$pageTitle = 'Empresas Omie - HubNexus';
$currentPage = 'hub';

include 'includes/header.php';
?>

<div class="container-fluid px-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Hub</a></li>
            <li class="breadcrumb-item"><a href="redecard.php">Redecard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Empresas Omie</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold h2">Gerenciar Empresas OMIE</h1>
        <?php if (Auth::hasRole(['admin', 'edit'])): ?>
        <a href="omie_empresas_create.php" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-lg me-2"></i> Nova Empresa Omie
        </a>
        <?php endif; ?>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 position-relative">
        <div class="loading-overlay" id="loading" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(var(--bs-body-bg-rgb), 0.7); display: none; justify-content: center; align-items: center; z-index: 10;">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Nome</th>
                            <th>App Key</th>
                            <th>Status</th>
                            <th>Última Sincronização</th>
                            <th>Coleta CR</th>
                            <th>Criado em</th>
                            <th class="text-center pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <!-- Preenchido via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent border-top d-flex justify-content-center py-3">
            <nav>
                <ul class="pagination mb-0" id="pagination">
                    <!-- Preenchido via AJAX -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php 
$canEdit = Auth::hasRole(['admin', 'edit']);
$extraScripts = <<<'JS'
<script>
    const canEdit = document.body.getAttribute('data-can-edit') === '1';
    async function fetchOmieEmpresas(page = 1) {
        const loading = document.getElementById("loading");
        const tableBody = document.getElementById("table-body");
        const pagination = document.getElementById("pagination");
        
        loading.style.display = "flex";
        
        try {
            const response = await fetch(`api/omie_empresas.php?page=${page}`);
            const result = await response.json();
            
            tableBody.innerHTML = "";
            
            result.data.forEach(e => {
                const statusBadge = (e.ativo == "true" || e.ativo === true) 
                    ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">Ativo</span>' 
                    : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2">Inativo</span>';
                
                const coletaBadge = (e.coleta_em_andamento == "true" || e.coleta_em_andamento === true)
                    ? '<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2"><i class="bi bi-hourglass-split me-1"></i> Em andamento</span>'
                    : '<span class="badge bg-light text-dark border rounded-pill px-2">Ocioso</span>';

                const deleteButton = canEdit 
                    ? `<button onclick="deleteOmieEmpresa(${e.id})" class="btn btn-sm btn-outline-danger rounded-circle" title="Excluir">
                            <i class="bi bi-trash"></i>
                       </button>`
                    : '';

                tableBody.innerHTML += `
                    <tr>
                        <td class="ps-4 text-muted small">${e.id}</td>
                        <td><span class="fw-bold">${HubNexus.escapeHTML(e.nome)}</span></td>
                        <td><code class="small text-primary">${HubNexus.escapeHTML(e.app_key)}</code></td>
                        <td>${statusBadge}</td>
                        <td class="small"><i class="bi bi-arrow-repeat me-1"></i> ${e.last_sync_cr_fmt}</td>
                        <td>${coletaBadge}</td>
                        <td class="small text-muted">${e.created_at_fmt}</td>
                        <td class="text-center pe-4">
                            <div class="btn-group">
                                <a href="omie_empresas_edit.php?id=${e.id}" class="btn btn-sm btn-outline-primary rounded-circle me-2" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                ${deleteButton}
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            HubNexus.renderPagination("pagination", result.pagination, fetchOmieEmpresas);
        } catch (error) {
            console.error("Erro:", error);
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-danger">Erro ao carregar dados.</td></tr>';
        } finally {
            loading.style.display = "none";
        }
    }

    async function deleteOmieEmpresa(id) {
        if (!confirm("Tem certeza que deseja excluir esta empresa Omie?")) return;
        
        const formData = new FormData();
        formData.append("action", "delete");
        formData.append("id", id);
        formData.append("csrf_token", HubNexus.csrfToken);
        
        try {
            const response = await fetch("omie_empresas.php", {
                method: "POST",
                body: formData
            });
            location.reload();
        } catch (error) {
            alert("Erro ao excluir empresa.");
        }
    }

    fetchOmieEmpresas(1);
</script>
JS;
include 'includes/footer.php'; 
?>
