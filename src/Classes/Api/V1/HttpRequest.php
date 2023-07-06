<?php

declare(strict_types=1);

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\Api\V1;

use RuntimeException;
use RobinTheHood\ModifiedModuleLoaderClient\Logger\LogLevel;
use RobinTheHood\ModifiedModuleLoaderClient\Logger\StaticLogger;

class HttpRequest
{
    public function isServerAvailable(string $url): bool
    {
        $headers = @get_headers($url);
        if ($headers) {
            return true;
        }
        return false;
    }

    /**
     * @throws RuntimeException
     */
    public function sendPostRequest(string $url, $data): string
    {
        //$result = $this->sendCurlPostRequest($url, $data);
        $result = $this->sendFileGetContentsPostRequest($url, $data);
        return $result;
    }


    /**
     * @throws RuntimeException
     */
    public function sendGetRequest(string $url): string
    {
        //$result = $this->sendCurlGetRequest($url);
        $result = $this->sendFileGetContentsGetRequest($url);
        return $result;
    }

    public static function createQuery(array $queryValues): string
    {
        $query = '';
        foreach ($queryValues as $name => $value) {
            $query .= $name . '=' . urlencode((string) $value) . '&';
        }
        return $query;
    }

    /**
     * @throws RuntimeException
     */
    private function sendCurlGetRequest(string $url): string
    {
        // HTTP GET-Request konfigurieren
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Modified Module Loader Client');

        StaticLogger::log(
            LogLevel::DEBUG,
            "Send GET request to $url"
        );

        // Request senden und Ergebnis erhalten
        $result = curl_exec($curl);

        // HTTP-Statuscode und Fehler prüfen
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($result === false) {
            $error = curl_error($curl);
            curl_close($curl);
            StaticLogger::log(LogLevel::ERROR, "$httpCode Error-Response from $url\n$error");
            throw new RuntimeException('Error sending the GET request: ' . $error);
        } elseif ($httpCode < 200 || $httpCode >= 300) {
            curl_close($curl);
            StaticLogger::log(LogLevel::ERROR, "$httpCode Error-Response from $url");
            throw new RuntimeException('Error sending the GET request: HTTP-Statuscode ' . $httpCode);
        }

        // Request beenden
        curl_close($curl);

        StaticLogger::log(LogLevel::DEBUG, "$httpCode Response from $url\n" . print_r($result, true));

        return (string) $result;
    }

    /**
     * @throws RuntimeException
     */
    private function sendCurlPostRequest(string $url, $data): string
    {
        // HTTP POST-Request konfigurieren
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Modified Module Loader Client');

        StaticLogger::log(
            LogLevel::DEBUG,
            "Send POST request to $url\n[DATA]\n" . print_r($data, true)
        );

        // Request senden und Ergebnis erhalten
        $result = curl_exec($curl);

        // HTTP-Statuscode und Fehler prüfen
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($result === false) {
            $error = curl_error($curl);
            curl_close($curl);
            StaticLogger::log(LogLevel::ERROR, "$httpCode Error-Response from $url\n$error");
            throw new RuntimeException('Error sending the POST request: ' . $error);
        } elseif ($httpCode < 200 || $httpCode >= 300) {
            curl_close($curl);
            StaticLogger::log(LogLevel::ERROR, "$httpCode Error-Response from $url");
            throw new RuntimeException('Error sending the POST request: HTTP-Statuscode ' . $httpCode);
        }

        // Request beenden
        curl_close($curl);

        return (string) $result;
    }

    /**
     * @throws RuntimeException
     */
    private function sendFileGetContentsPostRequest(string $url, $data): string
    {
        // http verwenden, auch wenn die Url mit https://... beginnt
        $options = [
            'http' => [
                'user_agent' => 'Modified Module Loader Client',
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Content-type: application/x-www-form-urlencoded'
                ]),
                'content' => http_build_query($data)
            ]
        ];
        $context  = stream_context_create($options);

        StaticLogger::log(
            LogLevel::DEBUG,
            "Send POST request to $url\n[OPTIONS]\n" . print_r($options, true) . "[DATA]\n" . print_r($data, true)
        );

        $timeBeforeRequest = microtime(true);

        $result = @file_get_contents($url, false, $context);
        if ($result === false) {
            $error = error_get_last();
            $errorMessage = $error['message'] ?? '';
            StaticLogger::log(LogLevel::ERROR, "Error-Response from $url\n$errorMessage");
            throw new RuntimeException('Error sending the POST request: ' . $errorMessage);
        }

        $time = microtime(true) - $timeBeforeRequest;

        StaticLogger::log(LogLevel::DEBUG, "Response from $url ($time sec)\n" . print_r($result, true));

        return $result;
    }

    /**
     * @throws RuntimeException
     */
    private function sendFileGetContentsGetRequest(string $url): string
    {
        // http verwenden, auch wenn die Url mit https://... beginnt
        $options = [
            'http' => [
                'user_agent' => 'Modified Module Loader Client',
                'method' => "GET",
                'header' => implode("\r\n", [
                    'Content-type: text/plain'
                ])
            ]
        ];

        $context = stream_context_create($options);

        StaticLogger::log(
            LogLevel::DEBUG,
            "Send GET request to $url\n[OPTIONS]\n" . print_r($options, true)
        );

        $result = @file_get_contents($url, false, $context);
        if ($result === false) {
            $error = error_get_last();
            $errorMessage = $error['message'] ?? '';
            StaticLogger::log(LogLevel::ERROR, "Error-Response from $url\n$errorMessage");
            throw new RuntimeException('Error sending the GET request: ' . $errorMessage);
        }

        StaticLogger::log(LogLevel::DEBUG, "Response from $url\n" . print_r($result, true));

        return $result;
    }
}
