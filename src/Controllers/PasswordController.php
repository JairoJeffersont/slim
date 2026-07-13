<?php

namespace App\Controllers;

use App\Helpers\MailHelper;
use App\Models\Usuario;
use Exception;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PasswordController extends BaseController {
    private const VIEW_RECOVER = 'pages/cadastro/esqueci_senha.twig';
    private const VIEW_NEW_PASS = 'pages/cadastro/nova_senha.twig';

    public function formRecover(Request $request, Response $response): Response {
        return $this->renderView($request, $response, self::VIEW_RECOVER, $this->getFlash());
    }

    public function recover(Request $request, Response $response): Response {

        try {

            $dados = $request->getParsedBody();

            if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                $this->flash('info', 'E-mail inválido');
                return $this->renderView($request, $response, self::VIEW_RECOVER, $this->getFlash());
            }

            $usuario = Usuario::where('email', $dados['email'])->first();


            if (!$usuario) {
                $this->flash('info', 'Usuário não encontrado');
                return $this->renderView($request, $response, self::VIEW_RECOVER, $this->getFlash());
            }


            $token = bin2hex(random_bytes(32));

            $usuario->reset_token = $token;
            $usuario->reset_token_expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $usuario->save();

            $link = $_ENV['BASE_URL'] . 'nova-senha/' . $token;

            $mensagem = "
                <div style='background-color: #f8f9fa; padding: 40px 20px; font-family: Arial, sans-serif; text-align: center;'>
                    <div style='max-width: 500px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: left;'>

                        <h2 style='color: #212529; font-size: 22px; margin-top: 0; margin-bottom: 20px; font-weight: 600;'>
                            Recuperação de senha
                        </h2>

                        <p style='color: #495057; font-size: 16px; line-height: 1.5; margin-bottom: 10px;'>
                            Olá, <strong>{$usuario->nome}</strong>,
                        </p>

                        <p style='color: #495057; font-size: 16px; line-height: 1.5; margin-bottom: 25px;'>
                            Recebemos uma solicitação para redefinir a sua senha. Clique no botão abaixo para criar uma nova credencial:
                        </p>

                        <div style='text-align: center; margin-bottom: 25px;'>
                            <a href='{$link}' style='background-color: #198754; color: #ffffff; padding: 12px 30px; text-decoration: none; font-size: 16px; font-weight: bold; border-radius: 50px; display: inline-block; box-shadow: 0 2px 4px rgba(25,135,84,0.2);'>
                                Recuperar senha
                            </a>
                        </div>

                        <hr style='border: 0; border-top: 1px solid #dee2e6; margin-bottom: 20px;'>

                        <p style='color: #6c757d; font-size: 13px; line-height: 1.5; margin-bottom: 0; text-align: center;'>
                            Este link é válido por <strong>1 hora</strong>.<br>
                            Se você não solicitou essa alteração, pode ignorar este e-mail com segurança.
                        </p>

                    </div>
                </div>
                ";

            MailHelper::enviar($usuario->email, $usuario->nome, 'Recuperação de senha', $mensagem);

            $this->flash('success', 'Link enviado com sucesso');
            return $this->renderView($request, $response, self::VIEW_RECOVER, $this->getFlash());
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_RECOVER, $this->getFlash());
        }
    }

    public function formNewPass(Request $request, Response $response, array $args): Response {

        $token = $args['token'];

        $usuario = Usuario::where('reset_token', $token)
            ->where('reset_token_expira', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$usuario) {
            $this->flash('info', 'Link inválido ou expirado.');
            return $this->renderView($request, $response, self::VIEW_NEW_PASS, $this->getFlash());
        }

        return $this->renderView($request, $response, self::VIEW_NEW_PASS, $this->getFlash());
    }

    public function newPass(Request $request, Response $response, array $args): Response {
        try {

            $dados = $request->getParsedBody();
            $token = $args['token'];

            $usuario = Usuario::where('reset_token', $token)
                ->where('reset_token_expira', '>', date('Y-m-d H:i:s'))
                ->first();

            if (!$usuario) {
                $this->flash('info', 'Link inválido ou expirado.');
                return $this->renderView($request, $response, self::VIEW_NEW_PASS, $this->getFlash());
            }

            $usuario->senha = password_hash($dados['senha'], PASSWORD_DEFAULT);
            $usuario->reset_token = null;
            $usuario->reset_token_expira = null;
            $usuario->save();

            $this->flash('success', 'Senha alterada com sucesso');
            return $this->renderView($request, $response, self::VIEW_NEW_PASS, $this->getFlash());
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW_RECOVER, $this->getFlash());
        }
    }
}
