<?php

namespace App\Controllers;

use App\Helpers\UploadHelper;
use App\Models\Pessoa;
use App\Models\Orgao;
use App\Models\Profissao;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FichaPessoaController extends BaseController {
    private const VIEW_FICHA_PESSOAS = 'pages/pessoas/ficha_pessoa.twig';
    private const VIEW_ROUTE = '/pessoas';
    private const EXTENSOES_PERMITIDAS = ['jpg', 'jpeg', 'png'];


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

    private function buscarPessoa(int $id): ?Pessoa {
        return Pessoa::with(['orgao', 'profissao'])
            ->where([
                'id' => $id,
                'gabinete_id' => $this->usuario['gabinete_id']
            ])
            ->first();
    }

    public function index(Request $request, Response $response, array $args): Response {
        try {
            $id = (int)$args['id'];

            $pessoa = $this->buscarPessoa($id);

            if (!$pessoa) {
                $this->flash('info', 'Pessoa não encontrada');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            $payload['orgaos'] = $this->listarOrgaos();
            $payload['profissoes'] = $this->listarProfissoes();
            $payload['pessoa'] = $pessoa;

            return $this->renderView($request, $response, self::VIEW_FICHA_PESSOAS, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_FICHA_PESSOAS, $this->getFlash());
        }
    }

    public function updatePessoa(Request $request, Response $response, array $args): Response {
        $id = (int)$args['id'];

        if (!in_array($this->usuario['nivel'], [1, 3])) {
            $this->flash('info', 'Você não tem autorização para atualizar um cadastro');
            return $this->redirect($response, self::VIEW_ROUTE . '/' . $id);
        }

        try {
            $pessoa = $this->buscarPessoa($id);

            if (!$pessoa) {
                $this->flash('info', 'Pessoa não encontrada');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            $dados = $request->getParsedBody();

            $arquivos = $request->getUploadedFiles();

            $arquivoAntigo = $pessoa->foto;
            $arquivoUrl = $arquivoAntigo;

            if (isset($arquivos['foto']) && $arquivos['foto']->getError() === UPLOAD_ERR_OK) {

                $nomeArquivo = $arquivos['foto']->getClientFilename();
                $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

                if (!in_array($extensao, self::EXTENSOES_PERMITIDAS)) {
                    $this->flash('info', 'Tipo de arquivo não permitido');
                    return $this->redirect($response, self::VIEW_ROUTE . '/' . $pessoa->id);
                }

                $arquivoUrl = UploadHelper::processar($arquivos['foto'], 'fotos/usuarios/');
            }

            $pessoa->update([
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
                'informacoes' => $dados['informacoes'],
                'foto' => $arquivoUrl ?? $pessoa->foto // Mantém a foto atual caso não seja enviada uma nova
            ]);

            if ($arquivoAntigo && $arquivoAntigo !== $arquivoUrl) {
                UploadHelper::remover($arquivoAntigo);
            }

            $this->flash('success', 'Cadastro atualizado com sucesso');

            return $this->redirect($response, self::VIEW_ROUTE . '/' . $id);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE . '/' . $id);
        }
    }

    public function apagarPessoa(Request $request, Response $response, array $args): Response {
        $id = (int)$args['id'];

        try {
            if (!in_array($this->usuario['nivel'], [1, 3])) {
                $this->flash('info', 'Você não tem autorização para apagar');
                return $this->redirect($response, self::VIEW_ROUTE . '/' . $id);
            }

            $pessoa = $this->buscarPessoa($id);

            if (!$pessoa) {
                $this->flash('info', 'Pessoa não encontrada');
                return $this->redirect($response, self::VIEW_ROUTE);
            }

            $arquivoUrl = $pessoa->foto;

            $pessoa->delete();


            if ($arquivoUrl) {
                UploadHelper::remover($arquivoUrl);
            }


            $this->flash('success', 'Cadastro apagado com sucesso');

            return $this->redirect($response, self::VIEW_ROUTE);
        } catch (Exception $e) {
            if (str_contains(strtolower($e->getMessage()), 'foreign key constraint fails')) {
                $this->flash('error', 'Esta pessoa não pode ser removida porque está vinculada a outros registros');
                return $this->redirect($response, self::VIEW_ROUTE . '/' . $id);
            }

            $this->flashError($e);
            return $this->redirect($response, self::VIEW_ROUTE . '/' . $id);
        }
    }
}
