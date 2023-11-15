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
        $formatted = '';

        if ($short && $long) {
            $formatted = "-$short, --$long";
        } elseif ($short) {
            $formatted = "-$short";
        } elseif ($long) {
            $formatted = "    --$long";
        }

        $this->sections['options'][$option] = [
            'short' => $short,
            'long' => $long,
            'formatted' => $formatted,
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

        $optionsFormatted = \array_map(
            function ($option) {
                return $option['formatted'];
            },
            $this->sections['options']
        );
        $optionsPadding = TextRenderer::getMaxLength($optionsFormatted) + 1;

        foreach ($this->sections['options'] as $option) {
            $name = TextRenderer::rightPad($option['formatted'], $optionsPadding);
            $text = self::INDENT . TextRenderer::color($name, TextRenderer::COLOR_GREEN) . $option['description'] . \PHP_EOL;

            $arguments .= $text;
        }

        return $arguments;
    }
}
