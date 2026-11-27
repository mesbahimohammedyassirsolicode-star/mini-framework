<?php

declare(strict_types=1);

namespace MiniFramework\Controllers;

use MiniFramework\Services\ArticleService;

final class ArticleController
{
    public function __construct(private ArticleService $articles)
    {
    }

    public function index(): void
    {
        $articles = $this->articles->lister();
        require dirname(__DIR__) . '/Views/articles/index.php';
    }

    public function show(int $id): void
    {
        $article = $this->articles->trouver($id);
        if ($article === null) {
            http_response_code(404);
            $title = 'Article introuvable';
            $content = '<h1>Article introuvable</h1>';
            require dirname(__DIR__) . '/Views/layouts/main.php';
            return;
        }

        require dirname(__DIR__) . '/Views/articles/show.php';
    }

    public function create(): void
    {
        $categories = $this->articlesCategories();
        $errors = [];
        require dirname(__DIR__) . '/Views/articles/form.php';
    }

    public function store(array $input): void
    {
        try {
            $titre = is_string($input['titre'] ?? null) ? $input['titre'] : '';
            $contenu = is_string($input['contenu'] ?? null) ? $input['contenu'] : '';
            $categorieId = filter_var($input['categorie_id'] ?? null, FILTER_VALIDATE_INT);
            if ($categorieId === false || $categorieId === null || $categorieId < 1) {
                throw new \InvalidArgumentException('Veuillez choisir une catégorie valide.');
            }

            $article = $this->articles->creer($titre, $contenu, $categorieId);
            header('Location: /articles/' . $article->id(), true, 303);
        } catch (\InvalidArgumentException $exception) {
            http_response_code(422);
            $errors = [$exception->getMessage()];
            $categories = $this->articlesCategories();
            require dirname(__DIR__) . '/Views/articles/form.php';
        }
    }

    /** @return list<\MiniFramework\Models\Categorie> */
    private function articlesCategories(): array
    {
        return $this->articles->listerCategories();
    }
}
