<?php

namespace App\Controllers;

use App\Models\Usuario;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PerfilController extends BaseController {
    private const VIEW = 'pages/perfil/meus_dados.twig';

    public function index(Request $request, Response $response): Response {
        try {
            $usuario = $this->getUsuarioLogado();

            if (!$usuario) {
                throw new Exception('Usuário não encontrado');
            }

            return $this->renderForm($request, $response, $this->dadosBase($usuario));
        } catch (Exception $e) {
            return $this->renderErro($request, $response, $e);
        }
    }

    public function atualizar(Request $request, Response $response): Response {
        try {
            $dados = $this->input($request);
            $usuario = $this->getUsuarioLogado();

            if (!$usuario) {
                throw new Exception('Usuário não encontrado');
            }

            $erro = $this->validarDadosPerfil($dados);
            if ($erro) {
                return $this->renderForm($request, $response, $this->dadosBase($usuario, $this->info($erro)));
            }

            $usuario->update([
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'telefone' => $dados['telefone'] ?? null,
                'aniversario' => !empty($dados['aniversario'])
                    ? '2000-' . implode('-', array_reverse(explode('/', $dados['aniversario'])))
                    : null,
            ]);

            $usuario = $this->getUsuarioLogado();

            return $this->renderForm($request, $response, $this->dadosBase($usuario, $this->success('Dados atualizados com sucesso')));
        } catch (Exception $e) {
            return $this->renderErro($request, $response, $e);
        }
    }

    private function getUsuarioLogado(): ?Usuario {
        $usuarioId = $this->user()['id'] ?? null;

        if (!$usuarioId) {
            return null;
        }

        return Usuario::with(['gabinete', 'gabinete.tipoGabinete'])
            ->find($usuarioId);
    }

    private function validarDadosPerfil(array $dados): ?string {
        if (empty($dados['nome']) || empty($dados['email'])) {
            return 'Nome e email são obrigatórios';
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            return 'E-mail inválido';
        }

        return null;
    }

    private function dadosBase(Usuario $usuario, array $extra = []): array {
        $usuarioSessao = $this->user();
        $nivelUsuario = $usuarioSessao['nivel'] ?? null;
        $gabineteId = $usuarioSessao['gabinete_id'] ?? null;

        return array_merge([
            'usuario' => $usuario->toArray(),
            'usuarios' => $nivelUsuario === 1 && $gabineteId
                ? Usuario::where('gabinete_id', $gabineteId)->get()->toArray()
                : [],
            'mostrarUsuarios' => $nivelUsuario === 1,
            'foto_parlamentar' => $this->buscarFotoParlamentar($usuario),
            'url_convite' => $_ENV['BASE_URL'] . 'novo-usuario/' . $usuario->gabinete->token
        ], $extra);
    }

    private function buscarFotoParlamentar(Usuario $usuario): ?string {
        $tipoGabinete = $usuario->gabinete->tipo_gabinete_id;
        $idParlamentar = $usuario->gabinete->id_parlamentar;

        return match ($tipoGabinete) {
            1 => "https://www.camara.leg.br/internet/deputado/bandep/{$idParlamentar}.jpg",
            2 => "https://legis.senado.leg.br/senadores/fotos-oficiais/{$idParlamentar}",
            default => null,
        };
    }

    private function renderErro(Request $request, Response $response, Exception $e): Response {
        $usuario = $this->getUsuarioLogado();

        return $this->renderServerError(
            $request,
            $response,
            self::VIEW,
            $e,
            'PerfilController',
            $usuario ? $this->dadosBase($usuario) : []
        );
    }

    private function renderForm(Request $request, Response $response, array $data = []): Response {
        return $this->render($request, $response, self::VIEW, $data);
    }
}
