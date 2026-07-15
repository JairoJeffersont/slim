<?php

namespace App\Controllers;

use App\Models\Pessoa;
use App\Models\Orgao;
use App\Models\Profissao;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PessoaController extends BaseController {
    private const VIEW_PESSOAS = 'pages/pessoas/pessoas.twig';
    private const VIEW_ROUTE = '/pessoas';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    private function listarOrgaos() {
        return Orgao::where('gabinete_id', $this->usuario['gabinete_id'])->orderBy('nome')->get();
    }

    private function listarProfissoes() {
        return Profissao::where('gabinete_id', $this->usuario['gabinete_id'])->orderBy('nome')->get();
    }

    private function getFiltros(array $params): array {
        $ordenacoesPermitidas = ['nome', 'created_at', 'estado', 'cidade'];

        return [
            'ordenarPor' => in_array($params['ordenarPor'] ?? '', $ordenacoesPermitidas) ? $params['ordenarPor'] : 'nome',
            'ordem' => in_array(strtolower($params['ordem'] ?? ''), ['asc', 'desc']) ? strtolower($params['ordem']) : 'asc',
            'itens' => (int)($params['itens'] ?? 10),
            'pagina' => (int)($params['pagina'] ?? 1),
            'busca' => $params['busca'] ?? null,
            'estado' => isset($params['estado']) ? ($params['estado'] ?: null) : $this->usuario['gabinete_estado']
        ];
    }

    private function listarPessoas(array $filtros): array {
        $query = Pessoa::with(['usuario', 'orgao', 'profissao'])
            ->where('gabinete_id', $this->usuario['gabinete_id'])
            ->when($filtros['busca'], fn($q) => $q->where('nome', 'like', "%{$filtros['busca']}%"))
            ->when($filtros['estado'], fn($q) => $q->where('estado', $filtros['estado']));

        $total = $query->count();

        return [
            'data' => $query->orderBy($filtros['ordenarPor'], $filtros['ordem'])
                ->offset(($filtros['pagina'] - 1) * $filtros['itens'])
                ->limit($filtros['itens'])
                ->get(),
            'total_paginas' => ceil($total / $filtros['itens'])
        ];
    }

    public function indexPessoas(Request $request, Response $response): Response {
        try {
            $filtros = $this->getFiltros($request->getQueryParams());

            $resultado = $this->listarPessoas($filtros);

            $payload = [
                'pessoas' => $resultado['data'],
                'total_paginas' => $resultado['total_paginas'],
                'pagina_atual' => $filtros['pagina'],
                'estado_gabinete' => $filtros['estado'],
                'orgaos' => $this->listarOrgaos(),
                'profissoes' => $this->listarProfissoes(),
                'parametros' => $filtros
            ];

            return $this->renderView($request, $response, self::VIEW_PESSOAS, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_PESSOAS, $this->getFlash());
        }
    }

    public function newPessoa(Request $request, Response $response): Response {
        if (!in_array($this->usuario['nivel'], [1, 3])) {
            $this->flash('info', 'Você não tem autorização para cadastrar uma pessoa');
            return $this->redirect($response, self::VIEW_ROUTE);
        }

        try {
            $dados = $request->getParsedBody();

            if (!empty($dados['email'])) {
                $pessoaExistente = Pessoa::where([
                    'nome' => $dados['nome'],
                    'email' => $dados['email'],
                    'gabinete_id' => $this->usuario['gabinete_id']
                ])->first();

                if ($pessoaExistente) {
                    $this->flash('info', 'Esta pessoa com este e-mail já está cadastrada');
                    return $this->redirect($response, self::VIEW_ROUTE);
                }
            }

            Pessoa::create([
                'nome' => $dados['nome'],
                'orgao_id' => $dados['orgao_id'] ?: null,
                'profissao_id' => $dados['profissao_id'] ?: null,
                'email' => $dados['email'],
                'telefone' => $dados['telefone'],
                'aniversario' => !empty($dados['aniversario'])
                    ? '2000-' . implode('-', array_reverse(explode('/', $dados['aniversario'])))
                    : null,
                'endereco' => $dados['endereco'],
                'bairro' => $dados['bairro'],
                'cidade' => $dados['cidade'],
                'estado' => $dados['estado'],
                'instagram' => $dados['instagram'],
                'facebook' => $dados['facebook'],
                'foto' => $dados['foto'] ?? null,
                'informacoes' => $dados['informacoes'],
                'gabinete_id' => $this->usuario['gabinete_id'],
                'usuario_id' => $this->usuario['id']
            ]);

            $this->flash('success', 'Pessoa cadastrada com sucesso');

            return $this->redirect($response, self::VIEW_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE);
        }
    }
}
