<?php

declare(strict_types=1);

namespace MiniFramework\Views;

final class ViewRenderer
{
    public function __construct(private string $viewsDirectory)
    {
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): string
    {
        $templatePath = $this->resolveTemplate($template);
        extract($data, EXTR_SKIP);

        ob_start();
        try {
            require $templatePath;
            return (string) ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }
    }

    private function resolveTemplate(string $template): string
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+(?:\/[a-zA-Z0-9_-]+)*$/', $template)) {
            throw new \InvalidArgumentException('Nom de template invalide.');
        }

        $path = $this->viewsDirectory . '/' . $template . '.php';
        if (!is_file($path)) {
            throw new \RuntimeException(sprintf('Template introuvable : %s.', $template));
        }

        return $path;
    }
}
