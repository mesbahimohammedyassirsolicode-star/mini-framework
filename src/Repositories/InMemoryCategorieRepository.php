<?php

declare(strict_types=1);

namespace MiniFramework\Repositories;

use MiniFramework\Models\Categorie;

final class InMemoryCategorieRepository implements CategorieRepository
{
    /** @var array<int, Categorie> */
    private array $categories = [];

    /** @param list<Categorie> $categories */
    public function __construct(array $categories = [])
    {
        foreach ($categories as $categorie) {
            if ($categorie->id() === null) {
                throw new \InvalidArgumentException('Les catégories en mémoire doivent avoir un identifiant.');
            }
            $this->categories[$categorie->id()] = $categorie;
        }
    }

    public function find(int $id): ?Categorie
    {
        return $this->categories[$id] ?? null;
    }

    public function all(): array
    {
        return array_values($this->categories);
    }
}
