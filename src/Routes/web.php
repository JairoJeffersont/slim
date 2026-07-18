<?php

use App\Controllers\AniversarianteController;
use App\Controllers\AgendaController;
use App\Controllers\AgendaExternaController;
use App\Controllers\CadastroController;
use App\Controllers\CelulaController;
use App\Controllers\DashboardController;
use App\Controllers\DocumentoController;
use App\Controllers\FichaAgendaController;
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
use App\Controllers\SituacaoAgendaController;
use App\Controllers\TipoAgendaController;
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

    $app->get('/agendar/{token}', [AgendaExternaController::class, 'formulario']);
    $app->post('/agendar/{token}', [AgendaExternaController::class, 'salvar']);

    $app->group('', function ($group) {

        $group->get('/dashboard', [DashboardController::class, 'index']);
        $group->get('/', [DashboardController::class, 'index']);


        $group->get('/perfil', [PerfilController::class, 'index']);
        $group->post('/perfil', [PerfilController::class, 'updateUser']);

        $group->get('/gabinete', [GabineteController::class, 'index']);
        $group->post('/gabinete/atualizar', [GabineteController::class, 'updateGabinete']);

        $group->get('/usuario/{id:[0-9]+}', [UsuarioController::class, 'index']);
        $group->post('/usuario/{id:[0-9]+}', [UsuarioController::class, 'updateUser']);

        $group->get('/orgaos/tipos/inserir', [TipoOrgaoController::class, 'inserirTiposPadrao']);
        $group->get('/orgaos/tipos', [TipoOrgaoController::class, 'indexTiposOrgaos']);
        $group->post('/orgaos/tipos', [TipoOrgaoController::class, 'newTipoOrgaos']);
        $group->get('/orgaos/tipos/{id:[0-9]+}', [TipoOrgaoController::class, 'buscarTipoOrgao']);
        $group->post('/orgaos/tipos/{id:[0-9]+}', [TipoOrgaoController::class, 'updateTipoOrgaos']);
        $group->get('/orgaos/tipos/{id:[0-9]+}/apagar', [TipoOrgaoController::class, 'apagarTipoOrgao']);

        $group->get('/orgaos', [OrgaoController::class, 'indexOrgaos']);
        $group->post('/orgaos', [OrgaoController::class, 'newOrgao']);

        $group->get('/orgaos/{id:[0-9]+}', [FichaOrgaoController::class, 'index']);
        $group->post('/orgaos/{id:[0-9]+}', [FichaOrgaoController::class, 'updateOrgao']);
        $group->get('/orgaos/{id:[0-9]+}/apagar', [FichaOrgaoController::class, 'apagarOrgao']);

        $group->get('/pessoas/profissoes/inserir', [ProfissaoController::class, 'inserirProfissoesPadrao']);
        $group->get('/pessoas/profissoes', [ProfissaoController::class, 'indexProfissoes']);
        $group->post('/pessoas/profissoes', [ProfissaoController::class, 'newProfissao']);
        $group->get('/pessoas/profissoes/{id:[0-9]+}/apagar', [ProfissaoController::class, 'apagarProfissao']);
        $group->get('/pessoas/profissoes/{id:[0-9]+}', [ProfissaoController::class, 'obterProfissao']);
        $group->post('/pessoas/profissoes/{id:[0-9]+}', [ProfissaoController::class, 'updateProfissao']);

        $group->get('/pessoas', [PessoaController::class, 'indexPessoas']);
        $group->post('/pessoas', [PessoaController::class, 'newPessoa']);


        $group->get('/pessoas/{id:[0-9]+}', [FichaPessoaController::class, 'index']);
        $group->post('/pessoas/{id:[0-9]+}', [FichaPessoaController::class, 'updatePessoa']);
        $group->get('/pessoas/{id:[0-9]+}/apagar', [FichaPessoaController::class, 'apagarPessoa']);

        $group->post('/pessoas/{id:[0-9]+}/tornar-lider', [CelulaController::class, 'tornarLider']);
        $group->post('/pessoas/{id:[0-9]+}/remover-lider', [CelulaController::class, 'removerLider']);
        $group->get('/pessoas/{id:[0-9]+}/liderados', [CelulaController::class, 'listarLiderados']);
        $group->get('/pessoas/liderancas', [CelulaController::class, 'listarLideres']);

        $group->get('/aniversariantes', [AniversarianteController::class, 'index']);

        $group->get('/documentos/tipos', [TipoDocumentoController::class, 'indexTiposDocumentos']);
        $group->post('/documentos/tipos', [TipoDocumentoController::class, 'newTipoDocumentos']);
        $group->get('/documentos/tipos/{id:[0-9]+}', [TipoDocumentoController::class, 'buscarTipoDocumento']);
        $group->post('/documentos/tipos/{id:[0-9]+}/editar', [TipoDocumentoController::class, 'updateTipoDocumentos']);
        $group->get('/documentos/tipos/{id:[0-9]+}/deletar', [TipoDocumentoController::class, 'apagarTipoDocumento']);
        $group->get('/documentos/tipos/inserir/padrao', [TipoDocumentoController::class, 'inserirTiposPadrao']);

        $group->get('/documentos', [DocumentoController::class, 'indexDocumentos']);
        $group->post('/documentos', [DocumentoController::class, 'newDocumentos']);

        $group->get('/documentos/{id:[0-9]+}', [FichaDocumentoController::class, 'indexDocumento']);
        $group->post('/documentos/{id:[0-9]+}', [FichaDocumentoController::class, 'updateDocumentos']);
        $group->get('/documentos/{id:[0-9]+}/apagar', [FichaDocumentoController::class, 'apagarDocumento']);

        $group->get('/agenda/situacoes', [SituacaoAgendaController::class, 'indexSituacoesAgenda']);
        $group->post('/agenda/situacoes', [SituacaoAgendaController::class, 'newSituacao']);
        $group->get('/agenda/situacoes/{id:[0-9]+}', [SituacaoAgendaController::class, 'buscarSituacaoView']);
        $group->post('/agenda/situacoes/{id:[0-9]+}/editar', [SituacaoAgendaController::class, 'updateSituacao']);
        $group->get('/agenda/situacoes/{id:[0-9]+}/apagar', [SituacaoAgendaController::class, 'apagarSituacao']);
        $group->get('/agenda/situacoes/inserir/padrao', [SituacaoAgendaController::class, 'inserirSituacoesPadrao']);

        $group->get('/agenda', [AgendaController::class, 'indexAgenda']);
        $group->post('/agenda', [AgendaController::class, 'newAgenda']);
        $group->get('/agenda/imprimir', [AgendaController::class, 'imprimirAgenda']);
        $group->get('/agenda/{id:[0-9]+}', [FichaAgendaController::class, 'indexAgenda']);
        $group->post('/agenda/{id:[0-9]+}', [FichaAgendaController::class, 'updateAgenda']);
        $group->get('/agenda/{id:[0-9]+}/apagar', [FichaAgendaController::class, 'apagarAgenda']);

        $group->get('/agenda/tipos', [TipoAgendaController::class, 'indexTiposAgenda']);
        $group->post('/agenda/tipos', [TipoAgendaController::class, 'newTipoAgenda']);
        $group->get('/agenda/tipos/{id:[0-9]+}', [TipoAgendaController::class, 'buscarTipoAgenda']);
        $group->post('/agenda/tipos/{id:[0-9]+}/editar', [TipoAgendaController::class, 'updateTipoAgenda']);
        $group->get('/agenda/tipos/{id:[0-9]+}/apagar', [TipoAgendaController::class, 'apagarTipoAgenda']);
        $group->get('/agenda/tipos/inserir/padrao', [TipoAgendaController::class, 'inserirTiposPadrao']);


        $group->get('/logout', [LoginController::class, 'logout']);
    })->add(new AuthMiddleware());
};
