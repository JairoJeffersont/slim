<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class MailHelper {

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
            throw($e);
        }
    }
}
