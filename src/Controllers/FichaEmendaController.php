<?php

namespace App\Controllers;

use App\Models\Emenda;
use App\Models\SituacaoEmenda;
use App\Models\TemaEmenda;
use App\Models\TipoEmenda;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FichaEmendaController extends BaseController {
    private const VIEW = 'pages/emendas/ficha_emenda.twig';
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

    private function buscarEmenda(int $id): ?Emenda {
        return Emenda::with(['tipoEmenda', 'situacaoEmenda', 'temaEmenda', 'usuario'])
            ->where([
                'id' => $id,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])
            ->first();
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

    public function index(Request $request, Response $response, array $args): Response {
        try {
            $id = (int) ($args['id'] ?? 0);
            $emenda = $this->buscarEmenda($id);

            if (!$emenda) {
                $this->flash('info', 'Emenda não encontrada');
                return $this->redirect($response, self::ROUTE);
            }

            $payload['emenda'] = $emenda;
            $payload['tipos'] = $this->listarTipos();
            $payload['situacoes'] = $this->listarSituacoes();
            $payload['temas'] = $this->listarTemas();

            return $this->renderView($request, $response, self::VIEW, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function atualizar(Request $request, Response $response, array $args): Response {
        $id = (int) ($args['id'] ?? 0);

        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para editar emendas');
            return $this->redirect($response, self::ROUTE . '/' . $id);
        }

        try {
            $emenda = $this->buscarEmenda($id);

            if (!$emenda) {
                $this->flash('info', 'Emenda não encontrada');
                return $this->redirect($response, self::ROUTE);
            }

            $dados = $request->getParsedBody();

            $tipo = $this->buscarTipo((int) ($dados['tipo_emenda_id'] ?? 0));
            $situacao = $this->buscarSituacao((int) ($dados['situacao_emenda_id'] ?? 0));
            $tema = $this->buscarTema((int) ($dados['tema_emenda_id'] ?? 0));

            if (!$tipo || !$situacao || !$tema) {
                $this->flash('info', 'Tipo, situação e tema da emenda são obrigatórios');
                return $this->redirect($response, self::ROUTE . '/' . $id);
            }

            $titulo = trim((string) ($dados['titulo'] ?? ''));
            if ($titulo === '') {
                $this->flash('info', 'Título da emenda é obrigatório');
                return $this->redirect($response, self::ROUTE . '/' . $id);
            }

            $emenda->update([
                'tipo_emenda_id' => $tipo->id,
                'situacao_emenda_id' => $situacao->id,
                'tema_emenda_id' => $tema->id,
                'titulo' => $titulo,
                'descricao' => trim((string) ($dados['descricao'] ?? '')) ?: null,
                'numero' => trim((string) ($dados['numero'] ?? '')) ?: null,
                'ano' => !empty($dados['ano']) ? (int) $dados['ano'] : null,
                'valor' => $this->normalizarValor($dados['valor'] ?? null),
                'data_publicacao' => !empty($dados['data_publicacao']) ? $dados['data_publicacao'] : null
            ]);

            $this->flash('success', 'Emenda atualizada com sucesso');
            return $this->redirect($response, self::ROUTE . '/' . $id);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE . '/' . $id);
        }
    }

    public function apagar(Request $request, Response $response, array $args): Response {
        $id = (int) ($args['id'] ?? 0);

        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para apagar emendas');
            return $this->redirect($response, self::ROUTE . '/' . $id);
        }

        try {
            $emenda = $this->buscarEmenda($id);

            if (!$emenda) {
                $this->flash('info', 'Emenda não encontrada');
                return $this->redirect($response, self::ROUTE);
            }

            $emenda->delete();

            $this->flash('success', 'Emenda apagada com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE . '/' . $id);
        }
    }
}
