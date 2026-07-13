<?php

use App\Controllers\CadastroController;
use App\Controllers\DashboardController;
use App\Controllers\GabineteController;
use App\Controllers\LoginController;
use App\Controllers\PasswordController;
use App\Controllers\PerfilController;
use App\Controllers\UsuarioController;
use App\Middleware\AuthMiddleware;
use Slim\App;



return function (App $app) {
    $app->get('/cadastro', [CadastroController::class, 'index']);
    $app->post('/cadastro', [CadastroController::class, 'cadastro']);

    $app->get('/login', [LoginController::class, 'index']);
    $app->post('/login', [LoginController::class, 'login']);

    $app->get('/esqueci-senha', [PasswordController::class, 'formRecover']);
    $app->post('/esqueci-senha', [PasswordController::class, 'recover']);

    $app->get('/nova-senha/{token}', [PasswordController::class, 'formNewPass']);
    $app->post('/nova-senha/{token}', [PasswordController::class, 'newPass']);

    $app->get('/novo-usuario/{token}', [UsuarioController::class, 'formNewUser']);
    $app->post('/novo-usuario/{token}', [UsuarioController::class, 'newUser']);

    $app->group('', function ($group) {

        $group->get('/dashboard', [DashboardController::class, 'index']);

        $group->get('/perfil', [PerfilController::class, 'index']);
        $group->post('/perfil', [PerfilController::class, 'updateUser']);

        $group->get('/gabinete', [GabineteController::class, 'index']);
        $group->post('/gabinete/atualizar', [GabineteController::class, 'updateGabinete']);

        $group->get('/usuario/{id}', [UsuarioController::class, 'index']);
        $group->post('/usuario/{id}', [UsuarioController::class, 'updateUser']);





        $group->get('/logout', [LoginController::class, 'logout']);
    })->add(new AuthMiddleware());
};
