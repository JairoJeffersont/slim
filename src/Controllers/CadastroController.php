<?php

namespace App\Controllers;

use App\Models\TipoGabinete;
use App\Models\Gabinete;
use App\Models\Usuario;
use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Classe CadastroController
 *
 * Responsável por gerenciar o fluxo de exibição e processamento do formulário
 * de novos gabinetes e seus respectivos usuários administradores.
 *
 * @package App\Controllers
 */
class CadastroController extends BaseController {

    /**
     * @var string Caminho relativo do template Twig para a página de cadastro.
     */
    private const VIEW = 'pages/cadastro/form_cadastro.twig';

    /**
     * Exibe a página inicial com o formulário de cadastro.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @return Response A resposta HTTP contendo a view do formulário renderizada.
     */
    public function index(Request $request, Response $response): Response {
        try {
            return $this->renderForm($request, $response);
        } catch (Exception $e) {
            return $this->renderServerError($request, $response, self::VIEW, $e, 'CadastroController@index');
        }
    }

    /**
     * Processa a submissão do formulário, valida os dados e cria as entidades no banco.
     *
     * Executa uma transação de banco de dados (Database Transaction) para garantir a consistência
     * entre a criação concomitante do Gabinete e do Usuário.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @return Response A resposta HTTP (renderização de sucesso ou de erro de validação/servidor).
     */
    public function cadastro(Request $request, Response $response): Response {
        try {
            $dados = $this->input($request);

            $erro = $this->validarCadastro($dados);
            if ($erro) {
                return $this->renderForm($request, $response, $this->info($erro, [
                    'dados' => $dados
                ]));
            }

            DB::beginTransaction();

            $gabinete = Gabinete::create([
                'id_parlamentar'    => $dados['id_parlamentar'],
                'nome'              => $dados['nome_parlamentar'],
                'token'             => uniqid(),
                'estado'            => $dados['uf'],
                'ativo'             => 1,
                'tipo_gabinete_id'  => $dados['tipo_gabinete_id']
            ]);

            Usuario::create([
                'nome'            => $dados['nome'],
                'email'           => $dados['email'],
                'senha'           => password_hash($dados['senha'], PASSWORD_DEFAULT),
                'ativo'           => 1,
                'tipo_usuario_id' => 1,
                'gabinete_id'     => $gabinete->id
            ]);

            DB::commit();

            return $this->renderForm($request, $response, $this->success('Gabinete cadastrado com sucesso.'));
        } catch (Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return $this->renderServerError($request, $response, self::VIEW, $e, 'CadastroController@cadastro');
        }
    }

    /**
     * Renderiza o formulário de cadastro injetando a listagem de tipos de gabinete necessária para o select.
     *
     * @param Request $request O objeto de requisição HTTP PSR-7.
     * @param Response $response O objeto de resposta HTTP PSR-7.
     * @param array $data Dados adicionais (mensagens de erro, sucesso, ou inputs antigos) a serem mesclados na view.
     * @return Response A resposta HTTP com o template renderizado.
     */
    private function renderForm(Request $request, Response $response, array $data = []): Response {
        return $this->render($request, $response, self::VIEW, array_merge([
            'tipos_gabinete' => TipoGabinete::all()->toArray()
        ], $data));
    }

    /**
     * Valida os dados recebidos da requisição antes de efetuar o cadastro.
     *
     * Verifica campos obrigatórios ausentes ou vazios, além de checar a existência prévia
     * do id do parlamentar ou do email no banco de dados.
     *
     * @param array $dados Dados vindos do corpo da requisição POST ($request->getParsedBody()).
     * @return string|null Retorna a mensagem de erro textual caso falte algo, ou null se os dados forem válidos.
     */
    private function validarCadastro(array $dados): ?string {
        $camposObrigatorios = [
            'id_parlamentar',
            'email',
            'senha',
            'nome_parlamentar',
            'uf',
            'tipo_gabinete_id',
            'nome'
        ];

        foreach ($camposObrigatorios as $campo) {
            if (empty($dados[$campo])) {
                return "O campo {$campo} é obrigatório.";
            }
        }

        if (Gabinete::where('id_parlamentar', $dados['id_parlamentar'])->exists()) {
            return 'Esse gabinete já está cadastrado';
        }

        if (Usuario::where('email', $dados['email'])->exists()) {
            return 'Esse usuário já está cadastrado';
        }

        return null;
    }
}
