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

    public function sendPostRequest(string $url, $data): string
    {
        $result = $this->sendCurlPostRequest($url, $data);
        return $result;
    }


    public function sendGetRequest(string $url): string
    {
        $result = $this->sendCurlGetRequest($url);
        return $result;
    }

    public static function createQuery(array $queryValues): string
    {
        $query = '';
        foreach ($queryValues as $name => $value) {
            $query .= $name . '=' . urlencode($value) . '&';
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
            throw new RuntimeException('Fehler beim Senden des GET-Requests: ' . $error);
        } elseif ($httpCode < 200 || $httpCode >= 300) {
            curl_close($curl);
            StaticLogger::log(LogLevel::ERROR, "$httpCode Error-Response from $url");
            throw new RuntimeException('Fehler beim Senden des GET-Requests: HTTP-Statuscode ' . $httpCode);
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
            throw new RuntimeException('Fehler beim Senden des POST-Requests: ' . $error);
        } elseif ($httpCode < 200 || $httpCode >= 300) {
            curl_close($curl);
            StaticLogger::log(LogLevel::ERROR, "$httpCode Error-Response from $url");
            throw new RuntimeException('Fehler beim Senden des POST-Requests: HTTP-Statuscode ' . $httpCode);
        }

        // Request beenden
        curl_close($curl);

        return (string) $result;
    }
}
