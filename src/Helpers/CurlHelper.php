<?php

namespace App\Helpers;

class CurlHelper {

    public function request(string $url, string $method = 'GET', array $headers = [], array $body = [], array &$responseHeaders = []): array {
        $curl = curl_init();

        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => array_merge($defaultHeaders, $headers),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$responseHeaders) {
                $length = strlen($header);
                $header = trim($header);

                if (strpos($header, ':') !== false) {
                    [$key, $value] = explode(':', $header, 2);
                    $responseHeaders[trim($key)] = trim($value);
                }

                return $length;
            }
        ];

        if (!empty($body)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        $errorCode = curl_errno($curl);

        if ($errorCode === CURLE_OPERATION_TIMEDOUT) {
            throw new \RuntimeException('A API demorou para responder.');
        }

        if ($error) {
            throw new \RuntimeException("Erro ao buscar dados na API [{$errorCode}]: {$error}");
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException("A API retornou HTTP {$httpCode}.");
        }

        return json_decode($response, true) ?? [];
    }
}
