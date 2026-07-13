<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Models\UsuarioLog;
use App\Middleware\SessionManager;
use Exception;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LoginController extends BaseController {
    private const VIEW = 'pages/login/form_login.twig';
    private const ROUTE = '/login';
    private const DESTINATION = '/dashboard';

    public function index(Request $request, Response $response): Response {
        $session = new SessionManager();

        if ($session->check()) {
            return $this->redirect($response, self::DESTINATION);
        }

        return $this->renderView($request, $response, self::VIEW, $this->getFlash());
    }

    public function login(Request $request, Response $response): Response {

        try {

            $dados = $request->getParsedBody();

            if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                $this->flash('info', 'E-mail inválido');
                return $this->redirect($response, self::ROUTE);
            }

            $usuario = Usuario::with('gabinete')
                ->where('email', $dados['email'])
                ->first();


            if (!$usuario) {
                $this->flash('info', 'Usuário não encontrado');
                return $this->redirect($response, self::ROUTE);
            }

            if (!password_verify($dados['senha'], $usuario->senha)) {
                $this->flash('info', 'Senha incorreta');
                return $this->redirect($response, self::ROUTE);
            }

            if (!$usuario->ativo) {
                $this->flash('info', 'Usuário desativado');
                return $this->redirect($response, self::ROUTE);
            }

            if (!$usuario->gabinete || !$usuario->gabinete->ativo) {
                $this->flash('info', 'Gabinete desativado');
                return $this->redirect($response, self::ROUTE);
            }

            $session = new SessionManager();

            $session->login([
                'id'               => $usuario->id,
                'nome'             => $usuario->nome,
                'email'            => $usuario->email,
                'nivel'            => $usuario->tipo_usuario_id,
                'gabinete_id'      => $usuario->gabinete_id,
                'id_parlamentar'   => $usuario->gabinete->id_parlamentar,
                'nome_parlamentar' => $usuario->gabinete->nome,
                'tipo_gabinete'    => $usuario->gabinete->tipo_gabinete_id
            ]);

            UsuarioLog::create([
                'usuario_id' => $usuario->id
            ]);

            return $this->redirect($response, self::DESTINATION);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function logout(Request $request, Response $response): Response {
        $session = new SessionManager();
        $session->logout();
        return $this->redirect($response, self::ROUTE);
    }
}
