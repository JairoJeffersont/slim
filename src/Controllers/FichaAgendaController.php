<?php

namespace App\Controllers;

use App\Models\Agenda;
use App\Models\Pessoa;
use App\Models\SituacaoAgenda;
use App\Models\TipoAgenda;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FichaAgendaController extends BaseController {
    private const VIEW_FICHA_AGENDA = 'pages/agenda/ficha_agenda.twig';
    private const VIEW_ROUTE = '/agenda';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    private function listarTipos() {
        return TipoAgenda::where('gabinete_id', $this->usuario['gabinete_id'])
            ->orderBy('nome', 'asc')
            ->get();
    }

    private function listarSituacoes() {
        return SituacaoAgenda::where('gabinete_id', $this->usuario['gabinete_id'])
            ->orderBy('nome', 'asc')
            ->get();
    }

    private function getFiltrosPessoas(array $params): array {
        return [
            'estado' => isset($params['pessoa_estado']) ? ($params['pessoa_estado'] ?: null) : ($this->usuario['gabinete_estado'] ?? null),
            'cidade' => isset($params['pessoa_cidade']) ? ($params['pessoa_cidade'] ?: null) : null
        ];
    }

    private function listarPessoas(array $filtros, ?int $pessoaSelecionadaId = null) {
        $carregarPessoas = !empty($filtros['cidade']);

        $pessoas = $carregarPessoas
            ? Pessoa::where('gabinete_id', $this->usuario['gabinete_id'])
                ->when($filtros['estado'], fn($query) => $query->where('estado', $filtros['estado']))
                ->when($filtros['cidade'], fn($query) => $query->where('cidade', $filtros['cidade']))
                ->orderBy('nome', 'asc')
                ->get()
            : Pessoa::whereRaw('1 = 0')->get();

        if ($pessoaSelecionadaId) {
            $pessoaSelecionada = $this->buscarPessoa($pessoaSelecionadaId);

            if ($pessoaSelecionada && !$pessoas->contains('id', $pessoaSelecionada->id)) {
                $pessoas->push($pessoaSelecionada);
                $pessoas = $pessoas->sortBy('nome')->values();
            }
        }

        return $pessoas;
    }

    private function buscarAgenda(int $id): ?Agenda {
        return Agenda::with(['tipoAgenda', 'situacaoAgenda', 'pessoa', 'usuario'])
            ->where([
                'id' => $id,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])
            ->first();
    }

    private function buscarTipo(int $id): ?TipoAgenda {
        return TipoAgenda::where([
            'id' => $id,
            'gabinete_id' => $this->usuario['gabinete_id']
        ])->first();
    }

    private function buscarSituacao(int $id): ?SituacaoAgenda {
        return SituacaoAgenda::where([
            'id' => $id,
            'gabinete_id' => $this->usuario['gabinete_id']
        ])->first();
    }

    private function buscarPessoa(?int $id): ?Pessoa {
        if (!$id) {
            return null;
        }

        return Pessoa::where([
            'id' => $id,
            'gabinete_id' => $this->usuario['gabinete_id']
        ])->first();
    }

    private function normalizarDataHora(?string $valor): ?string {
        if (!$valor) {
            return null;
        }

        $valor = trim($valor);

        if ($valor === '') {
            return null;
        }

        return str_replace('T', ' ', $valor) . ':00';
    }

    public function indexAgenda(Request $request, Response $response, array $args): Response {
        try {
            $id = (int) ($args['id'] ?? 0);
            $agenda = $this->buscarAgenda($id);
            $filtrosPessoas = $this->getFiltrosPessoas($request->getQueryParams());

            if (!$agenda) {
                $this->flash('info', 'Compromisso não encontrado');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            $payload['agenda'] = $agenda;
            $payload['tipos'] = $this->listarTipos();
            $payload['situacoes'] = $this->listarSituacoes();
            $payload['pessoas'] = $this->listarPessoas($filtrosPessoas, $agenda->pessoa_id);
            $payload['parametros_pessoas'] = $filtrosPessoas;

            return $this->renderView($request, $response, self::VIEW_FICHA_AGENDA, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE);
        }
    }

    public function updateAgenda(Request $request, Response $response, array $args): Response {
        $id = (int) ($args['id'] ?? 0);

        try {
            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para editar um compromisso');
                return $this->redirect($response, self::VIEW_ROUTE . '/' . $id);
            }

            $agenda = $this->buscarAgenda($id);
            if (!$agenda) {
                $this->flash('info', 'Compromisso não encontrado');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            $dados = $request->getParsedBody();

            $tipo = $this->buscarTipo((int) ($dados['tipo_agenda_id'] ?? 0));
            if (!$tipo) {
                $this->flash('info', 'Tipo de agenda não encontrado');
                return $this->redirect($response, self::VIEW_ROUTE . '/' . $agenda->id);
            }

            $situacao = $this->buscarSituacao((int) ($dados['situacao_agenda_id'] ?? 0));
            if (!$situacao) {
                $this->flash('info', 'Situação de agenda não encontrada');
                return $this->redirect($response, self::VIEW_ROUTE . '/' . $agenda->id);
            }

            $pessoaId = !empty($dados['pessoa_id']) ? (int) $dados['pessoa_id'] : null;
            $pessoa = $this->buscarPessoa($pessoaId);
            if ($pessoaId && !$pessoa) {
                $this->flash('info', 'Pessoa vinculada não encontrada');
                return $this->redirect($response, self::VIEW_ROUTE . '/' . $agenda->id);
            }

            $dataHora = $this->normalizarDataHora($dados['data_hora'] ?? null);
            $dataHoraFim = $this->normalizarDataHora($dados['data_hora_fim'] ?? null);

            if (!$dataHora) {
                $this->flash('info', 'Informe a data e hora do compromisso');
                return $this->redirect($response, self::VIEW_ROUTE . '/' . $agenda->id);
            }

            if ($dataHoraFim && strtotime($dataHoraFim) < strtotime($dataHora)) {
                $this->flash('info', 'A data final não pode ser menor que a data inicial');
                return $this->redirect($response, self::VIEW_ROUTE . '/' . $agenda->id);
            }

            $agenda->update([
                'tipo_agenda_id' => $tipo->id,
                'situacao_agenda_id' => $situacao->id,
                'pessoa_id' => $pessoa?->id,
                'titulo' => trim($dados['titulo']),
                'descricao' => trim($dados['descricao'] ?? '') ?: null,
                'local' => trim($dados['local'] ?? '') ?: 'Não informado',
                'data_hora' => $dataHora,
                'data_hora_fim' => $dataHoraFim
            ]);

            $this->flash('success', 'Compromisso atualizado com sucesso');
            return $this->redirect($response, self::VIEW_ROUTE . '/' . $agenda->id);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE . '/' . $id);
        }
    }

    public function apagarAgenda(Request $request, Response $response, array $args): Response {
        $id = (int) ($args['id'] ?? 0);

        try {
            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para apagar');
                return $this->redirect($response, self::VIEW_ROUTE . '/' . $id);
            }

            $agenda = $this->buscarAgenda($id);
            if (!$agenda) {
                $this->flash('info', 'Compromisso não encontrado');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            $agenda->delete();

            $this->flash('success', 'Compromisso apagado com sucesso');
            return $this->redirect($response, self::VIEW_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE . '/' . $id);
        }
    }
}
