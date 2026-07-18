<?php

namespace App\Controllers;

use App\Models\Agenda;
use App\Models\Pessoa;
use App\Models\SituacaoAgenda;
use App\Models\TipoAgenda;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AgendaController extends BaseController {
    private const VIEW_AGENDA = 'pages/agenda/agenda.twig';
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

    private function listarAgendas(?int $tipo = null, ?int $situacao = null, ?string $busca = null) {
        return Agenda::with(['tipoAgenda', 'situacaoAgenda', 'pessoa', 'usuario'])
            ->where('gabinete_id', $this->usuario['gabinete_id'])
            ->when($tipo !== null, function ($query) use ($tipo) {
                return $query->where('tipo_agenda_id', $tipo);
            })
            ->when($situacao !== null, function ($query) use ($situacao) {
                return $query->where('situacao_agenda_id', $situacao);
            })
            ->when(!empty($busca), function ($query) use ($busca) {
                return $query->where(function ($q) use ($busca) {
                    $q->where('titulo', 'like', "%{$busca}%")
                        ->orWhere('descricao', 'like', "%{$busca}%")
                        ->orWhere('local', 'like', "%{$busca}%")
                        ->orWhereHas('pessoa', function ($pessoaQuery) use ($busca) {
                            $pessoaQuery->where('nome', 'like', "%{$busca}%");
                        });
                });
            })
            ->orderBy('data_hora', 'asc')
            ->get();
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

    public function indexAgenda(Request $request, Response $response): Response {
        try {
            $query = $request->getQueryParams();
            $filtrosPessoas = $this->getFiltrosPessoas($query);

            $tipo = isset($query['tipo']) && trim($query['tipo']) !== '' ? (int) $query['tipo'] : null;
            $situacao = isset($query['situacao']) && trim($query['situacao']) !== '' ? (int) $query['situacao'] : null;
            $busca = isset($query['busca']) && trim($query['busca']) !== '' ? trim($query['busca']) : null;

            $payload['tipos'] = $this->listarTipos();
            $payload['situacoes'] = $this->listarSituacoes();
            $payload['pessoas'] = $this->listarPessoas($filtrosPessoas);
            $payload['agendas'] = $this->listarAgendas($tipo, $situacao, $busca);
            $payload['tipoGet'] = $tipo;
            $payload['situacaoGet'] = $situacao;
            $payload['busca'] = $busca;
            $payload['parametros_pessoas'] = $filtrosPessoas;

            return $this->renderView($request, $response, self::VIEW_AGENDA, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_AGENDA, $this->getFlash());
        }
    }

    public function newAgenda(Request $request, Response $response): Response {
        if (!in_array($this->usuario['nivel'], [1, 3])) {
            $this->flash('info', 'Você não tem autorização para inserir um compromisso');
            return $this->redirect($response, self::VIEW_ROUTE);
        }

        try {
            $dados = $request->getParsedBody();

            $tipo = $this->buscarTipo((int) ($dados['tipo_agenda_id'] ?? 0));
            if (!$tipo) {
                $this->flash('info', 'Tipo de agenda não encontrado');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            $situacao = $this->buscarSituacao((int) ($dados['situacao_agenda_id'] ?? 0));
            if (!$situacao) {
                $this->flash('info', 'Situação de agenda não encontrada');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            $pessoaId = !empty($dados['pessoa_id']) ? (int) $dados['pessoa_id'] : null;
            $pessoa = $this->buscarPessoa($pessoaId);
            if ($pessoaId && !$pessoa) {
                $this->flash('info', 'Pessoa vinculada não encontrada');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            $dataHora = $this->normalizarDataHora($dados['data_hora'] ?? null);
            $dataHoraFim = $this->normalizarDataHora($dados['data_hora_fim'] ?? null);

            if (!$dataHora) {
                $this->flash('info', 'Informe a data e hora do compromisso');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            if ($dataHoraFim && strtotime($dataHoraFim) < strtotime($dataHora)) {
                $this->flash('info', 'A data final não pode ser menor que a data inicial');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            Agenda::create([
                'gabinete_id' => $this->usuario['gabinete_id'],
                'tipo_agenda_id' => $tipo->id,
                'situacao_agenda_id' => $situacao->id,
                'usuario_id' => $this->usuario['id'],
                'pessoa_id' => $pessoa?->id,
                'titulo' => trim($dados['titulo']),
                'descricao' => trim($dados['descricao'] ?? '') ?: null,
                'local' => trim($dados['local'] ?? '') ?: null,
                'data_hora' => $dataHora,
                'data_hora_fim' => $dataHoraFim
            ]);

            $this->flash('success', 'Compromisso cadastrado com sucesso');
            return $this->redirect($response, self::VIEW_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE);
        }
    }
}
