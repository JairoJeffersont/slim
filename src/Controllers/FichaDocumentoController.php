<?php

namespace App\Controllers;

use App\Helpers\UploadHelper;
use App\Models\Documento;
use App\Models\TipoDocumento;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;

class FichaDocumentoController extends BaseController {
    private const VIEW_FICHA_DOCUMENTO = 'pages/documentos/ficha_documento.twig';
    private const VIEW_ROUTE = '/documentos';
    private const EXTENSOES_PERMITIDAS = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    private function listarTipos() {
        return TipoDocumento::with('usuario')->where('gabinete_id', $this->usuario['gabinete_id'])->orderBy('nome')->get();
    }

    private function buscarDocumento(int $id): ?Documento {
        return Documento::with('tipo')
            ->with('usuario')
            ->where([
                'id' => $id,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])
            ->first();
    }

    public function indexDocumento(Request $request, Response $response, array $args): Response {

        try {

            $id = $args['id'];
            $payload['tipos'] = $this->listarTipos();
            $payload['documento'] = $this->buscarDocumento($id);

            return $this->renderView($request, $response, self::VIEW_FICHA_DOCUMENTO, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_FICHA_DOCUMENTO, $this->getFlash());
        }
    }

    public function updateDocumentos(Request $request, Response $response, array $params): Response {

        $documento = null;

        try {
            $documento = Documento::where([
                'id' => $params['id'],
                'gabinete_id' => $this->usuario['gabinete_id']
            ])->first();

            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para editar um documento');
                return $this->redirect($response, self::VIEW_ROUTE . '/' . $params['id']);
            }

            if (!$documento) {
                $this->flash('info', 'Documento não encontrado');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            $dados = $request->getParsedBody();
            $arquivos = $request->getUploadedFiles();

            $arquivoAntigo = $documento->arquivo_url;
            $arquivoUrl = $arquivoAntigo;

            if (isset($arquivos['arquivo']) && $arquivos['arquivo']->getError() === UPLOAD_ERR_OK) {

                $nomeArquivo = $arquivos['arquivo']->getClientFilename();
                $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

                if (!in_array($extensao, self::EXTENSOES_PERMITIDAS)) {
                    $this->flash('info', 'Tipo de arquivo não permitido');
                    return $this->redirect($response, self::VIEW_ROUTE . '/' . $documento->id);
                }

                $arquivoUrl = UploadHelper::processar($arquivos['arquivo'], 'documentos/'.$this->usuario['gabinete_id']);
            }

            $tipo = TipoDocumento::find($dados['tipo_documento_id']);

            $titulo = $tipo->sigla . ' ' . $dados['numero'] . '/' . $dados['ano'];

            $documentoExistente = Documento::where('titulo', $titulo)
                ->where('gabinete_id', $this->usuario['gabinete_id'])
                ->where('id', '!=', $documento->id)
                ->first();

            if ($documentoExistente) {
                $this->flash('info', 'Esse documento já está cadastrado');
                return $this->redirect($response, self::VIEW_ROUTE . '/' . $documento->id);
            }

            $documento->update([
                'titulo' => $titulo,
                'resumo' => $dados['resumo'],
                'numero' => $dados['numero'],
                'ano' => $dados['ano'],
                'tipo_documento_id' => $dados['tipo_documento_id'],
                'arquivo_url' => $arquivoUrl
            ]);

            if ($arquivoAntigo && $arquivoAntigo !== $arquivoUrl) {
                UploadHelper::remover($arquivoAntigo);
            }

            $this->flash('success', 'Documento atualizado com sucesso');

            return $this->redirect($response, self::VIEW_ROUTE . '/' . $documento->id);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE . '/' . ($documento->id ?? $params['id']));
        }
    }


    public function apagarDocumento(Request $request, Response $response, array $args): Response {

        $id = $args['id'];

        try {

            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para apagar');
                return $this->redirect($response, self::VIEW_ROUTE . '/' . $id);
            }

            $documento = $this->buscarDocumento($id);

            if (!$documento) {
                $this->flash('info', 'Documento não encontrado');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            $arquivoUrl = $documento->arquivo_url;

            $documento->delete();

            if ($arquivoUrl) {
                UploadHelper::remover($arquivoUrl);
            }

            $this->flash('success', 'Documento apagado com sucesso');

            return $this->redirect($response, self::VIEW_ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE . '/' . $id);
        }
    }

    private function removerArquivo(?string $arquivoUrl): void {
        if (!$arquivoUrl) {
            return;
        }

        $caminho = parse_url($arquivoUrl, PHP_URL_PATH);

        if ($caminho) {
            $arquivo = $_SERVER['DOCUMENT_ROOT'] . $caminho;

            if (file_exists($arquivo)) {
                unlink($arquivo);
            }
        }
    }
}
