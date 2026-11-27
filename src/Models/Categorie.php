<?php

declare(strict_types=1);

namespace MiniFramework\Models;

final class Categorie
{
    public function __construct(
        private ?int $id,
        private string $nom,
    ) {
        if (trim($this->nom) === '') {
            throw new \InvalidArgumentException('Le nom de la catégorie est obligatoire.');
        }
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function nom(): string
    {
        return $this->nom;
    }
}
