<?php

declare(strict_types=1);

namespace MiniFramework\Repositories;

use MiniFramework\Models\Article;
use MiniFramework\Models\Categorie;

final class JsonArticleRepository implements ArticleRepository
{
    public function __construct(
        private string $filePath,
        private CategorieRepository $categories,
    ) {
    }

    public function all(): array
    {
        $articles = [];
        foreach ($this->readRecords() as $record) {
            $article = $this->hydrate($record);
            if ($article !== null) {
                $articles[] = $article;
            }
        }
        return $articles;
    }

    public function find(int $id): ?Article
    {
        foreach ($this->readRecords() as $record) {
            if (($record['id'] ?? null) === $id) {
                return $this->hydrate($record);
            }
        }
        return null;
    }

    public function save(Article $article): Article
    {
        $records = $this->readRecords();
        $id = $article->id() ?? $this->nextId($records);
        $categoryId = $article->categorie()->id();
        if ($categoryId === null) {
            throw new \InvalidArgumentException('La catégorie doit être persistée avant l’article.');
        }

        $record = [
            'id' => $id,
            'titre' => $article->titre(),
            'contenu' => $article->contenu(),
            'date_publication' => $article->datePublication()->format(DATE_ATOM),
            'categorie_id' => $categoryId,
        ];

        $updated = false;
        foreach ($records as $index => $existing) {
            if (($existing['id'] ?? null) === $id) {
                $records[$index] = $record;
                $updated = true;
                break;
            }
        }
        if (!$updated) {
            $records[] = $record;
        }

        $this->writeRecords($records);
        return new Article($id, $article->titre(), $article->contenu(), $article->datePublication(), $article->categorie());
    }

    /** @return list<array<string, mixed>> */
    private function readRecords(): array
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
            throw new \RuntimeException('Le fichier de données des articles est invalide.', 0, $exception);
        }

        if (!is_array($records) || !array_is_list($records)) {
            throw new \RuntimeException('Le fichier de données des articles doit contenir une liste JSON.');
        }

        return $records;
    }

    /** @param array<string, mixed> $record */
    private function hydrate(array $record): ?Article
    {
        $id = $record['id'] ?? null;
        $categoryId = $record['categorie_id'] ?? null;
        $category = is_int($categoryId) ? $this->categories->find($categoryId) : null;
        if (!is_int($id) || !is_string($record['titre'] ?? null) || !is_string($record['contenu'] ?? null)
            || !is_string($record['date_publication'] ?? null) || !$category instanceof Categorie) {
            return null;
        }

        try {
            $date = new \DateTimeImmutable($record['date_publication']);
        } catch (\Exception) {
            return null;
        }

        return new Article($id, $record['titre'], $record['contenu'], $date, $category);
    }

    /** @param list<array<string, mixed>> $records */
    private function nextId(array $records): int
    {
        $maximum = 0;
        foreach ($records as $record) {
            if (is_int($record['id'] ?? null)) {
                $maximum = max($maximum, $record['id']);
            }
        }
        return $maximum + 1;
    }

    /** @param list<array<string, mixed>> $records */
    private function writeRecords(array $records): void
    {
        $directory = dirname($this->filePath);
        if (!is_dir($directory) || !is_writable($directory)) {
            throw new \RuntimeException('Le répertoire de stockage des articles est inaccessible.');
        }

        $json = json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $temporaryPath = $this->filePath . '.tmp';
        if (file_put_contents($temporaryPath, $json, LOCK_EX) === false || !rename($temporaryPath, $this->filePath)) {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
            throw new \RuntimeException('Impossible d’enregistrer les articles.');
        }
    }
}
