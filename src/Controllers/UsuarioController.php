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
    private const VIEW_NEW_USER = 'pages/usuario/form-novo-usuario.twig';
    private const ROUTE = '/usuario';
    private const ROUTE_NEW_USER = '/novo-usuario/';

    private int $NIVEL_LOGADO;
    private int $LOGGED_USER_ID;

    public function __construct() {
        $this->NIVEL_LOGADO = $_SESSION['usuario']['nivel'] ?? 0;
        $this->LOGGED_USER_ID = $_SESSION['usuario']['id'] ?? 0;
    }

    private function getGabineteByToken(string $token): ?Gabinete {
        return Gabinete::where('token', $token)->first();
    }

    public function index(Request $request, Response $response, array $args): Response {

        if ($this->NIVEL_LOGADO !== 1) {
            $this->flash('info', 'Você não tem autorização para acessar essa área');
            return $this->redirect($response, '/dashboard');
        }

        try {

            $usuario = Usuario::with([
                'gabinete',
                'logs' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])->find($args['id']);

            if (!$usuario) {
                $this->flash('info', 'Usuário não encontrado');
                return $this->redirect($response, '/gabinete');
            }

            $payload = [
                'usuario' => $usuario,
                'tipos' => TipoUsuario::all(),
                'aniversario' => $usuario->aniversario && date('m-d') === date('m-d', strtotime($usuario->aniversario))
            ];

            return $this->renderView($request, $response, self::VIEW, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW, $this->getFlash());
        }
    }

    public function updateUser(Request $request, Response $response, array $args): Response {

        $id = $args['id'];

        try {

            $dados = $request->getParsedBody();
            $usuario = Usuario::findOrFail($id);

            $tipo = (int) $dados['tipo'];
            $ativo = (int) $dados['ativo'];

            if ($usuario->id === $this->LOGGED_USER_ID) {

                $adminsAtivos = Usuario::where('tipo_usuario_id', 1)
                    ->where('ativo', 1)
                    ->count();

                $vaiDeixarDeSerAdmin = $tipo !== 1;
                $vaiDesativar = $ativo === 0;

                if ($adminsAtivos === 1 && ($vaiDeixarDeSerAdmin || $vaiDesativar)) {
                    $this->flash('info', 'Você é o único administrador ativo');
                    return $this->redirect($response, self::ROUTE . '/' . $id);
                }
            }

            if (!$usuario->ativo && $ativo === 1) {

                $assinaturasUtilizadas = Usuario::where('gabinete_id', $usuario->gabinete_id)
                    ->where('ativo', 1)
                    ->count();

                $gabinete = Gabinete::find($usuario->gabinete_id);

                if ($assinaturasUtilizadas >= $gabinete->assinaturas) {
                    $this->flash('info', 'Não há assinaturas disponíveis para ativar este usuário.');
                    return $this->redirect($response, self::ROUTE . '/' . $id);
                }
            }

            $usuario->update([
                'tipo_usuario_id' => $tipo,
                'ativo' => $ativo
            ]);

            $this->flash('success', 'Usuário atualizado com sucesso');
            return $this->redirect($response, self::ROUTE . '/' . $id);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE . '/' . $id);
        }
    }

    public function formNewUser(Request $request, Response $response, array $args): Response {

        try {

            $token = $args['token'];
            $gabinete = $this->getGabineteByToken($token);

            if (!$gabinete) {
                $this->flash('info', 'Token inválido');
                return $this->renderView($request, $response, self::VIEW_NEW_USER, $this->getFlash());
            }

            return $this->renderView(
                $request,
                $response,
                self::VIEW_NEW_USER,
                array_merge([
                    'gabinete' => $gabinete
                ], $this->getFlash())
            );
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_NEW_USER, $this->getFlash());
        }
    }

    public function newUser(Request $request, Response $response, array $args): Response {

        try {

            $dados = $request->getParsedBody();
            $token = $args['token'];

            $gabinete = $this->getGabineteByToken($token);

            if (!$gabinete) {
                $this->flash('info', 'Token inválido');
                return $this->redirect($response, self::ROUTE_NEW_USER . $token);
            }

            $camposObrigatorios = ['nome', 'email', 'senha', 'senha2', 'telefone', 'aniversario'];

            foreach ($camposObrigatorios as $campo) {
                if (!isset($dados[$campo]) || trim((string) $dados[$campo]) === '') {
                    $this->flash('info', 'Todos os campos são obrigatórios');
                    return $this->redirect($response, self::ROUTE_NEW_USER . $token);
                }
            }

            $nome = trim($dados['nome']);
            $email = strtolower(trim($dados['email']));
            $telefone = preg_replace('/\D/', '', $dados['telefone']);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->flash('info', 'E-mail inválido');
                return $this->redirect($response, self::ROUTE_NEW_USER . $token);
            }

            if ($dados['senha'] !== $dados['senha2']) {
                $this->flash('info', 'As senhas não conferem');
                return $this->redirect($response, self::ROUTE_NEW_USER . $token);
            }

            if (Usuario::where('email', $email)->exists()) {
                $this->flash('info', 'Esse usuário já está cadastrado');
                return $this->redirect($response, self::ROUTE_NEW_USER . $token);
            }


            Usuario::create([
                'nome' => $nome,
                'email' => $email,
                'senha' => password_hash($dados['senha'], PASSWORD_DEFAULT),
                'telefone' => $telefone,
                'aniversario' => !empty($dados['aniversario'])
                    ? '2000-' . implode('-', array_reverse(explode('/', $dados['aniversario'])))
                    : null,
                'ativo' => 0,
                'tipo_usuario_id' => 2,
                'gabinete_id' => $gabinete->id
            ]);

            $this->flash('success', 'Cadastro realizado com sucesso. Aguarde a ativação da sua conta.');
            return $this->redirect($response, self::ROUTE_NEW_USER . $token);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_NEW_USER, $this->getFlash());
        }
    }
}
