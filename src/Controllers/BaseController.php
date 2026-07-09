<?php

namespace App\Controllers;

use Exception;
use JairoJeffersont\EasyLogger\Logger;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class BaseController {
    protected function render(Request $request, Response $response, string $view, array $data = []): Response {
        return Twig::fromRequest($request)->render($response, $view, $data);
    }

    protected function redirect(Response $response, string $path, int $status = 302): Response {
        return $response->withHeader('Location', $path)->withStatus($status);
    }

    protected function input(Request $request): array {
        return $request->getParsedBody() ?? [];
    }

    protected function user(): ?array {
        return $_SESSION['usuario'] ?? null;
    }

    protected function isAdmin(): bool {
        return ($this->user()['nivel'] ?? null) === 1;
    }

    protected function info(string $message, array $extra = []): array {
        return array_merge([
            'status' => 'info',
            'message' => $message
        ], $extra);
    }

    protected function success(string $message, array $extra = []): array {
        return array_merge([
            'status' => 'success',
            'message' => $message
        ], $extra);
    }

    protected function logError(string $context, Exception $e): string {
        return Logger::newLog('../logs', $context, $e->getMessage(), 'ERROR');
    }

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
