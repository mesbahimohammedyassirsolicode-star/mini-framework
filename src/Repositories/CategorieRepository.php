<?php

declare(strict_types=1);

namespace MiniFramework\Repositories;

use MiniFramework\Models\Categorie;

interface CategorieRepository
{
    public function find(int $id): ?Categorie;

    /** @return list<Categorie> */
    public function all(): array;
}
