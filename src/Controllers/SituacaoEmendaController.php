<?php

namespace App\Controllers;

use App\Models\SituacaoEmenda;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SituacaoEmendaController extends BaseController {
    private const VIEW = 'pages/emendas/situacoes-emendas.twig';
    private const ROUTE = '/emendas/situacoes';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    private function listarSituacoes() {
        return SituacaoEmenda::with('usuario')
            ->where('gabinete_id', $this->usuario['gabinete_id'])
            ->orderBy('nome', 'asc')
            ->get();
    }

    private function buscarSituacao(int $id): ?SituacaoEmenda {
        return SituacaoEmenda::where([
            'id' => $id,
            'gabinete_id' => $this->usuario['gabinete_id']
        ])->first();
    }

    public function index(Request $request, Response $response): Response {
        try {
            $payload['situacoes'] = $this->listarSituacoes();
            return $this->renderView($request, $response, self::VIEW, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW, $this->getFlash());
        }
    }

    public function novo(Request $request, Response $response): Response {
        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para criar situações de emenda');
            return $this->redirect($response, self::ROUTE);
        }

        try {
            $dados = $request->getParsedBody();
            $nome = trim((string) ($dados['nome'] ?? ''));

            if ($nome === '') {
                $this->flash('info', 'Nome é obrigatório');
                return $this->redirect($response, self::ROUTE);
            }

            $busca = SituacaoEmenda::where([
                'nome' => $nome,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->first();

            if ($busca) {
                $this->flash('info', 'Essa situação de emenda já está cadastrada');
                return $this->redirect($response, self::ROUTE);
            }

            SituacaoEmenda::create([
                'nome' => $nome,
                'gabinete_id' => $this->usuario['gabinete_id'],
                'usuario_id' => $this->usuario['id']
            ]);

            $this->flash('success', 'Situação de emenda cadastrada com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function buscar(Request $request, Response $response, array $args): Response {
        try {
            $id = (int) ($args['id'] ?? 0);
            $situacao = $this->buscarSituacao($id);

            if (!$situacao) {
                $this->flash('info', 'Situação de emenda não encontrada');
                return $this->redirect($response, self::ROUTE);
            }

            $payload['situacoes'] = $this->listarSituacoes();
            $payload['situacao'] = $situacao;
            return $this->renderView($request, $response, self::VIEW, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function atualizar(Request $request, Response $response, array $args): Response {
        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para atualizar situações de emenda');
            return $this->redirect($response, self::ROUTE);
        }

        try {
            $id = (int) ($args['id'] ?? 0);
            $situacao = $this->buscarSituacao($id);

            if (!$situacao) {
                $this->flash('info', 'Situação de emenda não encontrada');
                return $this->redirect($response, self::ROUTE);
            }

            $dados = $request->getParsedBody();
            $nome = trim((string) ($dados['nome'] ?? ''));

            if ($nome === '') {
                $this->flash('info', 'Nome é obrigatório');
                return $this->redirect($response, self::ROUTE);
            }

            $duplicado = SituacaoEmenda::where('gabinete_id', $this->usuario['gabinete_id'])
                ->where('nome', $nome)
                ->where('id', '!=', $situacao->id)
                ->first();

            if ($duplicado) {
                $this->flash('info', 'Essa situação de emenda já está cadastrada com este nome');
                return $this->redirect($response, self::ROUTE);
            }

            $situacao->update(['nome' => $nome]);

            $this->flash('success', 'Situação de emenda atualizada com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function apagar(Request $request, Response $response, array $args): Response {
        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para apagar situações de emenda');
            return $this->redirect($response, self::ROUTE);
        }

        try {
            $id = (int) ($args['id'] ?? 0);
            $situacao = $this->buscarSituacao($id);

            if (!$situacao) {
                $this->flash('info', 'Situação de emenda não encontrada');
                return $this->redirect($response, self::ROUTE);
            }

            $situacao->delete();

            $this->flash('success', 'Situação de emenda apagada com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            if (str_contains(strtolower($e->getMessage()), 'foreign key constraint fails')) {
                $this->flash('error', 'Esta situação não pode ser removida porque está vinculada a emendas existentes');
                return $this->redirect($response, self::ROUTE);
            }

            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function inserirPadrao(Request $request, Response $response): Response {
        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para inserir situações padrão');
            return $this->redirect($response, self::ROUTE);
        }

        try {
            $json = file_get_contents(__DIR__ . '/../Json/situacoes_emendas.json');
            $situacoes = json_decode($json, true);

            foreach ($situacoes as $item) {
                SituacaoEmenda::firstOrCreate(
                    [
                        'nome' => $item['nome'],
                        'gabinete_id' => $this->usuario['gabinete_id']
                    ],
                    [
                        'usuario_id' => $this->usuario['id']
                    ]
                );
            }

            $this->flash('success', 'Situações de emenda padrão inseridas com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }
}
