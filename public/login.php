<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Auth;

if (Auth::isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erro de validação CSRF. Por favor, recarregue a página.';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($email && $password) {
            $db = Config::getDatabaseConnection();
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['senha'])) {
                Auth::login($user);
                header('Location: index.php');
                exit;
            } else {
                $error = 'E-mail ou senha inválidos.';
            }
        } else {
            $error = 'Por favor, preencha todos os campos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HubNexus</title>
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { 
            font-family: 'Inter', sans-serif;
            background-color: var(--bs-body-bg);
            display: flex; 
            align-items: center; 
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-card {
            width: 100%;
            max-width: 350px;
            border: none;
            border-radius: 1.5rem;
        }
        .login-logo {
            width: 80px;
            height: 80px;
            background-color: var(--hub-primary);
            color: white;
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.5rem;
            box-shadow: 0 10px 20px rgba(75, 0, 129, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card login-card shadow-lg mx-auto">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <img src="assets/img/claro.png" id="navbar-logo" alt="HubNexus" style="max-height: 80px; max-width: 100%; height: auto;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="login-logo" style="display:none;">
                        <i class="bi bi-grid-fill"></i>
                    </div>
                </div>
                <h2 class="text-center fw-bold mb-2">HubNexus</h2>
                <p class="text-center text-muted mb-4">Acesse sua conta para gerenciar integrações</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 small py-2 mb-4">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <?php echo Auth::csrfInput(); ?>
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-bold">E-mail</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" name="email" id="email" class="form-control bg-light border-start-0" placeholder="seu@email.com" required autofocus>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label small fw-bold">Senha</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" name="password" id="password" class="form-control bg-light border-start-0" placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm">
                        Entrar <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <div id="theme-toggle" class="theme-toggle d-inline-block text-muted">
                <i class="bi bi-moon-fill fs-5"></i>
            </div>
        </div>
    </div>

    <!-- Theme Toggle Script -->
    <script src="assets/js/theme.js"></script>
</body>
</html>
