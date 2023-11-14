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
    private array $arguments = [];

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setUsage(string $command, string $description): void
    {
        $this->arguments['usage'][$command] = $description;
    }

    public function addArgument(string $argument, string $description): void
    {
        $this->arguments['arguments'][$argument] = $description;
    }

    public function addOption(string $short, string $long, string $description): void
    {
        $options = [$short, $long];

        /** Discard empty options */
        $options = \array_filter(
            $options,
            function ($option) {
                return !empty($option);
            }
        );

        /** Add dash (`-`) to remaining options */
        $options = \array_map(
            function ($value) {
                switch (\mb_strlen($value)) {
                    case 1:
                        return '-' . $value;
                        break;

                    default:
                        return '--' . $value;
                        break;
                }
            },
            $options
        );

        $combined = \implode(', ', $options);

        if (empty($short)) {
            $combined = '    ' . $combined;
        }

        $this->arguments['options'][$combined] = $description;
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
        if (empty($this->arguments['usage'])) {
            return '';
        }

        $items = \array_keys($this->arguments['usage']);
        $maxLength = TextRenderer::getMaxLength($items) + 1;

        $usage = \PHP_EOL;
        $usage .= TextRenderer::color('Usage:', TextRenderer::COLOR_YELLOW) . \PHP_EOL;

        foreach ($this->arguments['usage'] as $argument => $argumentDescription) {
            $name = self::INDENT . TextRenderer::rightPad($argument, $maxLength);
            $text = TextRenderer::color($name, TextRenderer::COLOR_GREEN) . $argumentDescription . \PHP_EOL;

            $usage .= $text;
        }

        return $usage;
    }

    private function renderArguments(): string
    {
        if (empty($this->arguments['arguments'])) {
            return '';
        }

        $items = \array_keys($this->arguments['arguments']);
        $maxLength = TextRenderer::getMaxLength($items) + 1;

        $arguments = \PHP_EOL;
        $arguments .= TextRenderer::color('Arguments:', TextRenderer::COLOR_YELLOW) . \PHP_EOL;

        foreach ($this->arguments['arguments'] as $argumentName => $argumentDescription) {
            $name = self::INDENT . TextRenderer::rightPad($argumentName, $maxLength);
            $text = TextRenderer::color($name, TextRenderer::COLOR_GREEN) . $argumentDescription . \PHP_EOL;

            $arguments .= $text;
        }

        return $arguments;
    }

    private function renderOptions(): string
    {
        if (empty($this->arguments['options'])) {
            return '';
        }

        $items = \array_keys($this->arguments['options']);
        $maxLength = TextRenderer::getMaxLength($items) + 1;

        $arguments = \PHP_EOL;
        $arguments .= TextRenderer::color('Options:', TextRenderer::COLOR_YELLOW) . \PHP_EOL;

        foreach ($this->arguments['options'] as $argument => $description) {
            $name = self::INDENT . TextRenderer::rightPad($argument, $maxLength);
            $text = TextRenderer::color($name, TextRenderer::COLOR_GREEN) . $description . \PHP_EOL;

            $arguments .= $text;
        }

        return $arguments;
    }
}
