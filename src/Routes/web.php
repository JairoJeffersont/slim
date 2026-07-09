<?php

use App\Controllers\CadastroController;
use App\Middleware\AuthMiddleware;
use App\Controllers\LoginController;
use App\Controllers\PerfilController;
use App\Controllers\UsuarioController;
use Slim\App;
use Slim\Views\Twig;



return function (App $app) {
    $app->get('/login', [LoginController::class, 'index']);
    $app->post('/login', [LoginController::class, 'login']);

    $app->get('/cadastro', [CadastroController::class, 'index']);
    $app->post('/cadastro', [CadastroController::class, 'cadastro']);

    $app->get('/esqueci-senha', [UsuarioController::class, 'esqueciSenha']);
    $app->post('/esqueci-senha', [UsuarioController::class, 'enviarRecuperacaoSenha']);

    $app->get('/nova-senha/{token}', [UsuarioController::class, 'novaSenha']);
    $app->post('/nova-senha/{token}', [UsuarioController::class, 'salvarNovaSenha']);

    $app->group('', function ($group) {

        $group->get('/dashboard', function ($request, $response) {
            return Twig::fromRequest($request)->render($response, 'dashboard.twig');
        });

        $group->get('/meu-gabinete', [PerfilController::class, 'index']);
        $group->post('/meu-gabinete', [PerfilController::class, 'atualizar']);

        $group->get('/usuario/{id}', [UsuarioController::class, 'index']);
        $group->post('/usuario/{id}/tipo', [UsuarioController::class, 'atualizarTipo']);
        $group->post('/usuario/{id}/status', [UsuarioController::class, 'alterarStatus']);
        $group->post('/usuario/{id}/excluir', [UsuarioController::class, 'excluir']);
        $group->get('/novo-usuario/{token}', [UsuarioController::class, 'novoUsuario']);
        $group->post('/novo-usuario/{token}', [UsuarioController::class, 'salvarNovoUsuario']);


        $group->get('/logout', [LoginController::class, 'logout']);
    })->add(new AuthMiddleware());
};
