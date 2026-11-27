<?php

declare(strict_types=1);

namespace MiniFramework\Models;

final class Article
{
    public function __construct(
        private ?int $id,
        private string $titre,
        private string $contenu,
        private \DateTimeImmutable $datePublication,
        private Categorie $categorie,
    ) {
        if (trim($this->titre) === '') {
            throw new \InvalidArgumentException('Le titre de l’article est obligatoire.');
        }

        if (trim($this->contenu) === '') {
            throw new \InvalidArgumentException('Le contenu de l’article est obligatoire.');
        }
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function titre(): string
    {
        return $this->titre;
    }

    public function contenu(): string
    {
        return $this->contenu;
    }

    public function datePublication(): \DateTimeImmutable
    {
        return $this->datePublication;
    }

    public function categorie(): Categorie
    {
        return $this->categorie;
    }
}
