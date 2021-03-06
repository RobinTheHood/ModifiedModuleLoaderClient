<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\App;
use Psr\Http\Message\ServerRequestInterface;

abstract class Controller
{
    protected $serverRequest;

    public function __construct(ServerRequestInterface $serverRequest)
    {
        $this->serverRequest = $serverRequest;
    }

    protected function isPostRequest(): bool
    {
        return $this->serverRequest->getMethod() == 'POST';
    }

    protected function getAction(): string
    {
        $queryParams = $this->serverRequest->getQueryParams();
        return $queryParams['action'] ?? '';
    }

    protected function render(string $templateName, array $data = []): array
    {
        $path = App::getTemplatesRoot() . '/' . $templateName . '.tmpl.php';
        if (!is_readable($path)) {
            throw new \Exception("Templatefile $path not found!", 1);
        }

        ob_start();
        extract($data, EXTR_SKIP);
        require $path;
        $content = ob_get_contents();
        ob_end_clean();
        
        return [
            'templateName' => $templateName,
            'templatePath' => $path,
            'data' => $data,
            'content' => $content
        ];
    }
}
