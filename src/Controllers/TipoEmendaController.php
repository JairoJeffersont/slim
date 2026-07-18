<?php

namespace App\Controllers;

use App\Models\TipoEmenda;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TipoEmendaController extends BaseController {
    private const VIEW = 'pages/emendas/tipos-emendas.twig';
    private const ROUTE = '/emendas/tipos';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    private function listarTipos() {
        return TipoEmenda::with('usuario')
            ->where('gabinete_id', $this->usuario['gabinete_id'])
            ->orderBy('nome', 'asc')
            ->get();
    }

    private function buscarTipo(int $id): ?TipoEmenda {
        return TipoEmenda::where([
            'id' => $id,
            'gabinete_id' => $this->usuario['gabinete_id']
        ])->first();
    }

    public function index(Request $request, Response $response): Response {
        try {
            $payload['tipos'] = $this->listarTipos();
            return $this->renderView($request, $response, self::VIEW, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW, $this->getFlash());
        }
    }

    public function novo(Request $request, Response $response): Response {
        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para criar tipos de emenda');
            return $this->redirect($response, self::ROUTE);
        }

        try {
            $dados = $request->getParsedBody();
            $nome = trim((string) ($dados['nome'] ?? ''));

            if ($nome === '') {
                $this->flash('info', 'Nome é obrigatório');
                return $this->redirect($response, self::ROUTE);
            }

            $busca = TipoEmenda::where([
                'nome' => $nome,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->first();

            if ($busca) {
                $this->flash('info', 'Esse tipo de emenda já está cadastrado');
                return $this->redirect($response, self::ROUTE);
            }

            TipoEmenda::create([
                'nome' => $nome,
                'gabinete_id' => $this->usuario['gabinete_id'],
                'usuario_id' => $this->usuario['id']
            ]);

            $this->flash('success', 'Tipo de emenda cadastrado com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function buscar(Request $request, Response $response, array $args): Response {
        try {
            $id = (int) ($args['id'] ?? 0);
            $tipo = $this->buscarTipo($id);

            if (!$tipo) {
                $this->flash('info', 'Tipo de emenda não encontrado');
                return $this->redirect($response, self::ROUTE);
            }

            $payload['tipos'] = $this->listarTipos();
            $payload['tipo'] = $tipo;
            return $this->renderView($request, $response, self::VIEW, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function atualizar(Request $request, Response $response, array $args): Response {
        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para atualizar tipos de emenda');
            return $this->redirect($response, self::ROUTE);
        }

        try {
            $id = (int) ($args['id'] ?? 0);
            $tipo = $this->buscarTipo($id);

            if (!$tipo) {
                $this->flash('info', 'Tipo de emenda não encontrado');
                return $this->redirect($response, self::ROUTE);
            }

            $dados = $request->getParsedBody();
            $nome = trim((string) ($dados['nome'] ?? ''));

            if ($nome === '') {
                $this->flash('info', 'Nome é obrigatório');
                return $this->redirect($response, self::ROUTE);
            }

            $duplicado = TipoEmenda::where('gabinete_id', $this->usuario['gabinete_id'])
                ->where('nome', $nome)
                ->where('id', '!=', $tipo->id)
                ->first();

            if ($duplicado) {
                $this->flash('info', 'Esse tipo de emenda já está cadastrado com este nome');
                return $this->redirect($response, self::ROUTE);
            }

            $tipo->update(['nome' => $nome]);

            $this->flash('success', 'Tipo de emenda atualizado com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function apagar(Request $request, Response $response, array $args): Response {
        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para apagar tipos de emenda');
            return $this->redirect($response, self::ROUTE);
        }

        try {
            $id = (int) ($args['id'] ?? 0);
            $tipo = $this->buscarTipo($id);

            if (!$tipo) {
                $this->flash('info', 'Tipo de emenda não encontrado');
                return $this->redirect($response, self::ROUTE);
            }

            $tipo->delete();

            $this->flash('success', 'Tipo de emenda apagado com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            if (str_contains(strtolower($e->getMessage()), 'foreign key constraint fails')) {
                $this->flash('error', 'Este tipo não pode ser removido porque está vinculado a emendas existentes');
                return $this->redirect($response, self::ROUTE);
            }

            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function inserirPadrao(Request $request, Response $response): Response {
        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para inserir tipos padrão');
            return $this->redirect($response, self::ROUTE);
        }

        try {
            $json = file_get_contents(__DIR__ . '/../Json/tipos_emendas.json');
            $tipos = json_decode($json, true);

            foreach ($tipos as $item) {
                TipoEmenda::firstOrCreate(
                    [
                        'nome' => $item['nome'],
                        'gabinete_id' => $this->usuario['gabinete_id']
                    ],
                    [
                        'usuario_id' => $this->usuario['id']
                    ]
                );
            }

            $this->flash('success', 'Tipos de emenda padrão inseridos com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }
}
