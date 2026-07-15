<?php

namespace App\Controllers;

use App\Models\TipoDocumento;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TipoDocumentoController extends BaseController {
    private const VIEW_TIPOS_DOCUMENTOS = 'pages/documentos/tipos-documentos.twig';
    private const VIEW_TIPOS_ROUTE = '/documentos/tipos';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    private function listarTipos() {
        return TipoDocumento::with('usuario')
            ->where('gabinete_id', $this->usuario['gabinete_id'])
            ->orderBy('nome', 'asc')
            ->get();
    }

    private function buscarTipo(int $id): ?TipoDocumento {
        return TipoDocumento::where([
            'id' => $id,
            'gabinete_id' => $this->usuario['gabinete_id']
        ])->first();
    }

    public function indexTiposDocumentos(Request $request, Response $response): Response {
        try {
            $payload['tipos'] = $this->listarTipos();
            $payload = array_merge($payload, $this->getFlash());

            return $this->renderView($request, $response, self::VIEW_TIPOS_DOCUMENTOS, $payload);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_TIPOS_DOCUMENTOS, $this->getFlash());
        }
    }

    public function newTipoDocumentos(Request $request, Response $response): Response {
        if (!in_array($this->usuario['nivel'], [1, 3])) {
            $this->flash('info', 'Você não tem autorização para criar novos tipos de documentos');
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        }

        try {
            $dados = $request->getParsedBody();

            $busca = TipoDocumento::where([
                'nome' => $dados['nome'],
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->first();

            if ($busca) {
                $this->flash('info', 'Esse tipo de documento já está cadastrado');
                return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
            }

            TipoDocumento::create([
                'nome' => $dados['nome'],
                'sigla' => $dados['sigla'] ?? null,
                'gabinete_id' => $this->usuario['gabinete_id'],
                'usuario_id' => $this->usuario['id']
            ]);

            $this->flash('success', 'Tipo de documento cadastrado com sucesso');
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        }
    }

    public function updateTipoDocumentos(Request $request, Response $response, array $args): Response {
        try {
            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para atualizar tipos de documentos');
                return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
            }

            $id = (int) ($args['id'] ?? 0);
            $tipo = $this->buscarTipo($id);

            if (!$tipo) {
                $this->flash('info', 'Tipo de documento não encontrado');
                return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
            }

            $dados = $request->getParsedBody();

            $busca = TipoDocumento::where('nome', $dados['nome'])
                ->where('gabinete_id', $this->usuario['gabinete_id'])
                ->where('id', '!=', $tipo->id)
                ->first();

            if ($busca) {
                $this->flash('info', 'Esse tipo de documento já está cadastrado com este nome');
                return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
            }

            $tipo->update([
                'nome' => $dados['nome'],
                'sigla' => $dados['sigla'] ?? null
            ]);

            $this->flash('success', 'Tipo de documento atualizado com sucesso');
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        }
    }

    public function buscarTipoDocumento(Request $request, Response $response, array $args): Response {
        try {
            $id = (int) ($args['id'] ?? 0);
            $tipo = $this->buscarTipo($id);

            if (!$tipo) {
                $this->flash('info', 'Tipo de documento não encontrado');
                return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
            }

            $payload['tipos'] = $this->listarTipos();
            $payload['tipo'] = $tipo;
            $payload = array_merge($payload, $this->getFlash());

            return $this->renderView($request, $response, self::VIEW_TIPOS_DOCUMENTOS, $payload);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        }
    }

    public function apagarTipoDocumento(Request $request, Response $response, array $args): Response {
        try {
            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para apagar tipos de documentos');
                return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
            }

            $id = (int) ($args['id'] ?? 0);
            $tipo = $this->buscarTipo($id);

            if (!$tipo) {
                $this->flash('info', 'Tipo de documento não encontrado');
                return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
            }

            $tipo->delete();

            $this->flash('success', 'Tipo de documento apagado com sucesso');
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                $this->flash('error', 'Este tipo não pode ser removido porque está vinculado a documentos existentes');
                return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
            }

            $this->flashError($e);
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        }
    }

    public function inserirTiposPadrao(Request $request, Response $response, array $args): Response {
        try {
            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para inserir tipos padrão');
                return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
            }

            // Altere o caminho ou o arquivo JSON conforme sua nova estrutura de tipos de documentos padrão
            $json = file_get_contents(__DIR__ . '/../Json/tipos_documentos.json');
            $tiposDocumentos = json_decode($json, true);

            foreach ($tiposDocumentos as $tipoDoc) {
                TipoDocumento::firstOrCreate(
                    [
                        'nome' => $tipoDoc['nome'],
                        'gabinete_id' => $this->usuario['gabinete_id']
                    ],
                    [
                        'sigla' => $tipoDoc['sigla'] ?? null,
                        'usuario_id' => $this->usuario['id']
                    ]
                );
            }

            $this->flash('success', 'Tipos de documentos padrão inseridos com sucesso');
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_TIPOS_ROUTE);
        }
    }
}
