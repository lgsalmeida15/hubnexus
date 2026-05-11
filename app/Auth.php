<?php

namespace App;

class Auth
{
    public static function init()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLoggedIn()
    {
        self::init();
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }

    public static function hasRole($roles)
    {
        if (!self::isLoggedIn()) return false;
        if (is_array($roles)) {
            return in_array($_SESSION['user_perfil'], $roles);
        }
        return $_SESSION['user_perfil'] === $roles;
    }

    public static function requireRole($roles)
    {
        self::requireLogin();
        if (!self::hasRole($roles)) {
            http_response_code(403);
            die("Acesso negado. Você não tem permissão para acessar esta página.");
        }
    }

    public static function login($user)
    {
        self::init();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_perfil'] = $user['perfil'];
        $_SESSION['user_permissoes'] = json_decode($user['permissoes'] ?? '[]', true);
        
        // Cache das integrações permitidas para evitar queries repetitivas no Hub
        self::refreshIntegrationCache();
    }

    public static function refreshIntegrationCache()
    {
        $db = Config::getDatabaseConnection();
        $user_perms = $_SESSION['user_permissoes'] ?? [];
        
        if ($_SESSION['user_perfil'] === 'admin') {
            $integracoes = $db->query("SELECT * FROM integracoes WHERE ativo = TRUE ORDER BY nome ASC")->fetchAll();
        } else {
            if (empty($user_perms)) {
                $integracoes = [];
            } else {
                $placeholders = implode(',', array_fill(0, count($user_perms), '?'));
                $stmt = $db->prepare("SELECT * FROM integracoes WHERE ativo = TRUE AND slug IN ($placeholders) ORDER BY nome ASC");
                $stmt->execute($user_perms);
                $integracoes = $stmt->fetchAll();
            }
        }
        $_SESSION['user_integracoes_cache'] = $integracoes;
    }

    public static function getPermittedIntegrations()
    {
        self::init();
        if (!isset($_SESSION['user_integracoes_cache'])) {
            self::refreshIntegrationCache();
        }
        return $_SESSION['user_integracoes_cache'];
    }

    public static function canAccess($integration)
    {
        if (!self::isLoggedIn()) return false;
        // Admins podem acessar tudo por padrão, ou verificamos a lista
        if ($_SESSION['user_perfil'] === 'admin') return true;
        
        $permissoes = $_SESSION['user_permissoes'] ?? [];
        return in_array($integration, $permissoes);
    }

    public static function logout()
    {
        self::init();
        session_destroy();
        header('Location: login.php');
        exit;
    }

    public static function getCsrfToken()
    {
        self::init();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfInput()
    {
        return '<input type="hidden" name="csrf_token" value="' . self::getCsrfToken() . '">';
    }

    public static function validateCsrfToken($token)
    {
        self::init();
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
