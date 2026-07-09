<?php

use Slim\Views\Twig;
use App\Middleware\SessionManager;

$cache = filter_var($_ENV['TWIG_CACHE'] ?? false, FILTER_VALIDATE_BOOLEAN);

$twig = Twig::create(__DIR__ . '/../Views', [
    'cache' => $cache ? __DIR__ . '/../../storage/cache/twig' : false,
    'debug' => !$cache
]);

$twig->getEnvironment()->addGlobal('app_name', $_ENV['APP_NAME'] ?? '');
$twig->getEnvironment()->addGlobal('app_slogan', $_ENV['APP_SLOGAN'] ?? '');

$session = new SessionManager();

$twig->getEnvironment()->addGlobal('auth', [
    'user' => $session->user()
]);

return $twig;
