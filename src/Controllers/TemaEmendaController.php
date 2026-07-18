<?php

namespace App\Controllers;

use App\Models\TemaEmenda;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TemaEmendaController extends BaseController {
    private const VIEW = 'pages/emendas/temas-emendas.twig';
    private const ROUTE = '/emendas/temas';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    private function listarTemas() {
        return TemaEmenda::with('usuario')
            ->where('gabinete_id', $this->usuario['gabinete_id'])
            ->orderBy('nome', 'asc')
            ->get();
    }

    private function buscarTema(int $id): ?TemaEmenda {
        return TemaEmenda::where([
            'id' => $id,
            'gabinete_id' => $this->usuario['gabinete_id']
        ])->first();
    }

    public function index(Request $request, Response $response): Response {
        try {
            $payload['temas'] = $this->listarTemas();
            return $this->renderView($request, $response, self::VIEW, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW, $this->getFlash());
        }
    }

    public function novo(Request $request, Response $response): Response {
        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para criar temas de emenda');
            return $this->redirect($response, self::ROUTE);
        }

        try {
            $dados = $request->getParsedBody();
            $nome = trim((string) ($dados['nome'] ?? ''));

            if ($nome === '') {
                $this->flash('info', 'Nome é obrigatório');
                return $this->redirect($response, self::ROUTE);
            }

            $busca = TemaEmenda::where([
                'nome' => $nome,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->first();

            if ($busca) {
                $this->flash('info', 'Esse tema de emenda já está cadastrado');
                return $this->redirect($response, self::ROUTE);
            }

            TemaEmenda::create([
                'nome' => $nome,
                'gabinete_id' => $this->usuario['gabinete_id'],
                'usuario_id' => $this->usuario['id']
            ]);

            $this->flash('success', 'Tema de emenda cadastrado com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function buscar(Request $request, Response $response, array $args): Response {
        try {
            $id = (int) ($args['id'] ?? 0);
            $tema = $this->buscarTema($id);

            if (!$tema) {
                $this->flash('info', 'Tema de emenda não encontrado');
                return $this->redirect($response, self::ROUTE);
            }

            $payload['temas'] = $this->listarTemas();
            $payload['tema'] = $tema;
            return $this->renderView($request, $response, self::VIEW, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function atualizar(Request $request, Response $response, array $args): Response {
        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para atualizar temas de emenda');
            return $this->redirect($response, self::ROUTE);
        }

        try {
            $id = (int) ($args['id'] ?? 0);
            $tema = $this->buscarTema($id);

            if (!$tema) {
                $this->flash('info', 'Tema de emenda não encontrado');
                return $this->redirect($response, self::ROUTE);
            }

            $dados = $request->getParsedBody();
            $nome = trim((string) ($dados['nome'] ?? ''));

            if ($nome === '') {
                $this->flash('info', 'Nome é obrigatório');
                return $this->redirect($response, self::ROUTE);
            }

            $duplicado = TemaEmenda::where('gabinete_id', $this->usuario['gabinete_id'])
                ->where('nome', $nome)
                ->where('id', '!=', $tema->id)
                ->first();

            if ($duplicado) {
                $this->flash('info', 'Esse tema de emenda já está cadastrado com este nome');
                return $this->redirect($response, self::ROUTE);
            }

            $tema->update(['nome' => $nome]);

            $this->flash('success', 'Tema de emenda atualizado com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function apagar(Request $request, Response $response, array $args): Response {
        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para apagar temas de emenda');
            return $this->redirect($response, self::ROUTE);
        }

        try {
            $id = (int) ($args['id'] ?? 0);
            $tema = $this->buscarTema($id);

            if (!$tema) {
                $this->flash('info', 'Tema de emenda não encontrado');
                return $this->redirect($response, self::ROUTE);
            }

            $tema->delete();

            $this->flash('success', 'Tema de emenda apagado com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            if (str_contains(strtolower($e->getMessage()), 'foreign key constraint fails')) {
                $this->flash('error', 'Este tema não pode ser removido porque está vinculado a emendas existentes');
                return $this->redirect($response, self::ROUTE);
            }

            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }

    public function inserirPadrao(Request $request, Response $response): Response {
        if (!in_array($this->usuario['nivel'], [1, 3], true)) {
            $this->flash('info', 'Você não tem autorização para inserir temas padrão');
            return $this->redirect($response, self::ROUTE);
        }

        try {
            $json = file_get_contents(__DIR__ . '/../Json/temas_emendas.json');
            $temas = json_decode($json, true);

            foreach ($temas as $item) {
                TemaEmenda::firstOrCreate(
                    [
                        'nome' => $item['nome'],
                        'gabinete_id' => $this->usuario['gabinete_id']
                    ],
                    [
                        'usuario_id' => $this->usuario['id']
                    ]
                );
            }

            $this->flash('success', 'Temas de emenda padrão inseridos com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }
}
