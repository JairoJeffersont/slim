<?php

namespace App\Controllers;

use App\Models\Gabinete;
use App\Models\TipoGabinete;
use App\Models\Usuario;
use Exception;
use Illuminate\Database\Capsule\Manager as DB;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CadastroController extends BaseController {
    private const VIEW = 'pages/cadastro/form_cadastro.twig';
    private const ROUTE = '/cadastro';

    public function index(Request $request, Response $response): Response {
        $payload = [];

        try {
            $payload['tipos_gabinete'] = TipoGabinete::all();
            $payload = array_merge($payload, $this->getFlash());
        } catch (Exception $e) {
            $payload = $this->errorResponse($e);
        }

        return $this->renderView($request, $response, self::VIEW, $payload);
    }

    public function cadastro(Request $request, Response $response): Response {
        try {

            $dados = $request->getParsedBody();

            $camposObrigatorios = ['id_parlamentar', 'nome_parlamentar', 'uf', 'tipo_gabinete_id', 'nome', 'email', 'senha'];

            foreach ($camposObrigatorios as $campo) {
                if (!isset($dados[$campo]) || trim((string) $dados[$campo]) === '') {
                    $this->flash('info', 'Todos os campos são obrigatórios');
                    return $this->redirect($response, self::ROUTE);
                }
            }

            if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                $this->flash('info', 'E-mail inválido');
                return $this->redirect($response, self::ROUTE);
            }

            if (Gabinete::where('id_parlamentar', $dados['id_parlamentar'])->first()) {
                $this->flash('info', 'Esse gabinete já está cadastrado');
                return $this->redirect($response, self::ROUTE);
            }

            if (Usuario::where('email', $dados['email'])->first()) {
                $this->flash('info', 'Esse usuário já está cadastrado');
                return $this->redirect($response, self::ROUTE);
            }

            DB::beginTransaction();

            $gabinete = Gabinete::create([
                'id_parlamentar' => $dados['id_parlamentar'],
                'nome' => $dados['nome_parlamentar'],
                'partido' => $dados['partido'],
                'token' => uniqid(),
                'estado' => $dados['uf'],
                'ativo' => 1,
                'assinaturas' => 1,
                'tipo_gabinete_id' => $dados['tipo_gabinete_id']
            ]);

            Usuario::create([
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => password_hash($dados['senha'], PASSWORD_DEFAULT),
                'ativo' => 1,
                'tipo_usuario_id' => 1,
                'gabinete_id' => $gabinete->id
            ]);

            DB::commit();

            $this->flash('success', 'Gabinete cadastrado com sucesso');

            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            DB::rollBack();
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }
}
