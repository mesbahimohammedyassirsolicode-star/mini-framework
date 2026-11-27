<?php

declare(strict_types=1);

namespace MiniFramework\Controllers;

use MiniFramework\Services\ArticleService;
use MiniFramework\Views\ViewRenderer;

final class ArticleController
{
    public function __construct(
        private ArticleService $articles,
        private ViewRenderer $views,
    ) {
    }

    public function index(): string
    {
        return $this->views->render('articles/index', [
            'articles' => $this->articles->lister(),
            'title' => 'Articles — Blog',
        ]);
    }

    public function show(int $id): string
    {
        $article = $this->articles->trouver($id);
        if ($article === null) {
            http_response_code(404);
            return $this->views->render('layouts/main', [
                'title' => 'Article introuvable',
                'content' => '<h1>Article introuvable</h1>',
            ]);
        }

        return $this->views->render('articles/show', [
            'article' => $article,
            'title' => $article->titre() . ' — Blog',
        ]);
    }

    public function create(): string
    {
        return $this->renderForm([]);
    }

    /** @param array<string, mixed> $input */
    public function store(array $input): string
    {
        try {
            $title = is_string($input['titre'] ?? null) ? $input['titre'] : '';
            $content = is_string($input['contenu'] ?? null) ? $input['contenu'] : '';
            $categoryId = filter_var($input['categorie_id'] ?? null, FILTER_VALIDATE_INT);
            if ($categoryId === false || $categoryId === null || $categoryId < 1) {
                throw new \InvalidArgumentException('Veuillez choisir une catégorie valide.');
            }

            $article = $this->articles->creer($title, $content, $categoryId);
            header('Location: /articles/' . $article->id(), true, 303);
            return '';
        } catch (\InvalidArgumentException $exception) {
            http_response_code(422);
            return $this->renderForm([$exception->getMessage()], $input);
        }
    }

    /** @param list<string> $errors
     *  @param array<string, mixed> $input
     */
    private function renderForm(array $errors, array $input = []): string
    {
        return $this->views->render('articles/form', [
            'categories' => $this->articles->listerCategories(),
            'errors' => $errors,
            'old' => $input,
            'title' => 'Créer un article — Blog',
        ]);
    }
}
