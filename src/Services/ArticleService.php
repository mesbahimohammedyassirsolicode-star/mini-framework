<?php

declare(strict_types=1);

namespace MiniFramework\Services;

use MiniFramework\Models\Article;
use MiniFramework\Repositories\ArticleRepository;
use MiniFramework\Repositories\CategorieRepository;

final class ArticleService
{
    public function __construct(
        private ArticleRepository $articles,
        private CategorieRepository $categories,
    ) {
    }

    /** @return list<Article> */
    public function lister(): array
    {
        return $this->articles->all();
    }

    /** @return list<\\MiniFramework\\Models\\Categorie> */
    public function listerCategories(): array
    {
        return $this->categories->all();
    }

    public function trouver(int $id): ?Article
    {
        if ($id < 1) {
            return null;
        }

        return $this->articles->find($id);
    }

    public function creer(string $titre, string $contenu, int $categorieId): Article
    {
        $titre = trim($titre);
        $contenu = trim($contenu);

        if ($titre === '' || $contenu === '') {
            throw new \InvalidArgumentException('Le titre et le contenu sont obligatoires.');
        }

        $categorie = $this->categories->find($categorieId);
        if ($categorie === null) {
            throw new \InvalidArgumentException('La catégorie sélectionnée est introuvable.');
        }

        $article = new Article(
            null,
            $titre,
            $contenu,
            new \DateTimeImmutable(),
            $categorie,
        );

        return $this->articles->save($article);
    }
}
