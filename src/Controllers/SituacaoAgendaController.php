<?php

namespace App\Controllers;

use App\Models\SituacaoAgenda;
use App\Models\TipoDocumento;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SituacaoAgendaController extends BaseController {
    private const VIEW_SITUACOES_AGENDA = 'pages/agenda/situacoes-agendas.twig';
    private const VIEW_SITUACOES_AGENDA_ROUTE = '/agenda/situacoes';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }


    private function listarSituacoes() {
        return SituacaoAgenda::with('usuario')
            ->where('gabinete_id', $this->usuario['gabinete_id'])
            ->orderBy('nome', 'asc')
            ->get();
    }

    private function buscarSituacao(int $id): ?SituacaoAgenda {
        return SituacaoAgenda::where([
            'id' => $id,
            'gabinete_id' => $this->usuario['gabinete_id']
        ])->first();
    }

    public function indexSituacoesAgenda(Request $request, Response $response): Response {
        try {
            $payload['situacoes'] = $this->listarSituacoes();
            $payload = array_merge($payload, $this->getFlash());

            return $this->renderView($request, $response, self::VIEW_SITUACOES_AGENDA, $payload);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_SITUACOES_AGENDA, $this->getFlash());
        }
    }

    public function newSituacao(Request $request, Response $response): Response {
        if (!in_array($this->usuario['nivel'], [1, 3])) {
            $this->flash('info', 'Você não tem autorização para criar novos tipos de documentos');
            return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
        }

        try {
            $dados = $request->getParsedBody();

            $busca = SituacaoAgenda::where([
                'nome' => $dados['nome'],
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->first();

            if ($busca) {
                $this->flash('info', 'Essa situação já está cadastrado');
                return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
            }

            SituacaoAgenda::create([
                'nome' => $dados['nome'],
                'gabinete_id' => $this->usuario['gabinete_id'],
                'usuario_id' => $this->usuario['id']
            ]);

            $this->flash('success', 'Situação cadastrada com sucesso');
            return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
        }
    }


    public function updateSituacao(Request $request, Response $response, array $args): Response {
        try {
            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para atualizar situações de agendas');
                return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
            }

            $id = (int) ($args['id'] ?? 0);
            $tipo = $this->buscarSituacao($id);

            if (!$tipo) {
                $this->flash('info', 'Situação não encontrada');
                return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
            }

            $dados = $request->getParsedBody();

            $busca = SituacaoAgenda::where('nome', $dados['nome'])
                ->where('gabinete_id', $this->usuario['gabinete_id'])
                ->where('id', '!=', $tipo->id)
                ->first();

            if ($busca) {
                $this->flash('info', 'Esse situação já está cadastrada com este nome');
                return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
            }

            $tipo->update([
                'nome' => $dados['nome']
            ]);

            $this->flash('success', 'Situação atualizada com sucesso');
            return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
        }
    }

    public function buscarSituacaoView(Request $request, Response $response, array $args): Response {
        try {
            $id = (int) ($args['id'] ?? 0);
            $tipo = $this->buscarSituacao($id);

            if (!$tipo) {
                $this->flash('info', 'Situação não encontrada');
                return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
            }

            $payload['situacoes'] = $this->listarSituacoes();
            $payload['situacao'] = $tipo;
            $payload = array_merge($payload, $this->getFlash());

            return $this->renderView($request, $response, self::VIEW_SITUACOES_AGENDA, $payload);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
        }
    }

    public function apagarSituacao(Request $request, Response $response, array $args): Response {
        try {
            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para apagar situações de agenda');
                return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
            }

            $id = (int) ($args['id'] ?? 0);
            $tipo = $this->buscarSituacao($id);

            if (!$tipo) {
                $this->flash('info', 'Situação não encontrada');
                return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
            }

            $tipo->delete();

            $this->flash('success', 'Situação apagada com sucesso');
            return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                $this->flash('error', 'Este situação não pode ser removida porque está vinculado a documentos existentes');
                return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
            }

            $this->flashError($e);
            return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
        }
    }


    public function inserirSituacoesPadrao(Request $request, Response $response, array $args): Response {
        try {
            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para inserir situeações padrão');
                return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
            }

            // Altere o caminho ou o arquivo JSON conforme sua nova estrutura de tipos de documentos padrão
            $json = file_get_contents(__DIR__ . '/../Json/situacoes_agenda.json');
            $situacoesAgenda = json_decode($json, true);

            foreach ($situacoesAgenda as $tipoDoc) {
                SituacaoAgenda::firstOrCreate(
                    [
                        'nome' => $tipoDoc['nome'],
                        'gabinete_id' => $this->usuario['gabinete_id']
                    ],
                    [
                        'usuario_id' => $this->usuario['id']
                    ]
                );
            }

            $this->flash('success', 'Situações de agenda padrão inseridos com sucesso');
            return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_SITUACOES_AGENDA_ROUTE);
        }
    }
}
