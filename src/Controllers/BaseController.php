<?php

namespace App\Controllers;

use Exception;
use JairoJeffersont\EasyLogger\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

abstract class BaseController {
    protected function flash(string $status, string $message, ?string $errorId = null): void {
        $_SESSION['flash'] = [
            'status' => $status,
            'message' => $message,
            'error_id' => $errorId
        ];
    }

    protected function getFlash(): array {
        if (!isset($_SESSION['flash'])) {
            return [];
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }

    protected function redirect(Response $response, string $url): Response {
        return $response->withHeader('Location', $url)->withStatus(302);
    }

    protected function renderView(Request $request, Response $response, string $view, array $data = []): Response {
        return Twig::fromRequest($request)->render($response, $view, $data);
    }

    protected function logError(Exception $e): string {
        return Logger::newLog('../logs', 'ERROR', static::class . ' | ' . $e->getMessage(), 'ERROR');
    }

    protected function errorResponse(Exception $e): array {
        return ['status' => 'error', 'message' => 'Erro interno do servidor', 'error_id' => $this->logError($e)];
    }

    protected function flashError(Exception $e): void {
        $error = $this->errorResponse($e);

        $this->flash(
            $error['status'],
            $error['message'],
            $error['error_id']
        );
    }
}
