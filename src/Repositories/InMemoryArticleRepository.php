<?php

declare(strict_types=1);

namespace MiniFramework\Repositories;

use MiniFramework\Models\Article;

final class InMemoryArticleRepository implements ArticleRepository
{
    /** @var array<int, Article> */
    private array $articles = [];

    private int $nextId = 1;

    /** @param list<Article> $articles */
    public function __construct(array $articles = [])
    {
        foreach ($articles as $article) {
            $this->save($article);
        }
    }

    public function all(): array
    {
        return array_values($this->articles);
    }

    public function find(int $id): ?Article
    {
        return $this->articles[$id] ?? null;
    }

    public function save(Article $article): Article
    {
        $id = $article->id() ?? $this->nextId++;
        $stored = new Article($id, $article->titre(), $article->contenu(), $article->datePublication(), $article->categorie());
        $this->articles[$id] = $stored;
        $this->nextId = max($this->nextId, $id + 1);
        return $stored;
    }
}
