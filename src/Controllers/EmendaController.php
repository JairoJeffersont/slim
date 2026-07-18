<?php

namespace App\Controllers;

use App\Models\Emenda;
use App\Models\Gabinete;
use App\Models\SituacaoEmenda;
use App\Models\TemaEmenda;
use App\Models\TipoEmenda;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EmendaController extends BaseController {
    private const VIEW = 'pages/emendas/emendas.twig';
    private const VIEW_IMPRESSAO = 'pages/emendas/emendas-impressao.twig';
    private const ROUTE = '/emendas';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    private function listarTipos() {
        return TipoEmenda::where('gabinete_id', $this->usuario['gabinete_id'])
            ->orderBy('nome', 'asc')
            ->get();
    }

    private function listarSituacoes() {
        return SituacaoEmenda::where('gabinete_id', $this->usuario['gabinete_id'])
            ->orderBy('nome', 'asc')
            ->get();
    }

    private function listarTemas() {
        return TemaEmenda::where('gabinete_id', $this->usuario['gabinete_id'])
            ->orderBy('nome', 'asc')
            ->get();
    }

    private function buscarTipo(int $id): ?TipoEmenda {
        return TipoEmenda::where([
            'id' => $id,
            'gabinete_id' => $this->usuario['gabinete_id']
        ])->first();
    }

    private function buscarSituacao(int $id): ?SituacaoEmenda {
        return SituacaoEmenda::where([
            'id' => $id,
            'gabinete_id' => $this->usuario['gabinete_id']
        ])->first();
    }

    private function buscarTema(int $id): ?TemaEmenda {
        return TemaEmenda::where([
            'id' => $id,
            'gabinete_id' => $this->usuario['gabinete_id']
        ])->first();
    }

    private function normalizarValor(?string $valor): ?float {
        if (!$valor) {
            return null;
        }

        $valor = trim($valor);

        if ($valor === '') {
            return null;
        }

        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);

        return is_numeric($valor) ? (float) $valor : null;
    }

    private function listarEmendas(?int $tipo = null, ?int $situacao = null, ?int $tema = null, ?int $ano = null, ?string $busca = null) {
        return Emenda::with(['tipoEmenda', 'situacaoEmenda', 'temaEmenda', 'usuario'])
            ->where('gabinete_id', $this->usuario['gabinete_id'])
            ->when($tipo, fn($q) => $q->where('tipo_emenda_id', $tipo))
            ->when($situacao, fn($q) => $q->where('situacao_emenda_id', $situacao))
            ->when($tema, fn($q) => $q->where('tema_emenda_id', $tema))
            ->when($ano, fn($q) => $q->where('ano', $ano))
            ->when($busca, function ($q) use ($busca) {
                return $q->where(function ($sub) use ($busca) {
                    $sub->where('titulo', 'like', "%{$busca}%")
                        ->orWhere('descricao', 'like', "%{$busca}%")
                        ->orWhere('numero', 'like', "%{$busca}%");
                });
            })
            ->orderBy('ano', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function obterNomeGabinete(): string {
        $gabinete = Gabinete::find($this->usuario['gabinete_id']);
        return $gabinete?->nome ?? 'Gabinete';
    }

    public function index(Request $request, Response $response): Response {
        try {
            $query = $request->getQueryParams();

            $tipo = isset($query['tipo']) && trim((string) $query['tipo']) !== '' ? (int) $query['tipo'] : null;
            $situacao = isset($query['situacao']) && trim((string) $query['situacao']) !== '' ? (int) $query['situacao'] : null;
            $tema = isset($query['tema']) && trim((string) $query['tema']) !== '' ? (int) $query['tema'] : null;
            $ano = isset($query['ano']) && trim((string) $query['ano']) !== '' ? (int) $query['ano'] : null;
            $busca = isset($query['busca']) && trim((string) $query['busca']) !== '' ? trim((string) $query['busca']) : null;

            $payload['tipos'] = $this->listarTipos();
            $payload['situacoes'] = $this->listarSituacoes();
            $payload['temas'] = $this->listarTemas();
            $payload['emendas'] = $this->listarEmendas($tipo, $situacao, $tema, $ano, $busca);
            $payload['tipoGet'] = $tipo;
            $payload['situacaoGet'] = $situacao;
            $payload['temaGet'] = $tema;
            $payload['anoGet'] = $ano;
            $payload['busca'] = $busca;

            return $this->renderView($request, $response, self::VIEW, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW, $this->getFlash());
        }
    }

    public function novo(Request $request, Response $response): Response {
        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para inserir emendas');
            return $this->redirect($response, self::ROUTE);
        }

        try {
            $dados = $request->getParsedBody();

            $tipo = $this->buscarTipo((int) ($dados['tipo_emenda_id'] ?? 0));
            $situacao = $this->buscarSituacao((int) ($dados['situacao_emenda_id'] ?? 0));
            $tema = $this->buscarTema((int) ($dados['tema_emenda_id'] ?? 0));

            if (!$tipo || !$situacao || !$tema) {
                $this->flash('info', 'Tipo, situação e tema da emenda são obrigatórios');
                return $this->redirect($response, self::ROUTE);
            }

            $titulo = trim((string) ($dados['titulo'] ?? ''));
            if ($titulo === '') {
                $this->flash('info', 'Título da emenda é obrigatório');
                return $this->redirect($response, self::ROUTE);
            }

            Emenda::create([
                'gabinete_id' => $this->usuario['gabinete_id'],
                'tipo_emenda_id' => $tipo->id,
                'situacao_emenda_id' => $situacao->id,
                'tema_emenda_id' => $tema->id,
                'usuario_id' => $this->usuario['id'],
                'titulo' => $titulo,
                'descricao' => trim((string) ($dados['descricao'] ?? '')) ?: null,
                'numero' => trim((string) ($dados['numero'] ?? '')) ?: null,
                'ano' => !empty($dados['ano']) ? (int) $dados['ano'] : null,
                'valor' => $this->normalizarValor($dados['valor'] ?? null),
                'data_publicacao' => !empty($dados['data_publicacao']) ? $dados['data_publicacao'] : null,

            ]);

            $this->flash('success', 'Emenda cadastrada com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function imprimir(Request $request, Response $response): Response {
        try {
            $query = $request->getQueryParams();

            $tipo = isset($query['tipo']) && trim((string) $query['tipo']) !== '' ? (int) $query['tipo'] : null;
            $situacao = isset($query['situacao']) && trim((string) $query['situacao']) !== '' ? (int) $query['situacao'] : null;
            $tema = isset($query['tema']) && trim((string) $query['tema']) !== '' ? (int) $query['tema'] : null;
            $ano = isset($query['ano']) && trim((string) $query['ano']) !== '' ? (int) $query['ano'] : null;
            $busca = isset($query['busca']) && trim((string) $query['busca']) !== '' ? trim((string) $query['busca']) : null;

            $filtroTipo = $tipo ? TipoEmenda::where([
                'id' => $tipo,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->first() : null;

            $filtroSituacao = $situacao ? SituacaoEmenda::where([
                'id' => $situacao,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->first() : null;

            $filtroTema = $tema ? TemaEmenda::where([
                'id' => $tema,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->first() : null;

            $payload['emendas'] = $this->listarEmendas($tipo, $situacao, $tema, $ano, $busca);
            $payload['nome_gabinete'] = $this->obterNomeGabinete();
            $payload['filtros'] = [
                'tipo' => $filtroTipo?->nome,
                'situacao' => $filtroSituacao?->nome,
                'tema' => $filtroTema?->nome,
                'ano' => $ano,
                'busca' => $busca
            ];

            return $this->renderView($request, $response, self::VIEW_IMPRESSAO, $payload);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }
}
