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

namespace RobinTheHood\ModifiedModuleLoaderClient;

class SystemCheck
{
    public const RESULT_PASSED = 'passed';
    public const RESULT_FAILED = 'failed';

    public const REQUIRED_PHP_VERSION = '7.4.0';

    public function check(array $options = []): array
    {
        $php = [
            'is' => PHP_VERSION,
            'require' => '>=' . self::REQUIRED_PHP_VERSION,
        ];

        if (version_compare(PHP_VERSION, self::REQUIRED_PHP_VERSION, '<')) {
            $php['result'] = self::RESULT_FAILED;
            $result = self::RESULT_FAILED;
        } else {
            $php['result'] = self::RESULT_PASSED;
            $result = self::RESULT_PASSED;
        }

        return [
            'result' => $result,
            'checks' => [
                'php' =>  $php
            ]
        ];
    }
}
