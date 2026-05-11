<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Auth;

Auth::requireRole('admin');

$db = Config::getDatabaseConnection();
$error = '';

// Busca integrações disponíveis
$integracoes = $db->query("SELECT * FROM integracoes WHERE ativo = TRUE ORDER BY nome ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erro de validação CSRF.';
    } else {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $perfil = $_POST['perfil'];
        $permissoes = json_encode($_POST['permissoes'] ?? []);

        try {
            $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha, perfil, permissoes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $senha, $perfil, $permissoes]);
            
            header('Location: usuarios.php?message=Usuário criado com sucesso!');
            exit;
        } catch (\PDOException $e) {
            $error = "Erro ao criar usuário: " . $e->getMessage();
        }
    }
}

$pageTitle = 'Novo Usuário - HubNexus';
$currentPage = 'usuarios';

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">Cadastrar Novo Usuário</h4>
                        <a href="usuarios.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Voltar</a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 small"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <?php echo Auth::csrfInput(); ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nome Completo</label>
                                <input type="text" name="nome" class="form-control" required autofocus placeholder="Ex: João Silva">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">E-mail</label>
                                <input type="email" name="email" class="form-control" required placeholder="joao@empresa.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Senha Inicial</label>
                                <input type="password" name="senha" class="form-control" required placeholder="••••••••">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Perfil (Nível de Acesso)</label>
                                <select name="perfil" class="form-select" required>
                                    <option value="view">Visualização (Apenas leitura)</option>
                                    <option value="edit">Edição (Pode alterar dados)</option>
                                    <option value="admin">Administrador (Controle total)</option>
                                </select>
                            </div>

                            <div class="col-md-12 mt-4">
                                <label class="form-label d-block fw-bold small mb-3">Integrações Permitidas</label>
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <?php foreach ($integracoes as $int): ?>
                                        <div class="form-check form-check-inline me-4">
                                            <input class="form-check-input" type="checkbox" name="permissoes[]" value="<?php echo $int['slug']; ?>" id="perm_<?php echo $int['slug']; ?>">
                                            <label class="form-check-label small" for="perm_<?php echo $int['slug']; ?>">
                                                <?php echo htmlspecialchars($int['nome']); ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php if (empty($integracoes)): ?>
                                            <span class="text-muted small">Nenhuma integração ativa no sistema.</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">Selecione quais módulos este usuário poderá acessar no Hub.</small>
                            </div>
                        </div>

                        <div class="mt-5 text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold">Criar Usuário</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

