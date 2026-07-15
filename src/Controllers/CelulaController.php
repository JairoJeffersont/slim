<?php


namespace App\Controllers;

use App\Models\Pessoa;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Support\Str;

class CelulaController extends BaseController {
    private const VIEW_LIDERADOS = 'pages/celulas/liderados.twig';
    private const VIEW_CADASTRO_CONVIDADO = 'pages/celulas/cadastro_convidado.twig';
    private const VIEW_SUCESSO_CADASTRO = 'pages/celulas/sucesso_cadastro.twig';
    private const VIEW_ROUTE_PESSOAS = '/pessoas';
    private const VIEW_LISTA_LIDERES = 'pages/celulas/lista_lideres.twig';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'] ?? [];
    }

    public function tornarLider(Request $request, Response $response, array $args): Response {
        try {

            $id = $args['id'];

            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização');
                return $this->redirect($response, '/pessoas/' . $id);
            }

            $pessoa = Pessoa::where([
                'id' => $id,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->first();

            if (!$pessoa) {
                $this->flash('error', 'Pessoa não encontrada');
                return $this->redirect($response, self::VIEW_ROUTE_PESSOAS);
            }

            $pessoa->update([
                'lideranca' => true,
                'token' => Str::uuid()->toString()
            ]);

            $this->flash('success', 'Liderança ativada com sucesso');
            return $this->redirect($response, self::VIEW_ROUTE_PESSOAS . '/' . $id);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE_PESSOAS);
        }
    }

    public function removerLider(Request $request, Response $response, array $args): Response {
        try {
            $id = $args['id'];

            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização');
                return $this->redirect($response, '/pessoas/' . $id);
            }

            $pessoa = Pessoa::where([
                'id' => $id,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->first();

            if (!$pessoa) {
                $this->flash('error', 'Pessoa não encontrada');
                return $this->redirect($response, self::VIEW_ROUTE_PESSOAS);
            }

            $pessoa->update([
                'lideranca' => false,
                'token' => null
            ]);

            $this->flash('success', 'Liderança removida com sucesso');
            return $this->redirect($response, self::VIEW_ROUTE_PESSOAS . '/' . $id);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE_PESSOAS);
        }
    }

    public function listarLiderados(Request $request, Response $response, array $args): Response {
        try {
            $id = $args['id'];
            $lider = Pessoa::where([
                'id' => $id,
                'gabinete_id' => $this->usuario['gabinete_id'],
                'lideranca' => true
            ])->first();

            if (!$lider) {
                $this->flash('error', 'Líder não encontrado');
                return $this->redirect($response, self::VIEW_ROUTE_PESSOAS);
            }

            $liderados = Pessoa::where([
                'indicado_por_pessoa_id' => $id,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->orderBy('nome')->get();

            $payload = [
                'lider' => $lider,
                'liderados' => $liderados
            ];

            return $this->renderView($request, $response, self::VIEW_LIDERADOS, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE_PESSOAS);
        }
    }

    public function cadastroConvidado(Request $request, Response $response): Response {
        try {
            $queryParams = $request->getQueryParams();
            $token = $queryParams['token'] ?? null;

            if (!$token) {
                $this->flash('info', 'Token não inválido');
                return $this->renderView($request, $response, self::VIEW_CADASTRO_CONVIDADO, $this->getFlash());
            }

            $lider = Pessoa::where('token', $token)->first();

            if (!$lider) {
                $this->flash('info', 'Token inválido');
                return $this->renderView($request, $response, self::VIEW_CADASTRO_CONVIDADO, $this->getFlash());
            }

            $payload = [
                'lider' => $lider,
                'token' => $token
            ];

            return $this->renderView($request, $response, self::VIEW_CADASTRO_CONVIDADO, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_CADASTRO_CONVIDADO, $this->getFlash());
        }
    }

    public function salvarConvidado(Request $request, Response $response): Response {
        try {
            $dados = $request->getParsedBody();
            $queryParams = $request->getQueryParams();
            $token = $queryParams['token'] ?? null;

            if (!$token) {
                $this->flash('error', 'Token de convite inválido');
                return $this->redirect($response, '/login');
            }

            $lider = Pessoa::where('token', $token)->first();

            if (!$lider) {
                $this->flash('error', 'Convite expirado ou inválido');
                return $this->redirect($response, '/login');
            }

            if (!empty($dados['email'])) {
                $existente = Pessoa::where([
                    'nome' => $dados['nome'],
                    'email' => $dados['email'],
                    'gabinete_id' => $lider->gabinete_id
                ])->first();

                if ($existente) {
                    $this->flash('info', 'Você já possui um cadastro ativo neste gabinete');
                    return $this->redirect($response, '/convite?token=' . $token);
                }
            }

            Pessoa::create([
                'nome' => $dados['nome'],
                'email' => $dados['email'] ?: null,
                'telefone' => $dados['telefone'] ?: null,
                'cidade' => $dados['cidade'],
                'estado' => $dados['estado'],
                'aniversario' => !empty($dados['aniversario'])
                    ? '2000-' . implode('-', array_reverse(explode('/', $dados['aniversario'])))
                    : null,
                'gabinete_id' => $lider->gabinete_id,
                'indicado_por_pessoa_id' => $lider->id,
                'lideranca' => false
            ]);

            return $this->renderView($request, $response, self::VIEW_SUCESSO_CADASTRO, $this->getFlash());
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, '/login');
        }
    }

    public function listarLideres(Request $request, Response $response): Response {
        try {
            // 1. Obtém o total de pessoas cadastradas no gabinete para o cálculo de porcentagem
            $totalPessoasGabinete = Pessoa::where('gabinete_id', $this->usuario['gabinete_id'])->count();

            // 2. Busca apenas os líderes do gabinete
            $lideres = Pessoa::where([
                'gabinete_id' => $this->usuario['gabinete_id'],
                'lideranca' => true
            ])
                ->orderBy('nome')
                ->get();

            // 3. Para cada líder, conta quantos liderados ele possui
            // (Isso evita carregar todos os dados dos liderados na memória, deixando a consulta muito rápida)
            foreach ($lideres as $lider) {
                $qtdLiderados = Pessoa::where([
                    'indicado_por_pessoa_id' => $lider->id,
                    'gabinete_id' => $this->usuario['gabinete_id']
                ])->count();

                $lider->total_liderados = $qtdLiderados + 1;

                // Calcula a porcentagem em relação ao total do gabinete (evitando divisão por zero)
                $lider->porcentagem = $totalPessoasGabinete > 0
                    ? round(($qtdLiderados / $totalPessoasGabinete) * 100, 1)
                    : 0;
            }

            $payload = [
                'lideres' => $lideres,
                'total_pessoas_gabinete' => $totalPessoasGabinete
            ];

            return $this->renderView($request, $response, self::VIEW_LISTA_LIDERES, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE_PESSOAS);
        }
    }
}
