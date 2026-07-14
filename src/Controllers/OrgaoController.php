<?php

namespace App\Controllers;

use App\Models\Orgao;
use App\Models\TipoOrgao;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OrgaoController extends BaseController {
    private const VIEW_ORGAOS = 'pages/orgaos/orgaos.twig';
    private const VIEW_ROUTE = '/orgaos';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    private function listarTipos() {
        return TipoOrgao::with('usuario')->where('gabinete_id', $this->usuario['gabinete_id'])->orderBy('nome')->get();
    }

    private function getFiltros(array $params): array {
        $ordenacoesPermitidas = ['nome', 'created_at', 'estado', 'cidade'];

        return [
            'ordenarPor' => in_array($params['ordenarPor'] ?? '', $ordenacoesPermitidas) ? $params['ordenarPor'] : 'nome',
            'ordem' => in_array(strtolower($params['ordem'] ?? ''), ['asc', 'desc']) ? strtolower($params['ordem']) : 'asc',
            'itens' => (int)($params['itens'] ?? 10),
            'pagina' => (int)($params['pagina'] ?? 1),
            'busca' => $params['busca'] ?? null,
            'tipo' => isset($params['tipo']) && $params['tipo'] !== '' ? $params['tipo'] : null,
            'estado' => isset($params['estado']) ? ($params['estado'] ?: null) : $this->usuario['gabinete_estado']
        ];
    }

    private function listarOrgaos(array $filtros): array {
        $query = Orgao::with(['usuario', 'tipoOrgao'])
            ->where('gabinete_id', $this->usuario['gabinete_id'])
            ->when($filtros['busca'], fn($q) => $q->where('nome', 'like', "%{$filtros['busca']}%"))
            ->when($filtros['estado'], fn($q) => $q->where('estado', $filtros['estado']))
            ->when($filtros['tipo'] !== null && $filtros['tipo'] !== '0', fn($q) => $q->where('tipo_orgao_id', $filtros['tipo']))
            ->when($filtros['tipo'] === '0', fn($q) => $q->whereNull('tipo_orgao_id'));

        $total = $query->count();

        return [
            'data' => $query->orderBy($filtros['ordenarPor'], $filtros['ordem'])
                ->offset(($filtros['pagina'] - 1) * $filtros['itens'])
                ->limit($filtros['itens'])
                ->get(),
            'total_paginas' => ceil($total / $filtros['itens'])
        ];
    }

    public function indexOrgaos(Request $request, Response $response): Response {
        try {
            $filtros = $this->getFiltros($request->getQueryParams());

            $resultado = $this->listarOrgaos($filtros);

            $payload = [
                'orgaos' => $resultado['data'],
                'total_paginas' => $resultado['total_paginas'],
                'pagina_atual' => $filtros['pagina'],
                'estado_gabinete' => $filtros['estado'],
                'tipos' => $this->listarTipos(),
                'parametros' => $filtros
            ];

            return $this->renderView($request, $response, self::VIEW_ORGAOS, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_ORGAOS, $this->getFlash());
        }
    }

    public function newOrgao(Request $request, Response $response): Response {
        if (!in_array($this->usuario['nivel'], [1, 3])) {
            $this->flash('info', 'Você não tem autorização para criar um órgão');
            return $this->redirect($response, self::VIEW_ROUTE);
        }

        try {
            $dados = $request->getParsedBody();

            if (
                Orgao::where([
                    'nome' => $dados['nome'],
                    'gabinete_id' => $this->usuario['gabinete_id']
                ])->first()
            ) {
                $this->flash('info', 'Esse órgão/entidade já está cadastrado');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            Orgao::create([
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'telefone' => $dados['telefone'],
                'endereco' => $dados['endereco'],
                'bairro' => $dados['bairro'],
                'cidade' => $dados['cidade'],
                'informacoes' => $dados['informacoes'],
                'estado' => $dados['estado'],
                'tipo_orgao_id' => $dados['tipo_orgao_id'] ?: null,
                'gabinete_id' => $this->usuario['gabinete_id'],
                'usuario_id' => $this->usuario['id']
            ]);

            $this->flash('success', 'Órgão/entidade cadastrado com sucesso');

            return $this->redirect($response, self::VIEW_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE);
        }
    }
}
