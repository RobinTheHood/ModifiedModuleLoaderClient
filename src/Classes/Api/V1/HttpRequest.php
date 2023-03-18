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

use Exception;
use RobinTheHood\ModifiedModuleLoaderClient\App;

class HttpRequest
{
    private $logging = false;

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
        try {
            // HTTP POST-Request konfigurieren und senden
            $result = $this->sendCurlPostRequest($url, $data);

            // Logging
            if ($this->logging) {
                $logFilepath = App::getLogsRoot() . '/log.txt';
                $logDirectory = dirname($logFilepath);

                if (!file_exists($logDirectory)) {
                    mkdir($logDirectory);
                }

                file_put_contents($logFilepath, $result);
            }

            return $result;
        } catch (Exception $e) {
            // Fehler behandeln
            //error_log($e->getMessage());
            return '';
        }
    }


    public function sendGetRequest(string $url): string
    {
        try {
            // HTTP GET-Request konfigurieren und senden
            $result = $this->sendCurlGetRequest($url);

            // Logging
            if ($this->logging) {
                $logFilepath = App::getLogsRoot() . '/log.txt';
                $logDirectory = dirname($logFilepath);

                if (!file_exists($logDirectory)) {
                    mkdir($logDirectory);
                }

                file_put_contents($logFilepath, $result);
            }

            return $result;
        } catch (Exception $e) {
            // Fehler behandeln
            //error_log($e->getMessage());
            return '';
        }
    }

    public static function createQuery(array $queryValues): string
    {
        $query = '';
        foreach ($queryValues as $name => $value) {
            $query .= $name . '=' . urlencode($value) . '&';
        }
        return $query;
    }

    private function sendCurlGetRequest(string $url)
    {
        // HTTP GET-Request konfigurieren
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Modified Module Loader Client');

        // Request senden und Ergebnis erhalten
        $result = curl_exec($curl);

        // HTTP-Statuscode und Fehler prüfen
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($result === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception('Fehler beim Senden des GET-Requests: ' . $error);
        } elseif ($httpCode < 200 || $httpCode >= 300) {
            curl_close($curl);
            throw new Exception('Fehler beim Senden des GET-Requests: HTTP-Statuscode ' . $httpCode);
        }

        // Request beenden
        curl_close($curl);

        return $result;
    }

    private function sendCurlPostRequest(string $url, $data)
    {
        // HTTP POST-Request konfigurieren
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Modified Module Loader Client');

        // Request senden und Ergebnis erhalten
        $result = curl_exec($curl);

        // HTTP-Statuscode und Fehler prüfen
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($result === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception('Fehler beim Senden des POST-Requests: ' . $error);
        } elseif ($httpCode < 200 || $httpCode >= 300) {
            curl_close($curl);
            throw new Exception('Fehler beim Senden des POST-Requests: HTTP-Statuscode ' . $httpCode);
        }

        // Request beenden
        curl_close($curl);

        return $result;
    }
}
