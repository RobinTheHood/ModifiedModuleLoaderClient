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

namespace RobinTheHood\ModifiedModuleLoaderClient\Helpers;

class GitHelper
{
    public function getCurrentGitBranch(string $gitPath): ?string
    {
        if (!is_dir($gitPath)) {
            return null;
        }

        $os = strtoupper(substr(PHP_OS, 0, 3));
        $command = '';

        switch ($os) {
            case 'WIN':
                $command = 'cd /d "' . $gitPath . '" & git symbolic-ref --short HEAD 2>NUL';
                break;
            case 'LIN':
            case 'DAR':
                $command = 'cd "' . $gitPath . '" && git symbolic-ref --short HEAD 2>/dev/null';
                break;
            default:
                return 'unkown branch';
        }

        $output = trim('' . shell_exec($command));

        if (empty($output)) {
            return null;
        }

        return $output;
    }
}
