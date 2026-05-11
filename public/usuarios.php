<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Auth;

Auth::requireRole('admin');

$db = Config::getDatabaseConnection();

// Processar exclusão via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!Auth::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['error' => 'Erro CSRF']);
        exit;
    }

    $id = $_POST['id'] ?? null;
    if ($id && $id != $_SESSION['user_id']) {
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }
}

$message = $_GET['message'] ?? '';

$pageTitle = 'Usuários - HubNexus';
$currentPage = 'usuarios';

include 'includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Gerenciar Usuários</h1>
        <a href="usuarios_create.php" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-person-plus me-2"></i> Novo Usuário
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm position-relative border-0">
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
                            <th>E-mail</th>
                            <th>Perfil</th>
                            <th>Permissões (Integrações)</th>
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
$extraScripts = <<<'JS'
<script>
    const currentUserId = document.body.getAttribute('data-user-id');

    async function fetchUsuarios(page = 1) {
        const loading = document.getElementById("loading");
        const tableBody = document.getElementById("table-body");
        const pagination = document.getElementById("pagination");
        
        loading.style.display = "flex";
        
        try {
            const response = await fetch(`api/usuarios.php?page=${page}`);
            const result = await response.json();
            
            tableBody.innerHTML = "";
            
            result.data.forEach(u => {
                const perms = u.permissoes_fmt;
                const permsLabel = perms.length === 0 
                    ? '<span class="text-muted small">Nenhuma</span>' 
                    : perms.map(p => {
                        const label = p.charAt(0).toUpperCase() + p.slice(1);
                        return `<span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1">${HubNexus.escapeHTML(label)}</span>`;
                    }).join("");

                const actionButtons = u.id != currentUserId 
                    ? `<button onclick="deleteUsuario(${u.id})" class="btn btn-outline-danger btn-sm rounded-circle" title="Excluir">
                            <i class="bi bi-trash"></i>
                       </button>`
                    : `<button class="btn btn-outline-secondary btn-sm rounded-circle" disabled title="Você não pode se excluir">
                            <i class="bi bi-person-check"></i>
                       </button>`;

                tableBody.innerHTML += `
                    <tr>
                        <td class="ps-4 text-muted small">${u.id}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    ${HubNexus.escapeHTML(u.nome.charAt(0).toUpperCase())}
                                </div>
                                <strong>${HubNexus.escapeHTML(u.nome)}</strong>
                            </div>
                        </td>
                        <td>${HubNexus.escapeHTML(u.email)}</td>
                        <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">${HubNexus.escapeHTML(u.perfil.charAt(0).toUpperCase() + u.perfil.slice(1))}</span></td>
                        <td>${permsLabel}</td>
                        <td class="text-center pe-4">
                            <div class="btn-group">
                                <a href="usuarios_edit.php?id=${u.id}" class="btn btn-outline-primary btn-sm rounded-circle me-2" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                ${actionButtons}
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            HubNexus.renderPagination("pagination", result.pagination, fetchUsuarios);
        } catch (error) {
            console.error("Erro:", error);
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Erro ao carregar dados.</td></tr>';
        } finally {
            loading.style.display = "none";
        }
    }

    async function deleteUsuario(id) {
        if (!confirm("Tem certeza que deseja excluir este usuário?")) return;
        
        const formData = new FormData();
        formData.append("action", "delete");
        formData.append("id", id);
        formData.append("csrf_token", HubNexus.csrfToken);
        
        try {
            const response = await fetch("usuarios.php", {
                method: "POST",
                body: formData
            });
            location.reload();
        } catch (error) {
            alert("Erro ao excluir usuário.");
        }
    }

    fetchUsuarios(1);
</script>
JS;
include 'includes/footer.php'; 
?>
