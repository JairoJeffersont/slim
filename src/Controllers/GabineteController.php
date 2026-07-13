<?php

namespace App\Controllers;

use App\Models\Gabinete;
use App\Models\Usuario;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GabineteController extends BaseController {

    private const VIEW = 'pages/gabinete/gabinete.twig';
    private const ROUTE = '/gabinete';

    private int $LOGGED_USER_ID;


    public function __construct() {
        $this->LOGGED_USER_ID = $_SESSION['usuario']['id'];
    }

    public function index(Request $request, Response $response): Response {
        $payload = [];

        try {

            $usuario = Usuario::find($this->LOGGED_USER_ID);

            if ($usuario->tipo_usuario_id != 1) {
                $this->flash('info', 'Você não tem autorização para acessar essa área');
                return $this->redirect($response, '/dashboard');
            }

            $gabinete = Gabinete::find($usuario->gabinete_id);
            $usuarios = Usuario::where('gabinete_id', $usuario->gabinete_id)->get();

            $payload['gabinete'] = $gabinete;
            $payload['usuarios'] = $usuarios;
            $payload['usuario_logado'] = $this->LOGGED_USER_ID;

            $payload = array_merge($payload, $this->getFlash());
            return $this->renderView($request, $response, self::VIEW, $payload);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW, $this->getFlash());
        }
    }

    public function updateGabinete(Request $request, Response $response): Response {

        try {

            $dados = $request->getParsedBody();

            $usuario = Usuario::find($this->LOGGED_USER_ID);
            $gabinete = Gabinete::find($usuario->gabinete_id);

            $gabinete->update([
                'cidade' => $dados['cidade'],
                'partido' => $dados['partido']
            ]);

            $this->flash('success', 'Gabinete atualizado com sucesso');
            return $this->redirect($response, self::ROUTE);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE);
        }
    }
}
