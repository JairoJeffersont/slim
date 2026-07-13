<?php

namespace App\Controllers;

use App\Models\TipoOrgao;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TipoOrgaoController extends BaseController {
    private const VIEW_TIPOS_ORGAOS = 'pages/orgaos/tipos-orgaos.twig';
    private const VIEW_TIPOS_ROUTE = '/orgaos/tipos';

    private int $LOGGED_USER_ID;
    private int $LOGGED_GABINETE;

    public function __construct() {
        $this->LOGGED_USER_ID = $_SESSION['usuario']['id'];
        $this->LOGGED_GABINETE = $_SESSION['usuario']['gabinete_id'];
    }

    private function listarTipos() {
        return TipoOrgao::with('usuario')
            ->where('gabinete_id', $this->LOGGED_GABINETE)
            ->get();
    }

    private function buscarTipo(int $id): ?TipoOrgao {
        return TipoOrgao::where([
            'id' => $id,
            'gabinete_id' => $this->LOGGED_GABINETE
        ])->first();
    }

    public function indexTiposOrgaos(Request $request, Response $response): Response {
        try {
            $payload['tipos'] = $this->listarTipos();
            $payload = array_merge($payload, $this->getFlash());

            return $this->renderView($request, $response, self::VIEW_TIPOS_ORGAOS, $payload);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_TIPOS_ORGAOS, $this->getFlash());
        }
    }

    public function newTipoOrgaos(Request $request, Response $response): Response {
        try {
            $dados = $request->getParsedBody();

            $busca = TipoOrgao::where([
                'nome' => $dados['nome'],
                'gabinete_id' => $this->LOGGED_GABINETE
            ])->first();

            if ($busca) {
                $this->flash('info', 'Esse tipo já está cadastrado');
                return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
            }

            TipoOrgao::create([
                'nome' => $dados['nome'],
                'gabinete_id' => $this->LOGGED_GABINETE,
                'usuario_id' => $this->LOGGED_USER_ID
            ]);

            $this->flash('success', 'Tipo cadastrado com sucesso');

            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        }
    }

    public function updateTipoOrgaos(Request $request, Response $response, array $args): Response {
        try {
            $id = (int) ($args['id'] ?? 0);

            $tipo = $this->buscarTipo($id);

            if (!$tipo) {
                $this->flash('info', 'Tipo não encontrado');
                return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
            }

            $dados = $request->getParsedBody();

            $busca = TipoOrgao::where('nome', $dados['nome'])
                ->where('gabinete_id', $this->LOGGED_GABINETE)
                ->where('id', '!=', $tipo->id)
                ->first();

            if ($busca) {
                $this->flash('info', 'Esse tipo já está cadastrado');
                return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
            }

            $tipo->update([
                'nome' => $dados['nome']
            ]);

            $this->flash('success', 'Tipo atualizado com sucesso');

            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        }
    }

    public function buscarTipoOrgao(Request $request, Response $response, array $args): Response {
        try {
            $id = (int) ($args['id'] ?? 0);

            $tipo = $this->buscarTipo($id);

            if (!$tipo) {
                $this->flash('info', 'Tipo de órgão não encontrado');
                return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
            }

            $payload['tipos'] = $this->listarTipos();
            $payload['tipo'] = $tipo;
            $payload = array_merge($payload, $this->getFlash());

            return $this->renderView($request, $response, self::VIEW_TIPOS_ORGAOS, $payload);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        }
    }

    public function apagarTipoOrgao(Request $request, Response $response, array $args): Response {
        try {
            $id = (int) ($args['id'] ?? 0);

            $tipo = $this->buscarTipo($id);

            if (!$tipo) {
                $this->flash('info', 'Tipo de órgão não encontrado');
                return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
            }

            $tipo->delete();

            $this->flash('success', 'Tipo de órgão apagado com sucesso');
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        }
    }
}
