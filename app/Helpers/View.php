<?php

declare(strict_types=1);

namespace App\Helpers;

final class View
{
    private static string $templatesDir = '';

    public static function setTemplatesDir(string $dir): void
    {
        self::$templatesDir = $dir;
    }

    public static function render(string $name, array $data = [], string $layout = 'base'): HttpResponse
    {
        $templatePath = self::resolvePath($name);
        $layoutPath = self::resolvePath("layouts.{$layout}");

        $session = new \App\Services\PhpSessionStore();
        $csrf = $session->get('csrf_token');
        if (!is_string($csrf) || $csrf === '') {
            $csrf = bin2hex(random_bytes(32));
            $session->set('csrf_token', $csrf);
        }
        $data['csrf_token'] = $csrf;

        extract($data);


        ob_start();
        require $templatePath;
        $content = ob_get_clean();

        ob_start();
        require $layoutPath;
        $html = ob_get_clean();

        return HttpResponse::text($html, 200, ['content-type' => 'text/html; charset=utf-8']);
    }

    private static function resolvePath(string $name): string
    {
        $parts = explode('.', $name);
        $file = implode(DIRECTORY_SEPARATOR, $parts) . '.php';
        $path = self::$templatesDir . DIRECTORY_SEPARATOR . $file;

        if (!is_file($path)) {
            // Try with .html.php
            $file = implode(DIRECTORY_SEPARATOR, $parts) . '.html.php';
            $path = self::$templatesDir . DIRECTORY_SEPARATOR . $file;
        }

        if (!is_file($path)) {
            throw new \RuntimeException("Template not found: {$name} at {$path}");
        }

        return $path;
    }
}
