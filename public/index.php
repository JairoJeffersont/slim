<?php

use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

require __DIR__ . '/../src/Config/database.php';

$app = AppFactory::create();

$twig = require __DIR__ . '/../src/Config/twig.php';

$app->add(TwigMiddleware::create($app, $twig));

(require __DIR__ . '/../src/Routes/web.php')($app);

$app->run();
