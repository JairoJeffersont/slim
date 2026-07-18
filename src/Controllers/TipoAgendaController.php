<?php

namespace App\Controllers;

use App\Models\TipoAgenda;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TipoAgendaController extends BaseController {
    private const VIEW_TIPOS_AGENDA = 'pages/agenda/tipos-agendas.twig';
    private const VIEW_TIPOS_AGENDA_ROUTE = '/agenda/tipos';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    private function listarTipos() {
        return TipoAgenda::with('usuario')
            ->where('gabinete_id', $this->usuario['gabinete_id'])
            ->orderBy('nome', 'asc')
            ->get();
    }

    private function buscarTipo(int $id): ?TipoAgenda {
        return TipoAgenda::where([
            'id' => $id,
            'gabinete_id' => $this->usuario['gabinete_id']
        ])->first();
    }

    public function indexTiposAgenda(Request $request, Response $response): Response {
        try {
            $payload['tipos'] = $this->listarTipos();
            $payload = array_merge($payload, $this->getFlash());

            return $this->renderView($request, $response, self::VIEW_TIPOS_AGENDA, $payload);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_TIPOS_AGENDA, $this->getFlash());
        }
    }

    public function newTipoAgenda(Request $request, Response $response): Response {
        if (!in_array($this->usuario['nivel'], [1, 3])) {
            $this->flash('info', 'Você não tem autorização para criar novos tipos de agenda');
            return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
        }

        try {
            $dados = $request->getParsedBody();

            $busca = TipoAgenda::where([
                'nome' => $dados['nome'],
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->first();

            if ($busca) {
                $this->flash('info', 'Esse tipo de agenda já está cadastrado');
                return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
            }

            TipoAgenda::create([
                'nome' => $dados['nome'],
                'gabinete_id' => $this->usuario['gabinete_id'],
                'usuario_id' => $this->usuario['id']
            ]);

            $this->flash('success', 'Tipo de agenda cadastrado com sucesso');
            return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
        }
    }

    public function updateTipoAgenda(Request $request, Response $response, array $args): Response {
        try {
            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para atualizar tipos de agenda');
                return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
            }

            $id = (int) ($args['id'] ?? 0);
            $tipo = $this->buscarTipo($id);

            if (!$tipo) {
                $this->flash('info', 'Tipo de agenda não encontrado');
                return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
            }

            $dados = $request->getParsedBody();

            $busca = TipoAgenda::where('nome', $dados['nome'])
                ->where('gabinete_id', $this->usuario['gabinete_id'])
                ->where('id', '!=', $tipo->id)
                ->first();

            if ($busca) {
                $this->flash('info', 'Esse tipo de agenda já está cadastrado com este nome');
                return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
            }

            $tipo->update([
                'nome' => $dados['nome']
            ]);

            $this->flash('success', 'Tipo de agenda atualizado com sucesso');
            return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
        }
    }

    public function buscarTipoAgenda(Request $request, Response $response, array $args): Response {
        try {
            $id = (int) ($args['id'] ?? 0);
            $tipo = $this->buscarTipo($id);

            if (!$tipo) {
                $this->flash('info', 'Tipo de agenda não encontrado');
                return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
            }

            $payload['tipos'] = $this->listarTipos();
            $payload['tipo'] = $tipo;
            $payload = array_merge($payload, $this->getFlash());

            return $this->renderView($request, $response, self::VIEW_TIPOS_AGENDA, $payload);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
        }
    }

    public function apagarTipoAgenda(Request $request, Response $response, array $args): Response {
        try {
            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para apagar tipos de agenda');
                return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
            }

            $id = (int) ($args['id'] ?? 0);
            $tipo = $this->buscarTipo($id);

            if (!$tipo) {
                $this->flash('info', 'Tipo de agenda não encontrado');
                return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
            }

            $tipo->delete();

            $this->flash('success', 'Tipo de agenda apagado com sucesso');
            return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                $this->flash('error', 'Este tipo não pode ser removido porque está vinculado a compromissos existentes');
                return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
            }

            $this->flashError($e);
            return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
        }
    }

    public function inserirTiposPadrao(Request $request, Response $response, array $args): Response {
        try {
            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para inserir tipos padrão');
                return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
            }

            $json = file_get_contents(__DIR__ . '/../Json/tipos_agenda.json');
            $tiposAgenda = json_decode($json, true);

            foreach ($tiposAgenda as $tipoAgenda) {
                TipoAgenda::firstOrCreate(
                    [
                        'nome' => $tipoAgenda['nome'],
                        'gabinete_id' => $this->usuario['gabinete_id']
                    ],
                    [
                        'usuario_id' => $this->usuario['id']
                    ]
                );
            }

            $this->flash('success', 'Tipos de agenda padrão inseridos com sucesso');
            return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_TIPOS_AGENDA_ROUTE);
        }
    }
}
