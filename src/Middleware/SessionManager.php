<?php

namespace App\Middleware;

class SessionManager {

    private string $sessionPath;

    public function __construct() {

        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $this->sessionPath = dirname(__DIR__, 2) . '/storage/sessions';

        if (!is_dir($this->sessionPath)) {
            mkdir($this->sessionPath, 0777, true);
        }

        session_save_path($this->sessionPath);

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.cookie_samesite', 'Strict');

        session_start();
    }

    public function login(array $dados): void {

        session_regenerate_id(true);

        $_SESSION['usuario'] = $dados;
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['created_at'] = time();
    }

    public function check(): bool {

        if (empty($_SESSION['usuario'])) {
            return false;
        }

        if (($_SESSION['ip'] ?? '') !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
            $this->destroy();
            return false;
        }

        return true;
    }

    public function user(): ?array {

        return $_SESSION['usuario'] ?? null;
    }

    public function logout(): void {

        $this->destroy();
    }

    private function destroy(): void {

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {

            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}
