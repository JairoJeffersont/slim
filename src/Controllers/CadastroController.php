<?php

namespace App\Controllers;

use App\Models\TipoGabinete;
use App\Models\Gabinete;
use App\Models\Usuario;
use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CadastroController extends BaseController {
    private const VIEW = 'pages/cadastro/form_cadastro.twig';

    public function index(Request $request, Response $response): Response {
        try {
            return $this->renderForm($request, $response);
        } catch (Exception $e) {
            return $this->renderServerError($request, $response, self::VIEW, $e, 'CadastroController@index');
        }
    }

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

    private function renderForm(Request $request, Response $response, array $data = []): Response {
        return $this->render($request, $response, self::VIEW, array_merge([
            'tipos_gabinete' => TipoGabinete::all()->toArray()
        ], $data));
    }

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
