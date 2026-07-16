<?php

namespace App\Helpers;

use Psr\Http\Message\UploadedFileInterface as UploadedFile;

class UploadHelper {

    public static function processar(UploadedFile $uploadedFile, string $pasta = 'documentos'): string {

        $diretorioUpload = __DIR__ . '/../../public/uploads/' . $pasta;

        if (!is_dir($diretorioUpload)) {
            mkdir($diretorioUpload, 0755, true);
        }

        $extensao = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

        $nomeUnico = sprintf('%s-%s.%s', uniqid(), bin2hex(random_bytes(8)), $extensao);

        $uploadedFile->moveTo($diretorioUpload . DIRECTORY_SEPARATOR . $nomeUnico);

        return '/uploads/' . $pasta . '/' . $nomeUnico;
    }

    public static function remover(?string $arquivoUrl): void {

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
