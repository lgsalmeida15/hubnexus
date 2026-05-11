<?php
use App\Auth;
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'HubNexus'; ?></title>
    
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
    
    <!-- Global App JS -->
    <script src="assets/js/app.js"></script>
    <script>
        HubNexus.csrfToken = '<?php echo Auth::getCsrfToken(); ?>';
    </script>
    
    <?php echo $extraHead ?? ''; ?>
</head>
<body data-user-id="<?php echo $_SESSION['user_id'] ?? ''; ?>" data-can-edit="<?php echo Auth::hasRole(['admin', 'edit']) ? '1' : '0'; ?>">
    <nav class="navbar navbar-expand-lg border-bottom bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold" href="index.php">
                <!-- Logo -->
                <img src="assets/img/claro.png" id="navbar-logo" alt="HubNexus" class="me-2" style="max-height: 40px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span style="display:none; color: var(--hub-primary);">HubNexus</span>
                <span class="ms-1 d-none d-sm-inline">HubNexus</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage ?? '') === 'hub' ? 'active fw-bold' : ''; ?>" href="index.php">
                            <i class="bi bi-grid-fill me-1"></i> Hub
                        </a>
                    </li>
                    <?php if (Auth::isLoggedIn()): ?>
                        <?php if (Auth::hasRole('admin')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage ?? '') === 'usuarios' ? 'active fw-bold' : ''; ?>" href="usuarios.php">
                                <i class="bi bi-people-fill me-1"></i> Usuários
                            </a>
                        </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <div class="d-flex align-items-center">
                    <div id="theme-toggle" class="theme-toggle me-3 text-body">
                        <i class="bi bi-moon-fill fs-5"></i>
                    </div>
                    
                    <?php if (Auth::isLoggedIn()): ?>
                        <div class="dropdown">
                            <button class="btn btn-link nav-link dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle fs-5 me-2"></i>
                                <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['user_nome']); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><h6 class="dropdown-header">Perfil: <?php echo ucfirst($_SESSION['user_perfil']); ?></h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sair</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-sm px-4 rounded-pill">Entrar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="py-4 flex-grow-1">
