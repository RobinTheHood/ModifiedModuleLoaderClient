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

namespace RobinTheHood\ModifiedModuleLoaderClient\Cli;

class HelpRenderer
{
    private const INDENT = '  ';
    private string $description = '';
    private array $sections = [];

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setUsage(string $command, string $description): void
    {
        $this->sections['usage'][$command] = $description;
    }

    public function addArgument(string $argument, string $description): void
    {
        $this->sections['arguments'][$argument] = $description;
    }

    public function addOption(string $short, string $long, string $description): void
    {
        $option = $short . $long;

        $this->sections['options'][$option] = [
            'short' => $short,
            'long' => $long,
            'description' => $description,
        ];
    }

    public function render(): string
    {
        $render = '';

        $render .= $this->renderDescription();
        $render .= $this->renderUsage();
        $render .= $this->renderArguments();
        $render .= $this->renderOptions();

        return $render;
    }

    private function renderDescription(): string
    {
        $description = \PHP_EOL;
        $description .= TextRenderer::color('Description:', TextRenderer::COLOR_YELLOW) . \PHP_EOL;
        $description .= self::INDENT . $this->description . \PHP_EOL;

        return $description;
    }

    private function renderUsage(): string
    {
        if (empty($this->sections['usage'])) {
            return '';
        }

        $items = \array_keys($this->sections['usage']);
        $maxLength = TextRenderer::getMaxLength($items) + 1;

        $usage = \PHP_EOL;
        $usage .= TextRenderer::color('Usage:', TextRenderer::COLOR_YELLOW) . \PHP_EOL;

        foreach ($this->sections['usage'] as $argument => $argumentDescription) {
            $name = self::INDENT . TextRenderer::rightPad($argument, $maxLength);
            $text = TextRenderer::color($name, TextRenderer::COLOR_GREEN) . $argumentDescription . \PHP_EOL;

            $usage .= $text;
        }

        return $usage;
    }

    private function renderArguments(): string
    {
        if (empty($this->sections['arguments'])) {
            return '';
        }

        $items = \array_keys($this->sections['arguments']);
        $maxLength = TextRenderer::getMaxLength($items) + 1;

        $arguments = \PHP_EOL;
        $arguments .= TextRenderer::color('Arguments:', TextRenderer::COLOR_YELLOW) . \PHP_EOL;

        foreach ($this->sections['arguments'] as $argumentName => $argumentDescription) {
            $name = self::INDENT . TextRenderer::rightPad($argumentName, $maxLength);
            $text = TextRenderer::color($name, TextRenderer::COLOR_GREEN) . $argumentDescription . \PHP_EOL;

            $arguments .= $text;
        }

        return $arguments;
    }

    private function renderOptions(): string
    {
        if (empty($this->sections['options'])) {
            return '';
        }

        $arguments = \PHP_EOL;
        $arguments .= TextRenderer::color('Options:', TextRenderer::COLOR_YELLOW) . \PHP_EOL;

        $padLeftLength = self::getMaxLengthOptionsLeft($this->sections['options']) + 1;
        $padRightLength = self::getMaxLengthOptionsRight($this->sections['options']) + 1;

        foreach ($this->sections['options'] as $option) {
            $name = TextRenderer::leftPad('', $padLeftLength);

            if ($option['short'] && $option['long']) {
                $short = '-' . $option['short'];
                $long = '--' . $option['long'];

                $name = TextRenderer::leftPad($short . ', ' . $long, 2);
            } elseif ($option['short']) {
                $short = TextRenderer::rightPad('-' . $option['short'], 3);

                $name = $short;
            } elseif ($option['long']) {
                $long = TextRenderer::rightPad('--' . $option['long'], 3);
                $name = TextRenderer::leftPad($long, $padLeftLength);
            }

            $name = TextRenderer::rightPad($name, $padRightLength);
            $text = self::INDENT . TextRenderer::color($name, TextRenderer::COLOR_GREEN) . $option['description'] . \PHP_EOL;

            $arguments .= $text;
        }

        return $arguments;
    }

    private static function optionExists(array $options, string $shortOrLong): bool {
        foreach ($options as $option) {
            if ($option[$shortOrLong]) {
                return true;
            }
        }

        return false;
    }

    private static function getMaxLengthOptionsLeft(array $options): int
    {
        $maxLength = 0;

        $shortOptionExists = self::optionExists($options, 'short');
        $longOptionExists = self::optionExists($options, 'long');

        foreach ($options as $option) {
            $shortLength = \mb_strlen($option['short']);
            $longLength = \mb_strlen($option['long']);
            $currentLength = max($shortLength, $longLength);

            $maxLength = \max($maxLength, $currentLength);
        }

        if ($shortOptionExists && $longOptionExists) {
            $maxLength += 5;
        } elseif ($shortOptionExists) {
            $maxLength += 1;
        } elseif ($longOptionExists) {
            return 0;
        }

        return $maxLength;
    }

    private static function getMaxLengthOptionsRight(array $options): int
    {
        $maxLength = 0;

        $shortOptionExists = self::optionExists($options, 'short');
        $longOptionExists = self::optionExists($options, 'long');

        foreach ($options as $option) {
            $shortLength = \mb_strlen($option['short']);
            $longLength = \mb_strlen($option['long']);
            $currentLength = max($shortLength, $longLength);

            $maxLength = \max($maxLength, $currentLength);
        }

        if ($shortOptionExists && $longOptionExists) {
            $maxLength += 6;
        } elseif ($shortOptionExists) {
        } elseif ($longOptionExists) {
            $maxLength += 3;
        }

        return $maxLength;
    }
}
