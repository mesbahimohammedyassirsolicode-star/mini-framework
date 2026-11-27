<?php

declare(strict_types=1);

namespace MiniFramework\Repositories;

use MiniFramework\Models\Article;

interface ArticleRepository
{
    /** @return list<Article> */
    public function all(): array;

    public function find(int $id): ?Article;

    public function save(Article $article): Article;
}
