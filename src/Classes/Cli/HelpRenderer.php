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
    private array $arguments = array();

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function addArgument(string $section, string $name, string $description, int $pad = 20)
    {
        $this->arguments[$section][$name] = $description;
    }

    public function getRender(): string
    {
        $render = '';

        $render .= $this->getDescription();
        $render .= $this->getArguments();
        $render .= $this->getOptions();

        return $render;
    }

    private function getDescription(): string
    {
        if (empty($this->arguments['description'])) {
            return '';
        }

        $items = \array_keys($this->arguments['description']);
        $padding = TextRenderer::getPadding($items);

        $description = \PHP_EOL;
        $description .= TextRenderer::color('Description', TextRenderer::COLOR_YELLOW) . \PHP_EOL;
        $description .= self::INDENT . $this->description . \PHP_EOL . \PHP_EOL;

        foreach ($this->arguments['description'] as $argument => $argumentDescription) {
            $name = self::INDENT . TextRenderer::rightPad($argument, $padding);
            $text = TextRenderer::color($name, TextRenderer::COLOR_GREEN) . $argumentDescription . \PHP_EOL;

            $description .= $text;
        }

        return $description;
    }

    private function getArguments(): string
    {
        if (empty($this->arguments['arguments'])) {
            return '';
        }

        $items = \array_keys($this->arguments['arguments']);
        $padding = TextRenderer::getPadding($items);

        $arguments = \PHP_EOL;
        $arguments .= TextRenderer::color('Arguments', TextRenderer::COLOR_YELLOW) . \PHP_EOL;

        foreach ($this->arguments['arguments'] as $argumentName => $argumentDescription) {
            $arguments .= self::INDENT . TextRenderer::rightPad($argumentName, $padding) . $argumentDescription . \PHP_EOL;
        }

        return $arguments;
    }

    private function getOptions(): string
    {
        if (empty($this->arguments['options'])) {
            return '';
        }

        $items = \array_keys($this->arguments['options']);
        $padding = TextRenderer::getPadding($items);

        $arguments = \PHP_EOL;
        $arguments .= TextRenderer::color('Options', TextRenderer::COLOR_YELLOW) . \PHP_EOL;

        foreach ($this->arguments['options'] as $argument => $description) {
            $arguments .= self::INDENT . TextRenderer::rightPad($argument, $padding) . $description . \PHP_EOL;
        }

        return $arguments;
    }
}
