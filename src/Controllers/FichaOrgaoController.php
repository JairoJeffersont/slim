<?php

namespace App\Controllers;

use App\Models\Orgao;
use App\Models\TipoOrgao;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FichaOrgaoController extends BaseController {
    private const VIEW_FICHA_ORGAOS = 'pages/orgaos/ficha_orgao.twig';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    private function listarTipos() {
        return TipoOrgao::with('usuario')->where('gabinete_id', $this->usuario['gabinete_id'])->orderBy('nome')->get();
    }

    private function buscarOrgao(int $id): ?Orgao {
        return Orgao::with('tipoOrgao')
            ->where([
                'id' => $id,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])
            ->first();
    }

    public function index(Request $request, Response $response, array $args): Response {
        try {
            $id = (int)$args['id'];

            $orgao = $this->buscarOrgao($id);

            if (!$orgao) {
                $this->flash('info', 'Órgão/entidade não encontrado');
                return $this->redirect($response, '/orgaos');
            }

            $payload['tipos'] = $this->listarTipos();
            $payload['orgao'] = $orgao;

            return $this->renderView($request, $response, self::VIEW_FICHA_ORGAOS, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_FICHA_ORGAOS, $this->getFlash());
        }
    }

    public function updateOrgao(Request $request, Response $response, array $args): Response {
        $id = (int)$args['id'];

        if (!in_array($this->usuario['nivel'], [1, 3])) {
            $this->flash('info', 'Você não tem autorização para atualizar um órgão');
            return $this->redirect($response, '/orgaos/' . $id);
        }

        try {
            $orgao = $this->buscarOrgao($id);

            if (!$orgao) {
                $this->flash('info', 'Órgão/entidade não encontrado');
                return $this->redirect($response, '/orgaos');
            }

            $dados = $request->getParsedBody();

            $orgao->update([
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'telefone' => $dados['telefone'],
                'endereco' => $dados['endereco'],
                'bairro' => $dados['bairro'],
                'cidade' => $dados['cidade'],
                'informacoes' => $dados['informacoes'],
                'estado' => $dados['estado'],
                'tipo_orgao_id' => $dados['tipo_orgao_id'] ?: null
            ]);

            $this->flash('success', 'Órgão/entidade atualizado com sucesso');

            return $this->redirect($response, '/orgaos/' . $id);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, '/orgaos/' . $id);
        }
    }

    public function apagarOrgao(Request $request, Response $response, array $args): Response {

        $id = $args['id'];

        try {

            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para apagar');
                return $this->redirect($response, '/orgaos/' . $id);
            }

            $orgao = $this->buscarOrgao($id);

            if (!$orgao) {
                $this->flash('info', 'Órgão/entidade não encontrado');
                return $this->redirect($response, '/orgaos');
            }

            $orgao->delete();

            $this->flash('success', 'Órgão/entidade apagado com sucesso');

            return $this->redirect($response, '/orgaos');
        } catch (Exception $e) {

            if (str_contains(strtolower($e->getMessage()), 'foreign key constraint fails')) {
                $this->flash('error', 'Este órgão não pode ser removido porque está vinculado a outros registros');
                return $this->redirect($response, '/orgaos/' . $id);
            }

            $this->flashError($e);

            return $this->redirect($response, '/orgaos/' . $id);
        }
    }
}
