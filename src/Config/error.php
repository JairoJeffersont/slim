<?php

use Slim\Exception\HttpNotFoundException;

return function ($app, $twig) {

    $errorMiddleware = $app->addErrorMiddleware(true, true, true);

    $errorHandler = function ($request, $exception, $displayErrorDetails, $logErrors, $logErrorDetails) use ($twig) {

        $response = new \Slim\Psr7\Response();

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
