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
        $option = [
            'short' => TextRenderer::color($short ? '-' . $short : '', TextRenderer::COLOR_GREEN),
            'separator' => TextRenderer::color($short && $long ? ', ' : '', TextRenderer::COLOR_GREEN),
            'long' => TextRenderer::color($long ? '--' . $long : '', TextRenderer::COLOR_GREEN),
            'description' => $description,
        ];

        $this->sections['options'][] = $option;
    }

    public function render(): string
    {
        $render = \array_filter(
            [
                $this->renderDescription(),
                $this->renderUsage(),
                $this->renderArguments(),
                $this->renderOptions(),
            ],
            function (string $renderedText) {
                return !empty($renderedText);
            }
        );

        return \PHP_EOL . \implode(\PHP_EOL, $render);
    }

    private function renderDescription(): string
    {
        $description = TextRenderer::color('Description:', TextRenderer::COLOR_YELLOW) . \PHP_EOL;
        $description .= self::INDENT . $this->description;

        return $description;
    }

    private function renderUsage(): string
    {
        if (empty($this->sections['usage'])) {
            return '';
        }

        $items = \array_keys($this->sections['usage']);
        $maxLength = TextRenderer::getMaxLength($items) + 1;

        $usage = TextRenderer::color('Usage:', TextRenderer::COLOR_YELLOW) . \PHP_EOL;

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

        $arguments = TextRenderer::color('Arguments:', TextRenderer::COLOR_YELLOW) . \PHP_EOL;

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

        $options = TextRenderer::color('Options:', TextRenderer::COLOR_YELLOW) . \PHP_EOL;

        $optionsShort = self::getTableColumn($this->sections['options'], 'short');
        $optionsShortLength = TextRenderer::getMaxLength($optionsShort);

        $optionsSeparator = self::getTableColumn($this->sections['options'], 'separator');
        $optionsSeparatorLength = TextRenderer::getMaxLength($optionsSeparator);

        $optionsLong = self::getTableColumn($this->sections['options'], 'long');
        $optionsLongLength = TextRenderer::getMaxLength($optionsLong);

        $optionsDescription = self::getTableColumn($this->sections['options'], 'description');
        $optionsDescriptionLength = TextRenderer::getMaxLength($optionsDescription);

        foreach ($this->sections['options'] as $option) {
            $short = $option['short'];
            $separator = $option['separator'];
            $long = $option['long'];
            $description = $option['description'];

            $options .= self::INDENT;
            $options .= TextRenderer::rightPad($short, $optionsShortLength);
            $options .= TextRenderer::rightPad($separator, $optionsSeparatorLength);
            $options .= TextRenderer::rightPad($long, $optionsLongLength);
            $options .= ' ';
            $options .= TextRenderer::rightPad($description, $optionsDescriptionLength);
            $options .= \PHP_EOL;
        }

        return $options;
    }

    private static function getTableColumn(array $table, string $column): array {
        $tableColumn = \array_map(
            function ($row) use ($column) {
                return $row[$column];
            },
            $table
        );

        return $tableColumn;
    }
}
