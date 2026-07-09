<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use JairoJeffersont\EasyLogger\Logger;

/**
 * Classe MailHelper
 *
 * Classe utilitária (Helper) responsável pela centralização, configuração
 * e disparo de e-mails transacionais do sistema utilizando a biblioteca PHPMailer via SMTP.
 *
 * @package App\Helpers
 */
class MailHelper {

    /**
     * Inicializa e configura uma nova instância do PHPMailer.
     * * O método extrai as credenciais SMTP, portas e dados de remetente
     * diretamente das variáveis de ambiente (`$_ENV`). Define também o charset para UTF-8
     * e ativa a criptografia STARTTLS por padrão.
     *
     * @return PHPMailer Instância do PHPMailer pronta para o envio.
     * @throws Exception Caso ocorra algum erro interno na inicialização dos parâmetros do PHPMailer.
     */
    private static function configurar(): PHPMailer {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->Port = (int) $_ENV['SMTP_PORT'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->CharSet = 'UTF-8';

        $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);

        return $mail;
    }

    /**
     * Dispara um e-mail em formato HTML para o destinatário especificado.
     * * Caso o disparo falhe, a exceção é capturada, gravada nos arquivos de log
     * do sistema e um array formatado com o ID do erro é retornado para rastreabilidade.
     *
     * @param string $destinatario Endereço de e-mail de quem vai receber (ex: 'usuario@email.com').
     * @param string $nome Nome completo do destinatário.
     * @param string $assunto O título/assunto do e-mail.
     * @param string $mensagem O corpo do e-mail (aceita tags HTML).
     * @return array Array associativo indicando o resultado da operação:
     * * Em caso de sucesso: `['status' => 'success', 'message' => '...']`
     * * Em caso de erro: `['status' => 'error', 'message' => '...', 'error_id' => '...']`
     */
    public static function enviar(string $destinatario, string $nome, string $assunto, string $mensagem): array {
        try {
            $mail = self::configurar();

            $mail->addAddress($destinatario, $nome);
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $mensagem;
            $mail->AltBody = strip_tags($mensagem);

            $mail->send();

            return ['status' => 'success', 'message' => 'E-mail enviado com sucesso'];
        } catch (Exception $e) {
            $errorId = Logger::newLog('../logs', 'MAIL', $e->getMessage(), 'ERROR');
            return ['status' => 'error', 'message' => $e->getMessage(), 'error_id' => $errorId];
        }
    }
}
