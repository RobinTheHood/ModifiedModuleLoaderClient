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

class SemverParser
{
    public function parse(string $string): array
    {
        $version = [
            'major' => 0,
            'minor' => 0,
            'patch' => 0
        ];

        $parts = explode('.', $string);
        
        if (count($parts) != 3) {
            throw new ParseErrorException('Can not parse string to version array');
        } elseif ($parts[0] == '' || $parts[1] == '' || $parts[2] == '') {
            throw new ParseErrorException('Some part of version string is empty');
        }

        $version['major'] = (int) $parts[0];
        $version['minor'] = (int) $parts[1];
        $version['patch'] = (int) $parts[2];

        return $version;
    }
}