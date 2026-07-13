<?php

namespace App\Controllers;

use App\Models\Usuario;
use Exception;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PerfilController extends BaseController {
    private const VIEW = 'pages/usuario/perfil.twig';
    private const ROUTE = '/perfil';

    private int $LOGGED_USER_ID;

    public function __construct() {
        $this->LOGGED_USER_ID = $_SESSION['usuario']['id'];
    }

    public function index(Request $request, Response $response): Response {
        $payload = [];
        $payload['aniversario'] = false;

        try {
            $usuario = Usuario::with(['gabinete', 'logs' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(5);
            }])->find($this->LOGGED_USER_ID);

            $payload['usuario'] = $usuario;

            if ($usuario->aniversario && date('m-d') === date('m-d', strtotime($usuario->aniversario))) {
                $payload['aniversario'] = true;
            }

            $payload = array_merge($payload, $this->getFlash());
            return $this->renderView($request, $response, self::VIEW, $payload);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW, $this->getFlash());
        }
    }

    public function updateUser(Request $request, Response $response): Response {

        try {

            $dados = $request->getParsedBody();

            if (!isset($dados['nome']) || empty($dados['nome'])) {
                $this->flash('info', 'O campo nome é obrigatório');
                return $this->redirect($response, self::ROUTE);
            }

            if (!isset($dados['email']) || empty($dados['email'])) {
                $this->flash('info', 'O campo email é obrigatório');
                return $this->redirect($response, self::ROUTE);
            }

            $usuario = Usuario::find($this->LOGGED_USER_ID);

            $usuario->update([
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'telefone' => $dados['telefone'] ?? null,
                'aniversario' => !empty($dados['aniversario'])
                    ? '2000-' . implode('-', array_reverse(explode('/', $dados['aniversario'])))
                    : null,
            ]);

            $_SESSION['usuario']['nome'] = $dados['nome'];

            $this->flash('success', 'Usuário atualizado com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }
}
