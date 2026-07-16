<?php

use App\Controllers\AniversarianteController;
use App\Controllers\CadastroController;
use App\Controllers\CelulaController;
use App\Controllers\DashboardController;
use App\Controllers\DocumentoController;
use App\Controllers\FichaDocumentoController;
use App\Controllers\FichaOrgaoController;
use App\Controllers\FichaPessoaController;
use App\Controllers\GabineteController;
use App\Controllers\LoginController;
use App\Controllers\OrgaoController;
use App\Controllers\TipoOrgaoController;
use App\Controllers\PasswordController;
use App\Controllers\PerfilController;
use App\Controllers\PessoaController;
use App\Controllers\ProfissaoController;
use App\Controllers\TipoDocumentoController;
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

    $app->get('/convite', [CelulaController::class, 'cadastroConvidado']);
    $app->post('/convite/salvar', [CelulaController::class, 'salvarConvidado']);

    $app->group('', function ($group) {

        $group->get('/dashboard', [DashboardController::class, 'index']);

        $group->get('/perfil', [PerfilController::class, 'index']);
        $group->post('/perfil', [PerfilController::class, 'updateUser']);

        $group->get('/gabinete', [GabineteController::class, 'index']);
        $group->post('/gabinete/atualizar', [GabineteController::class, 'updateGabinete']);

        $group->get('/usuario/{id}', [UsuarioController::class, 'index']);
        $group->post('/usuario/{id}', [UsuarioController::class, 'updateUser']);

        $group->get('/orgaos/tipos/inserir', [TipoOrgaoController::class, 'inserirTiposPadrao']);
        $group->get('/orgaos/tipos', [TipoOrgaoController::class, 'indexTiposOrgaos']);
        $group->post('/orgaos/tipos', [TipoOrgaoController::class, 'newTipoOrgaos']);
        $group->get('/orgaos/tipos/{id}', [TipoOrgaoController::class, 'buscarTipoOrgao']);
        $group->post('/orgaos/tipos/{id}', [TipoOrgaoController::class, 'updateTipoOrgaos']);
        $group->get('/orgaos/tipos/{id}/apagar', [TipoOrgaoController::class, 'apagarTipoOrgao']);

        $group->get('/orgaos', [OrgaoController::class, 'indexOrgaos']);
        $group->post('/orgaos', [OrgaoController::class, 'newOrgao']);

        $group->get('/orgaos/{id}', [FichaOrgaoController::class, 'index']);
        $group->post('/orgaos/{id}', [FichaOrgaoController::class, 'updateOrgao']);
        $group->get('/orgaos/{id}/apagar', [FichaOrgaoController::class, 'apagarOrgao']);

        $group->get('/pessoas/profissoes/inserir', [ProfissaoController::class, 'inserirProfissoesPadrao']);
        $group->get('/pessoas/profissoes', [ProfissaoController::class, 'indexProfissoes']);
        $group->post('/pessoas/profissoes', [ProfissaoController::class, 'newProfissao']);
        $group->get('/pessoas/profissoes/{id}/apagar', [ProfissaoController::class, 'apagarProfissao']);
        $group->get('/pessoas/profissoes/{id}', [ProfissaoController::class, 'obterProfissao']);
        $group->post('/pessoas/profissoes/{id}', [ProfissaoController::class, 'updateProfissao']);

        $group->get('/pessoas', [PessoaController::class, 'indexPessoas']);
        $group->post('/pessoas', [PessoaController::class, 'newPessoa']);

        $group->get('/pessoas/{id:[0-9]+}', [FichaPessoaController::class, 'index']);
        $group->post('/pessoas/{id:[0-9]+}', [FichaPessoaController::class, 'updatePessoa']);
        $group->get('/pessoas/{id:[0-9]+}/apagar', [FichaPessoaController::class, 'apagarPessoa']);

        $group->post('/pessoas/{id}/tornar-lider', [CelulaController::class, 'tornarLider']);
        $group->post('/pessoas/{id}/remover-lider', [CelulaController::class, 'removerLider']);
        $group->get('/pessoas/{id}/liderados', [CelulaController::class, 'listarLiderados']);
        $group->get('/pessoas/liderancas', [CelulaController::class, 'listarLideres']);

        $group->get('/aniversariantes', [AniversarianteController::class, 'index']);

        $group->get('/documentos/tipos', [TipoDocumentoController::class, 'indexTiposDocumentos']);
        $group->post('/documentos/tipos', [TipoDocumentoController::class, 'newTipoDocumentos']);
        $group->get('/documentos/tipos/{id}', [TipoDocumentoController::class, 'buscarTipoDocumento']);
        $group->post('/documentos/tipos/{id}/editar', [TipoDocumentoController::class, 'updateTipoDocumentos']);
        $group->get('/documentos/tipos/{id}/deletar', [TipoDocumentoController::class, 'apagarTipoDocumento']);
        $group->get('/documentos/tipos/inserir/padrao', [TipoDocumentoController::class, 'inserirTiposPadrao']);

        $group->get('/documentos', [DocumentoController::class, 'indexDocumentos']);
        $group->post('/documentos', [DocumentoController::class, 'newDocumentos']);

        $group->get('/documentos/{id}', [FichaDocumentoController::class, 'indexDocumento']);
        $group->post('/documentos/{id}', [FichaDocumentoController::class, 'updateDocumentos']);
        $group->get('/documentos/{id}/apagar', [FichaDocumentoController::class, 'apagarDocumento']);





        $group->get('/logout', [LoginController::class, 'logout']);
    })->add(new AuthMiddleware());
};
