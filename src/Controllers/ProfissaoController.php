<?php

namespace App\Controllers;

use App\Models\Profissao;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProfissaoController extends BaseController {
    private const VIEW_PROFISSOES = 'pages/pessoas/profissoes.twig';
    private const VIEW_PROFISSOES_ROUTE = '/pessoas/profissoes';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    private function listarProfissoes() {
        return Profissao::with('usuario')
            ->where('gabinete_id', $this->usuario['gabinete_id'])
            ->orderBy('nome', 'asc')
            ->get();
    }

    private function buscarProfissaoPorId(int $id): ?Profissao {
        return Profissao::where([
            'id' => $id,
            'gabinete_id' => $this->usuario['gabinete_id']
        ])->first();
    }

    public function indexProfissoes(Request $request, Response $response): Response {
        try {
            $payload['profissoes'] = $this->listarProfissoes();
            $payload = array_merge($payload, $this->getFlash());

            return $this->renderView($request, $response, self::VIEW_PROFISSOES, $payload);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_PROFISSOES, $this->getFlash());
        }
    }

    public function newProfissao(Request $request, Response $response): Response {
        if ($this->usuario['nivel'] != 1) {
            $this->flash('info', 'Você não tem autorização para criar novas profissões');
            return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
        }
        try {
            $dados = $request->getParsedBody();

            $busca = Profissao::where([
                'nome' => $dados['nome'],
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->first();

            if ($busca) {
                $this->flash('info', 'Esta profissão já está cadastrada');
                return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
            }

            Profissao::create([
                'nome' => $dados['nome'],
                'gabinete_id' => $this->usuario['gabinete_id'],
                'usuario_id' => $this->usuario['id']
            ]);

            $this->flash('success', 'Profissão cadastrada com sucesso');

            return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
        }
    }

    public function updateProfissao(Request $request, Response $response, array $args): Response {
        try {
            if ($this->usuario['nivel'] != 1) {
                $this->flash('info', 'Você não tem autorização para atualizar profissões');
                return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
            }

            $id = (int) ($args['id'] ?? 0);

            $profissao = $this->buscarProfissaoPorId($id);

            if (!$profissao) {
                $this->flash('info', 'Profissão não encontrada');
                return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
            }

            $dados = $request->getParsedBody();

            $busca = Profissao::where('nome', $dados['nome'])
                ->where('gabinete_id', $this->usuario['gabinete_id'])
                ->where('id', '!=', $profissao->id)
                ->first();

            if ($busca) {
                $this->flash('info', 'Esta profissão já está cadastrada');
                return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
            }

            $profissao->update([
                'nome' => $dados['nome']
            ]);

            $this->flash('success', 'Profissão atualizada com sucesso');

            return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
        }
    }

    public function obterProfissao(Request $request, Response $response, array $args): Response {
        try {
            $id = (int) ($args['id'] ?? 0);

            $profissao = $this->buscarProfissaoPorId($id);

            if (!$profissao) {
                $this->flash('info', 'Profissão não encontrada');
                return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
            }

            $payload['profissoes'] = $this->listarProfissoes();
            $payload['profissao'] = $profissao;
            $payload = array_merge($payload, $this->getFlash());

            return $this->renderView($request, $response, self::VIEW_PROFISSOES, $payload);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
        }
    }

    public function apagarProfissao(Request $request, Response $response, array $args): Response {
        try {
            if ($this->usuario['nivel'] != 1) {
                $this->flash('info', 'Você não tem autorização para apagar profissões');
                return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
            }

            $id = (int) ($args['id'] ?? 0);

            $profissao = $this->buscarProfissaoPorId($id);

            if (!$profissao) {
                $this->flash('info', 'Profissão não encontrada');
                return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
            }

            $profissao->delete();

            $this->flash('success', 'Profissão apagada com sucesso');
            return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                $this->flash('error', 'Esta profissão não pode ser removida porque está vinculada a outros registros');
                return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
            }

            $this->flashError($e);
            return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
        }
    }

    public function inserirProfissoesPadrao(Request $request, Response $response, array $args): Response {
        try {
            if ($this->usuario['nivel'] != 1) {
                $this->flash('info', 'Você não tem autorização para inserir profissões');
                return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
            }

            $json = file_get_contents(__DIR__ . '/../Json/profissoes.json');
            $profissoes = json_decode($json, true);

            foreach ($profissoes as $profissao) {
                Profissao::firstOrCreate(
                    [
                        'nome' => $profissao['nome'],
                        'gabinete_id' => $this->usuario['gabinete_id']
                    ],
                    [
                        'usuario_id' => $this->usuario['id']
                    ]
                );
            }

            $this->flash('success', 'Profissões padrão inseridas com sucesso');
            return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_PROFISSOES_ROUTE);
        }
    }
}
