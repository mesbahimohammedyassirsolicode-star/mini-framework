<?php

declare(strict_types=1);

namespace MiniFramework\Repositories;

use MiniFramework\Models\Categorie;

final class JsonCategorieRepository implements CategorieRepository
{
    public function __construct(private string $filePath)
    {
    }

    public function find(int $id): ?Categorie
    {
        foreach ($this->all() as $category) {
            if ($category->id() === $id) {
                return $category;
            }
        }
        return null;
    }

    public function all(): array
    {
        if (!is_file($this->filePath)) {
            return [];
        }

        $json = file_get_contents($this->filePath);
        if ($json === false || trim($json) === '') {
            return [];
        }

        try {
            $records = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('Le fichier de données des catégories est invalide.', 0, $exception);
        }

        if (!is_array($records) || !array_is_list($records)) {
            throw new \RuntimeException('Le fichier de catégories doit contenir une liste JSON.');
        }

        $categories = [];
        foreach ($records as $record) {
            if (!is_array($record) || !is_int($record['id'] ?? null) || !is_string($record['nom'] ?? null)) {
                continue;
            }
            $categories[] = new Categorie($record['id'], $record['nom']);
        }
        return $categories;
    }
}
