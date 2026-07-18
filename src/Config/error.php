<?php

use Slim\Exception\HttpNotFoundException;
use App\Middleware\SessionManager;
use Slim\Psr7\Response;

return function ($app, $twig) {

    $errorMiddleware = $app->addErrorMiddleware(true, true, true);

    $errorHandler = function ($request, $exception, $displayErrorDetails, $logErrors, $logErrorDetails) use ($twig) {

        $session = new SessionManager();

        if (!$session->check()) {
            return (new Response())
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        $response = new Response();

        if ($exception instanceof HttpNotFoundException) {
            return $twig->render($response, 'errors/404.twig', [
                'title' => 'Página não encontrada'
            ]);
        }

        return $twig->render($response, 'errors/500.twig', [
            'title' => 'Erro interno'
        ]);
    };

    $errorMiddleware->setErrorHandler(HttpNotFoundException::class, $errorHandler);

    return $errorMiddleware;
};
