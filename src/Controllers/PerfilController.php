<?php

namespace App\Controllers;

use App\Models\Usuario;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Classe PerfilController
 *
 * Responsável por gerenciar a visualização e atualização dos dados cadastrais do próprio usuário logado,
 * além de listar membros do mesmo gabinete caso o usuário possua privilégios administrativos.
 *
 * @package App\Controllers
 */
class PerfilController extends BaseController {

    /**
     * @var string Caminho relativo do template Twig para a página de perfil/meus dados.
     */
    private const VIEW = 'pages/perfil/meus_dados.twig';

    /**
     * Exibe a página de perfil com os dados do usuário logado e informações do gabinete.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @return Response A resposta HTTP contendo a view do perfil renderizada.
     * @throws Exception Se o usuário logado não for encontrado na base de dados.
     */
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

    /**
     * Processa a atualização dos dados cadastrais (nome, e-mail, telefone e aniversário) do usuário.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @return Response A resposta HTTP (renderização de sucesso ou com erros de validação/servidor).
     * @throws Exception Se o usuário logado não for encontrado na base de dados.
     */
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

    /**
     * Recupera o modelo do usuário logado baseado no ID guardado na sessão,
     * trazendo aninhados os relacionamentos de Gabinete e Tipo de Gabinete.
     *
     * @return Usuario|null Retorna a instância do modelo Usuario ou null se não houver ID na sessão.
     */
    private function getUsuarioLogado(): ?Usuario {
        $usuarioId = $this->user()['id'] ?? null;

        if (!$usuarioId) {
            return null;
        }

        return Usuario::with(['gabinete', 'gabinete.tipoGabinete'])
            ->find($usuarioId);
    }

    /**
     * Valida os dados obrigatórios e a estrutura de e-mail informada na requisição de atualização.
     *
     * @param array $dados Dados oriundos do corpo da requisição POST.
     * @return string|null Mensagem textual do erro de validação ou null caso os dados estejam válidos.
     */
    private function validarDadosPerfil(array $dados): ?string {
        if (empty($dados['nome']) || empty($dados['email'])) {
            return 'Nome e email são obrigatórios';
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            return 'E-mail inválido';
        }

        return null;
    }

    /**
     * Monta o array com o conjunto de dados base exigidos pelo template Twig da página.
     * * Inclui os dados do próprio usuário, controle de permissões de visualização, URL da foto
     * institucional do parlamentar, link de convite e a lista de membros (se o usuário for admin).
     *
     * @param Usuario $usuario Instância do usuário logado.
     * @param array $extra Dados adicionais (como mensagens de feedback) para mesclar no retorno.
     * @return array Array estruturado pronto para consumo da view Twig.
     */
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

    /**
     * Retorna a URL oficial da foto do parlamentar de acordo com a casa legislativa (Câmara ou Senado).
     *
     * @param Usuario $usuario Instância do usuário contendo o relacionamento carregado do gabinete.
     * @return string|null A URL da imagem do parlamentar correspondente, ou null se o tipo for desconhecido.
     */
    private function buscarFotoParlamentar(Usuario $usuario): ?string {
        $tipoGabinete = $usuario->gabinete->tipo_gabinete_id;
        $idParlamentar = $usuario->gabinete->id_parlamentar;

        return match ($tipoGabinete) {
            1 => "https://www.camara.leg.br/internet/deputado/bandep/{$idParlamentar}.jpg",
            2 => "https://legis.senado.leg.br/senadores/fotos-oficiais/{$idParlamentar}",
            default => null,
        };
    }

    /**
     * Centraliza o tratamento de exceções geradas na classe, tentando recompor os dados base
     * do formulário antes de delegar a renderização do erro para o servidor.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param Exception $e A exceção capturada.
     * @return Response A resposta HTTP contendo a renderização amigável de erro.
     */
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

    /**
     * Facilita a chamada de renderização padrão do template associado ao controller de Perfil.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param array $data Dados a serem injetados na view.
     * @return Response A resposta HTTP com o template renderizado.
     */
    private function renderForm(Request $request, Response $response, array $data = []): Response {
        return $this->render($request, $response, self::VIEW, $data);
    }
}
