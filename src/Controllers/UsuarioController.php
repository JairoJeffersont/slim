<?php

namespace App\Controllers;

use App\Models\Gabinete;
use App\Models\TipoUsuario;
use App\Models\Usuario;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Classe UsuarioController
 *
 * Responsável por gerenciar o ciclo de vida dos usuários (criação via convite, alteração
 * de status, modificação de cargos) e o fluxo de recuperação de senhas esquecidas.
 * Includes regras de negócio específicas para impedir que um gabinete fique sem administradores ativos.
 *
 * @package App\Controllers
 */
class UsuarioController extends BaseController {

    /** @var string Template para o formulário de cadastro de novo usuário via convite. */
    private const VIEW = 'pages/usuario/novo_usuario.twig';

    /** @var string Template para a ficha de gerenciamento do usuário (visão do admin). */
    private const VIEW_FICHA = 'pages/usuario/ficha_usuario.twig';

    /** @var string Template para a tela de solicitação de recuperação de senha. */
    private const VIEW_ESQUECI_SENHA = 'pages/usuario/esqueci_senha.twig';

    /** @var string Template para a tela de definição da nova senha. */
    private const VIEW_NOVA_SENHA = 'pages/usuario/nova_senha.twig';

    /**
     * Exibe a ficha de detalhes e gerenciamento de um usuário específico.
     * * Apenas administradores podem acessar.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param array $args Argumentos da rota, contendo o ['id'] do usuário.
     * @return Response Redirecionamento caso não tenha permissão, ou a view da ficha do usuário.
     */
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

    /**
     * Atualiza o nível de permissão (tipo) de um usuário.
     * * Impede a alteração se o usuário for o único administrador ativo do gabinete.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param array $args Argumentos da rota, contendo o ['id'] do usuário.
     * @return Response A resposta HTTP com a view atualizada e mensagem de feedback.
     */
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

    /**
     * Alterna o status de atividade (Ativo/Inativo) de um usuário.
     * * Impede a desativação se o usuário for o único administrador ativo do gabinete.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param array $args Argumentos da rota, contendo o ['id'] do usuário.
     * @return Response A resposta HTTP com a view atualizada e mensagem de feedback.
     */
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

    /**
     * Exibe o formulário público de cadastro para um novo usuário aceitando um convite.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param array $args Argumentos da rota, contendo o ['token'] do gabinete.
     * @return Response A view de cadastro ou mensagem de erro de token inválido.
     */
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

    /**
     * Processa a submissão do formulário de convite e cria o novo usuário (com status inativo).
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param array $args Argumentos da rota, contendo o ['token'] do gabinete.
     * @return Response A resposta HTTP contendo mensagens de sucesso ou validação.
     */
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

    /**
     * Busca um gabinete correspondente ao token alfanumérico fornecido.
     *
     * @param string $token Token único do gabinete.
     * @return Gabinete|null O modelo do gabinete localizado ou null.
     */
    private function buscarGabinetePorToken(string $token): ?Gabinete {
        return Gabinete::where('token', $token)->first();
    }

    /**
     * Valida os campos do formulário de criação de novo usuário via convite.
     *
     * @param array $dados Dados vindos da requisição POST.
     * @return string|null Texto descritivo do erro ou null se estiver válido.
     */
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

    /**
     * Exibe a página pública para solicitação de recuperação de senha.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param array $args Argumentos da rota.
     * @return Response A view de esqueci a senha renderizada.
     */
    public function esqueciSenha(Request $request, Response $response, array $args): Response {
        return $this->render($request, $response, self::VIEW_ESQUECI_SENHA);
    }

    /**
     * Gera e armazena um token temporário de recuperação se o e-mail existir na base de dados.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param array $args Argumentos da rota.
     * @return Response A resposta HTTP com mensagem de sucesso ou erro.
     */
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

    /**
     * Valida o token recebido pela URL e exibe a tela de definição da nova senha.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param array $args Argumentos da rota contendo o ['token'].
     * @return Response A view para digitação da nova senha ou erro se expirado/inválido.
     */
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

    /**
     * Valida as credenciais digitadas e efetiva a alteração definitiva de senha do usuário.
     * Limpa o token de recuperação após o sucesso.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param array $args Argumentos da rota contendo o ['token'].
     * @return Response A resposta HTTP indicando o resultado do processo de alteração.
     */
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
