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

    public function index(Request $request, Response $response): Response {
        return $this->render($request, $response, self::VIEW);
    }

    public function login(Request $request, Response $response): Response {
        try {
            $dados = $this->input($request);

            if (empty($dados['email'])) {
                return $this->render($request, $response, self::VIEW, $this->info('E-mail inválido'));
            }

            if (empty($dados['senha'])) {
                return $this->render($request, $response, self::VIEW, $this->info('Senha inválida'));
            }

            $usuario = Usuario::with('gabinete')
                ->where('email', $dados['email'])
                ->first();

            if (!$usuario) {
                return $this->render($request, $response, self::VIEW, $this->info('Usuário não encontrado'));
            }

            if (!password_verify($dados['senha'], $usuario->senha)) {
                return $this->render($request, $response, self::VIEW, $this->info('Senha inválida'));
            }

            if (!$usuario->ativo) {
                return $this->render($request, $response, self::VIEW, $this->info('Usuário desativado'));
            }

            if (!$usuario->gabinete || !$usuario->gabinete->ativo) {
                return $this->render($request, $response, self::VIEW, $this->info('Gabinete desativado'));
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

            return $this->redirect($response, '/dashboard');
        } catch (Exception $e) {
            return $this->renderServerError($request, $response, self::VIEW, $e, 'LoginController@login');
        }
    }

    public function logout(Request $request, Response $response): Response {
        $session = new SessionManager();
        $session->logout();

        return $this->redirect($response, '/login');
    }
}
