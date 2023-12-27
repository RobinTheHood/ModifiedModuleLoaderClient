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

namespace RobinTheHood\ModifiedModuleLoaderClient\ViewModels;

class ButtonViewModel
{
    public const DEFAULT = 'default';
    public const PRIMARY = 'primary';
    public const SUCCESS = 'success';
    public const WARNING = 'warning';
    public const DANGER = 'danger';

    /**
     * @var array<int, array{title: string, url: string, message?: string}> $actions
     */
    private $actions = [];

    /** @var string */
    private $class = self::DEFAULT;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public static function create(string $class): ButtonViewModel
    {
        $button = new ButtonViewModel($class);
        return $button;
    }

    public function addAction(string $title, string $url): ButtonViewModel
    {
        $this->actions[] = [
            'title' => $title,
            'url' => $url,
            'message' => ''
        ];
        return $this;
    }

    public function addConfirmAction(string $title, string $url, string $message): ButtonViewModel
    {
        $this->actions[] = [
            'title' => $title,
            'url' => $url,
            'message' => $message
        ];
        return $this;
    }

    public function render(): string
    {
        if (empty($this->actions)) {
            return '';
        }

        $class = $this->mapClassToCss($this->class);

        $title = $this->actions[0]['title'] ?? '';
        $url = $this->actions[0]['url'] ?? '';
        $message = $this->actions[0]['message'] ?? '';

        $js = $message ? $this->renderConfirmJs($message) : '';

        if (count($this->actions) > 1) {
            $htmlActions = $this->renderActions($this->actions);

            $html = sprintf(
                '<div class="btn-group">
                    <a class="btn %s" href="%s" %s role="button" aria-expanded="false">
                        %s
                    </a>
                    <button type="button" class="btn %s dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"
                        aria-expanded="false" data-reference="parent">
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu">%s</div>
                </div>',
                htmlspecialchars($class),
                htmlspecialchars($url),
                $js,
                htmlspecialchars($title),
                htmlspecialchars($class),
                $htmlActions
            );
        } else {
            $html = sprintf(
                '<div class="btn-group">
                    <a class="btn %s" href="%s" %s role="button">%s</a>
                </div>',
                htmlspecialchars($class),
                htmlspecialchars($url),
                $js,
                htmlspecialchars($title)
            );
        }

        return $html;
    }


    public function __toString()
    {
        return $this->render();
    }

    private function mapClassToCss(string $class): string
    {
        $classMapping = [
            self::DEFAULT => 'btn-outline-default',
            self::PRIMARY => 'btn-outline-primary',
            self::SUCCESS => 'btn-outline-success',
            self::WARNING => 'btn-outline-warning',
            self::DANGER  => 'btn-outline-danger',
        ];

        return $classMapping[$class] ?? 'btn-outline-default';
    }

    /**
     * @param array<int, array{title: string, url: string, message?: string}> $actions
     */
    private function renderActions(array $actions): string
    {
        $html = '';
        foreach ($actions as $action) {
            if ($action['message'] ?? '') {
                $html .= $this->renderAConfirmTag(
                    $action['title'] ?? '',
                    $action['url'] ?? '',
                    $action['message'] ?? '',
                    'dropdown-item'
                );
            } else {
                $html .= $this->renderATag($action['title'], $action['url'], 'dropdown-item');
            }
        }
        return $html;
    }

    private function renderATag(string $title, string $url, string $class = ''): string
    {
        return sprintf(
            '<a class="%s" href="%s">%s</a>',
            htmlspecialchars($class),
            htmlspecialchars($url),
            htmlspecialchars($title)
        );
    }

    private function renderAConfirmTag(string $title, string $url, string $message, string $class = ''): string
    {
        $js = $this->renderConfirmJs($message);
        return sprintf(
            '<a class="%s" href="%s" %s>%s</a>',
            htmlspecialchars($class),
            htmlspecialchars($url),
            $js,
            htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
        );
    }

    private function renderConfirmJs(string $message): string
    {
        return sprintf('onclick="return confirm(\'%s\');"', htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    }
}
