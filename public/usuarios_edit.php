<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Auth;

Auth::requireRole('admin');

$db = Config::getDatabaseConnection();
$error = '';
$message = '';
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: usuarios.php');
    exit;
}

// Busca integrações disponíveis
$integracoes = $db->query("SELECT * FROM integracoes WHERE ativo = TRUE ORDER BY nome ASC")->fetchAll();

// Busca usuário
$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: usuarios.php?message=Usuário não encontrado');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erro de validação CSRF.';
    } else {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $perfil = $_POST['perfil'];
        $permissoes = json_encode($_POST['permissoes'] ?? []);

        try {
            if (!empty($_POST['senha'])) {
                $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET nome = ?, email = ?, perfil = ?, permissoes = ?, senha = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $perfil, $permissoes, $senha, $id]);
            } else {
                $stmt = $db->prepare("UPDATE usuarios SET nome = ?, email = ?, perfil = ?, permissoes = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $perfil, $permissoes, $id]);
            }
            
            header('Location: usuarios.php?message=Usuário atualizado com sucesso!');
            exit;
        } catch (\PDOException $e) {
            $error = "Erro ao atualizar: " . $e->getMessage();
        }
    }
}

$user_perms = json_decode($user['permissoes'] ?? '[]', true);

$pageTitle = 'Editar Usuário - HubNexus';
$currentPage = 'usuarios';

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">Editar Usuário: <?php echo htmlspecialchars($user['nome']); ?></h4>
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
                                <label class="form-label small fw-bold">Nome</label>
                                <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($user['nome']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">E-mail</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Perfil (Tipo de Permissão)</label>
                                <select name="perfil" class="form-select" required>
                                    <option value="view" <?php echo $user['perfil'] === 'view' ? 'selected' : ''; ?>>Visualização (View)</option>
                                    <option value="edit" <?php echo $user['perfil'] === 'edit' ? 'selected' : ''; ?>>Edição (Edit)</option>
                                    <option value="admin" <?php echo $user['perfil'] === 'admin' ? 'selected' : ''; ?>>Administrador (Admin)</option>
                                </select>
                                <small class="text-muted mt-1 d-block">O perfil define as ações dentro das integrações.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nova Senha</label>
                                <input type="password" name="senha" class="form-control" placeholder="Deixe em branco para manter">
                            </div>

                            <div class="col-md-12 mt-4">
                                <label class="form-label d-block fw-bold small mb-3">Integrações Permitidas</label>
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <?php foreach ($integracoes as $int): ?>
                                        <div class="form-check form-check-inline me-4">
                                            <input class="form-check-input" type="checkbox" name="permissoes[]" value="<?php echo $int['slug']; ?>" id="perm_<?php echo $int['slug']; ?>" <?php echo in_array($int['slug'], $user_perms) ? 'checked' : ''; ?>>
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
                            </div>
                        </div>

                        <div class="mt-5 text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

