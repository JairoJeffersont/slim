<?php

namespace App\Controllers;

use App\Models\Gabinete;
use App\Models\TipoUsuario;
use App\Models\Usuario;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UsuarioController extends BaseController {
    private const VIEW = 'pages/usuario/novo_usuario.twig';
    private const VIEW_FICHA = 'pages/usuario/ficha_usuario.twig';
    private const VIEW_ESQUECI_SENHA = 'pages/usuario/esqueci_senha.twig';
    private const VIEW_NOVA_SENHA = 'pages/usuario/nova_senha.twig';

    public function index(Request $request, Response $response, array $args): Response {
        $id = (int) $args['id'];

        $usuario = Usuario::with(['tipoUsuario', 'logs'])->find($id);

        if (!$this->isAdmin() || !$usuario) {
            return $this->redirect($response, '/meu-gabinete');
        }

        return $this->render($request, $response, self::VIEW_FICHA, [
            'usuario' => $usuario,
            'tiposUsuario' => TipoUsuario::all()
        ]);
    }

    public function atualizarTipo(Request $request, Response $response, array $args): Response {
        $id = (int) $args['id'];
        $dados = $this->input($request);

        $usuario = Usuario::find($id);

        if ($usuario && !empty($dados['usuario_tipo'])) {

            if ($usuario->tipo_usuario_id == 1 && $dados['usuario_tipo'] != 1) {

                $totalAdministradoresAtivos = Usuario::where('gabinete_id', $usuario->gabinete_id)
                    ->where('tipo_usuario_id', 1)
                    ->where('ativo', true)
                    ->count();

                if ($totalAdministradoresAtivos <= 1) {
                    return $this->render($request, $response, self::VIEW_FICHA, $this->info(
                        'Você é o único administrador ativo desse gabinete.',
                        [
                            'usuario' => Usuario::with(['tipoUsuario', 'logs'])->find($id),
                            'tiposUsuario' => TipoUsuario::all()
                        ]
                    ));
                }
            }

            $usuario->tipo_usuario_id = $dados['usuario_tipo'];
            $usuario->save();
        }

        return $this->render($request, $response, self::VIEW_FICHA, $this->success(
            'Tipo de usuário atualizado com sucesso.',
            [
                'usuario' => Usuario::with(['tipoUsuario', 'logs'])->find($id),
                'tiposUsuario' => TipoUsuario::all()
            ]
        ));
    }

    public function alterarStatus(Request $request, Response $response, array $args): Response {
        $id = (int) $args['id'];

        $usuario = Usuario::find($id);

        if ($usuario) {

            if ($usuario->tipo_usuario_id == 1 && $usuario->ativo) {

                $totalAdministradores = Usuario::where('gabinete_id', $usuario->gabinete_id)
                    ->where('tipo_usuario_id', 1)
                    ->where('ativo', true)
                    ->count();

                if ($totalAdministradores <= 1) {
                    return $this->render($request, $response, self::VIEW_FICHA, $this->info(
                        'Você é o único administrador ativo desse gabinete.',
                        [
                            'usuario' => Usuario::with(['tipoUsuario', 'logs'])->find($id),
                            'tiposUsuario' => TipoUsuario::all()
                        ]
                    ));
                }
            }

            $usuario->ativo = !$usuario->ativo;
            $usuario->save();
        }

        return $this->render($request, $response, self::VIEW_FICHA, $this->success(
            'Status do usuário atualizado com sucesso.',
            [
                'usuario' => Usuario::with(['tipoUsuario', 'logs'])->find($id),
                'tiposUsuario' => TipoUsuario::all()
            ]
        ));
    }

    public function novoUsuario(Request $request, Response $response, array $args): Response {
        $token = $args['token'];
        $gabinete = $this->buscarGabinetePorToken($token);

        if (!$gabinete) {
            return $this->render($request, $response, self::VIEW, $this->info('Token inválido'));
        }

        return $this->render($request, $response, self::VIEW, [
            'gabinete' => $gabinete
        ]);
    }

    public function salvarNovoUsuario(Request $request, Response $response, array $args): Response {
        $token = $args['token'];
        $dados = $this->input($request);

        try {
            $gabinete = $this->buscarGabinetePorToken($token);

            if (!$gabinete) {
                return $this->render($request, $response, self::VIEW, $this->info('Token inválido', [
                    'dados' => $dados
                ]));
            }

            $erro = $this->validarNovoUsuario($dados);
            if ($erro) {
                return $this->render($request, $response, self::VIEW, $this->info($erro, [
                    'dados' => $dados,
                    'gabinete' => $gabinete
                ]));
            }

            if (Usuario::where('email', $dados['email'])->exists()) {
                return $this->render($request, $response, self::VIEW, $this->info(
                    'Esse usuário já está cadastrado nesse gabinete',
                    ['dados' => $dados, 'gabinete' => $gabinete]
                ));
            }

            Usuario::create([
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'telefone' => $dados['telefone'] ?? null,
                'senha' => password_hash($dados['senha'], PASSWORD_DEFAULT),
                'aniversario' => !empty($dados['aniversario'])
                    ? '2000-' . implode('-', array_reverse(explode('/', $dados['aniversario'])))
                    : null,
                'ativo' => false,
                'tipo_usuario_id' => 2,
                'gabinete_id' => $gabinete->id
            ]);

            return $this->render($request, $response, self::VIEW, $this->success(
                'Usuário cadastrado com sucesso. Aguarde o gestor ativar sua conta...',
                ['gabinete' => $gabinete]
            ));
        } catch (Exception $e) {
            return $this->renderServerError($request, $response, self::VIEW, $e, 'UsuarioController@salvarNovoUsuario');
        }
    }

    private function buscarGabinetePorToken(string $token): ?Gabinete {
        return Gabinete::where('token', $token)->first();
    }

    private function validarNovoUsuario(array $dados): ?string {
        if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha']) || empty($dados['confirmar_senha'])) {
            return 'Preencha os campos obrigatórios';
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            return 'E-mail inválido';
        }

        if ($dados['senha'] !== $dados['confirmar_senha']) {
            return 'As senhas não conferem';
        }

        return null;
    }

    public function esqueciSenha(Request $request, Response $response, array $args): Response {
        return $this->render($request, $response, self::VIEW_ESQUECI_SENHA);
    }

    public function enviarRecuperacaoSenha(Request $request, Response $response, array $args): Response {
        $dados = $this->input($request);

        $usuario = Usuario::where('email', $dados['email'])->first();

        if (!$usuario) {
            return $this->render($request, $response, self::VIEW_ESQUECI_SENHA, $this->info(
                'E-mail não encontrado'
            ));
        }

        $token = bin2hex(random_bytes(32));

        $usuario->reset_token = $token;
        $usuario->reset_token_expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $usuario->save();

        // Aqui futuramente entra o envio do e-mail

        return $this->render($request, $response, self::VIEW_ESQUECI_SENHA, $this->success(
            'Um link de recuperação foi enviado para seu e-mail.'
        ));
    }

    public function novaSenha(Request $request, Response $response, array $args): Response {
        $token = $args['token'];

        $usuario = Usuario::where('reset_token', $token)
            ->where('reset_token_expira', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$usuario) {
            return $this->render($request, $response, self::VIEW_ESQUECI_SENHA, $this->info(
                'Link inválido ou expirado.'
            ));
        }

        return $this->render($request, $response, self::VIEW_NOVA_SENHA, [
            'token' => $token
        ]);
    }

    public function salvarNovaSenha(Request $request, Response $response, array $args): Response {
        $token = $args['token'];
        $dados = $this->input($request);

        $usuario = Usuario::where('reset_token', $token)
            ->where('reset_token_expira', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$usuario) {
            return $this->render($request, $response, self::VIEW_ESQUECI_SENHA, $this->info(
                'Link inválido ou expirado.'
            ));
        }

        if ($dados['senha'] !== $dados['confirmar_senha']) {
            return $this->render($request, $response, self::VIEW_NOVA_SENHA, $this->info(
                'As senhas não conferem.',
                ['token' => $token]
            ));
        }

        $usuario->senha = password_hash($dados['senha'], PASSWORD_DEFAULT);
        $usuario->reset_token = null;
        $usuario->reset_token_expira = null;
        $usuario->save();

        return $this->render($request, $response, self::VIEW_NOVA_SENHA, $this->success(
            'Senha alterada com sucesso.'
        ));
    }
}
