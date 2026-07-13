<?php

namespace App\Controllers;

use App\Models\Gabinete;
use App\Models\TipoUsuario;
use App\Models\Usuario;
use Exception;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UsuarioController extends BaseController {

    private const VIEW = 'pages/usuario/ficha-usuario.twig';
    private const ROUTE = '/usuario';
    private int $NIVEL_LOGADO;
    private int $LOGGED_USER_ID;

    public function __construct() {
        $this->NIVEL_LOGADO = $_SESSION['usuario']['nivel'];
        $this->LOGGED_USER_ID = $_SESSION['usuario']['id'];
    }

    public function index(Request $request, Response $response, array $args): Response {

        if ($this->NIVEL_LOGADO != 1) {
            $this->flash('info', 'Você não tem autorização para acessar essa área');
            return $this->redirect($response, '/dashboard');
        }

        $id = $args['id'];

        $payload = [];
        $payload['aniversario'] = false;

        try {

            $usuario = Usuario::with(['gabinete', 'logs' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])->find($id);

            if (!$usuario) {
                $this->flash('info', 'Usuário não encontrado');
                return $this->redirect($response, '/gabinete');
            }

            $tiposUsuario = TipoUsuario::get();

            $payload['usuario'] = $usuario;
            $payload['tipos'] = $tiposUsuario;

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

    public function updateUser(Request $request, Response $response, array $args): Response {

        try {
            $id = $args['id'];
            $dados = $request->getParsedBody();

            $usuario = Usuario::findOrFail($id);

            if ($usuario->id == $this->LOGGED_USER_ID) {

                $adminsAtivos = Usuario::where('tipo_usuario_id', 1)->where('ativo', 1)->count();

                $vaiDeixarDeSerAdmin = (int) $dados['tipo'] !== 1;
                $vaiDesativar = (int) $dados['ativo'] === 0;

                if ($adminsAtivos === 1 && ($vaiDeixarDeSerAdmin || $vaiDesativar)) {
                    $this->flash('info', 'Você é o único administrador ativo');
                    return $this->redirect($response, self::ROUTE . '/' . $id);
                }
            }

            $usuario->update([
                'tipo_usuario_id' => $dados['tipo'],
                'ativo' => $dados['ativo']
            ]);

            $this->flash('success', 'Usuário atualizado com sucesso');
            return $this->redirect($response, self::ROUTE . '/' . $id);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE . '/' . $id);
        }
    }
}
