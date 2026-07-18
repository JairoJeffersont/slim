<?php

namespace App\Controllers;

use App\Helpers\UploadHelper;
use App\Models\Documento;
use App\Models\TipoDocumento;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DocumentoController extends BaseController {
    private const VIEW_DOCUMENTOS = 'pages/documentos/documentos.twig';
    private const VIEW_ROUTE = '/documentos';
    private const EXTENSOES_PERMITIDAS = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    private function listarTipos() {
        return TipoDocumento::with('usuario')->where('gabinete_id', $this->usuario['gabinete_id'])->orderBy('nome')->get();
    }

    private function listarDocumentos(?int $ano = null, ?int $tipo = null, ?string $busca = null) {
        return Documento::with(['tipo', 'usuario'])
            ->where('gabinete_id', $this->usuario['gabinete_id'])

            ->when($ano !== null, function ($query) use ($ano) {
                return $query->where('ano', $ano);
            })

            ->when($tipo !== null, function ($query) use ($tipo) {
                return $query->where('tipo_documento_id', $tipo);
            })

            ->when(!empty($busca), function ($query) use ($busca) {
                return $query->where(function ($q) use ($busca) {
                    $q->where('titulo', 'like', "%{$busca}%")
                        ->orWhere('resumo', 'like', "%{$busca}%");
                });
            })

            ->orderBy('titulo', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function indexDocumentos(Request $request, Response $response): Response {
        try {

            $query = $request->getQueryParams();

            if (!array_key_exists('ano', $query)) {
                $ano = (int) date('Y');
            } elseif (trim($query['ano']) === '') {
                $ano = null;
            } else {
                $ano = (int) $query['ano'];
            }

            $tipo = isset($query['tipo']) && trim($query['tipo']) !== '' ? (int) $query['tipo'] : null;
            $busca = isset($query['busca']) && trim($query['busca']) !== '' ? trim($query['busca']) : null;

            $documentos = $this->listarDocumentos($ano, $tipo, $busca);

            $payload['tipos'] = $this->listarTipos();
            $payload['documentos'] = $documentos;
            $payload['anoGet'] = $ano;
            $payload['tipoGet'] = $tipo;

            return $this->renderView($request, $response, self::VIEW_DOCUMENTOS, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_DOCUMENTOS, $this->getFlash());
        }
    }

    public function newDocumentos(Request $request, Response $response): Response {
        if (!in_array($this->usuario['nivel'], [1, 3])) {
            $this->flash('info', 'Você não tem autorização para inserir um documento');
            return $this->redirect($response, self::VIEW_ROUTE);
        }

        try {
            $dados = $request->getParsedBody();
            $arquivos = $request->getUploadedFiles();

            $arquivoUrl = null;

            if (isset($arquivos['arquivo']) && $arquivos['arquivo']->getError() === UPLOAD_ERR_OK) {

                $nomeArquivo = $arquivos['arquivo']->getClientFilename();
                $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

                if (!in_array($extensao, self::EXTENSOES_PERMITIDAS)) {
                    $this->flash('info', 'Tipo de arquivo não permitido');
                    return $this->redirect($response, self::VIEW_ROUTE);
                }

                $arquivoUrl = UploadHelper::processar($arquivos['arquivo'], 'documentos/');
            }

            $tipo = TipoDocumento::find($dados['tipo_documento_id']);

            $titulo = $tipo->sigla . ' ' . $dados['numero'] . '/' . $dados['ano'];

            $documento = Documento::where([
                'titulo' => $titulo,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->first();

            if ($documento) {
                $this->flash('info', 'Esse documento já está cadastrado');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            Documento::create([
                'titulo' => $titulo,
                'resumo' => $dados['resumo'],
                'numero' => $dados['numero'],
                'ano' => $dados['ano'],
                'tipo_documento_id' => $dados['tipo_documento_id'],
                'gabinete_id' => $this->usuario['gabinete_id'],
                'usuario_id' => $this->usuario['id'],
                'arquivo_url' => $arquivoUrl
            ]);

            $this->flash('success', 'Documento arquivado com sucesso');

            return $this->redirect($response, self::VIEW_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE);
        }
    }



}
