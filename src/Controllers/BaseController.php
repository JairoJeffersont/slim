<?php

namespace App\Controllers;

use Exception;
use JairoJeffersont\EasyLogger\Logger;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Classe Abstrata BaseController
 * * Fornece métodos utilitários compartilhados para manipulação de requisições,
 * renderização de views, redirecionamentos, autenticação e tratamento de logs.
 * * @package App\Controllers
 */
abstract class BaseController {

    /**
     * Renderiza uma view utilizando o mecanismo de templates Twig.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param string $view O caminho/nome do arquivo de template do Twig.
     * @param array $data Dados opcionais a serem passados para a view.
     * @return Response A resposta HTTP com o conteúdo renderizado.
     */
    protected function render(Request $request, Response $response, string $view, array $data = []): Response {
        return Twig::fromRequest($request)->render($response, $view, $data);
    }

    /**
     * Executa um redirecionamento HTTP para o caminho especificado.
     *
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param string $path O destino do redirecionamento (URL ou rota).
     * @param int $status O código de status HTTP (padrão: 302 Found).
     * @return Response A resposta HTTP configurada para o redirecionamento.
     */
    protected function redirect(Response $response, string $path, int $status = 302): Response {
        return $response->withHeader('Location', $path)->withStatus($status);
    }

    /**
     * Recupera os dados do corpo da requisição (geralmente de formulários POST ou JSON).
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @return array Os dados decodificados do corpo da requisição.
     */
    protected function input(Request $request): array {
        return $request->getParsedBody() ?? [];
    }

    /**
     * Obtém os dados do usuário atualmente autenticado na sessão.
     *
     * @return array|null Retorna o array de dados do usuário ou null se não estiver autenticado.
     */
    protected function user(): ?array {
        return $_SESSION['usuario'] ?? null;
    }

    /**
     * Verifica se o usuário autenticado possui privilégios de administrador.
     *
     * @return bool True se for administrador (nível igual a 1), false caso contrário.
     */
    protected function isAdmin(): bool {
        return ($this->user()['nivel'] ?? null) === 1;
    }

    /**
     * Formata uma estrutura de dados padrão para mensagens informativas.
     *
     * @param string $message A mensagem de informação.
     * @param array $extra Dados adicionais para mesclar ao retorno.
     * @return array Array formatado com 'status', 'message' e dados extras.
     */
    protected function info(string $message, array $extra = []): array {
        return array_merge([
            'status' => 'info',
            'message' => $message
        ], $extra);
    }

    /**
     * Formata uma estrutura de dados padrão para mensagens de sucesso.
     *
     * @param string $message A mensagem de sucesso.
     * @param array $extra Dados adicionais para mesclar ao retorno.
     * @return array Array formatado com 'status', 'message' e dados extras.
     */
    protected function success(string $message, array $extra = []): array {
        return array_merge([
            'status' => 'success',
            'message' => $message
        ], $extra);
    }

    /**
     * Registra uma exceção nos arquivos de log do sistema.
     *
     * @param string $context O contexto ou identificador do erro (ex: 'AUTH_ERROR').
     * @param Exception $e A exceção capturada.
     * @return string O identificador único do log gerado.
     */
    protected function logError(string $context, Exception $e): string {
        return Logger::newLog('../logs', $context, $e->getMessage(), 'ERROR');
    }

    /**
     * Captura uma exceção, gera o log de erro e renderiza uma view amigável de erro 500.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param string $view O caminho do template de erro a ser renderizado.
     * @param Exception $e A exceção que disparou o erro.
     * @param string $context O contexto do erro para o arquivo de log (padrão: 'SERVER_ERROR').
     * @param array $extra Dados adicionais a serem passados para a view de erro.
     * @return Response A resposta HTTP com a view de erro renderizada.
     */
    protected function renderServerError(
        Request $request,
        Response $response,
        string $view,
        Exception $e,
        string $context = 'SERVER_ERROR',
        array $extra = []
    ): Response {
        $errorId = $this->logError($context, $e);

        return $this->render($request, $response, $view, array_merge([
            'status' => 'server_error',
            'message' => 'Erro interno do servidor',
            'error_id' => $errorId
        ], $extra));
    }
}
