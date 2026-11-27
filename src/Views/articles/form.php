<?php

declare(strict_types=1);

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$title = isset($title) && is_string($title) ? $title : 'Créer un article — Blog';
$old = isset($old) && is_array($old) ? $old : [];
$errors = isset($errors) && is_array($errors) ? $errors : [];
$categories = isset($categories) && is_array($categories) ? $categories : [];
ob_start();
?>
<h1>Créer un article</h1>
<?php if ($errors !== []): ?>
    <div role="alert" aria-labelledby="form-errors-title">
        <h2 id="form-errors-title">Le formulaire contient une erreur</h2>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= $escape((string) $error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<form method="post" action="/articles">
    <div>
        <label for="titre">Titre</label>
        <input id="titre" name="titre" type="text" required maxlength="200" value="<?= $escape(is_string($old['titre'] ?? null) ? $old['titre'] : '') ?>">
    </div>
    <div>
        <label for="contenu">Contenu</label>
        <textarea id="contenu" name="contenu" rows="10" required><?= $escape(is_string($old['contenu'] ?? null) ? $old['contenu'] : '') ?></textarea>
    </div>
    <div>
        <label for="categorie_id">Catégorie</label>
        <select id="categorie_id" name="categorie_id" required>
            <option value="">Choisir une catégorie</option>
            <?php foreach ($categories as $categorie): ?>
                <option value="<?= $escape((string) $categorie->id()) ?>" <?= (string) $categorie->id() === (string) ($old['categorie_id'] ?? '') ? 'selected' : '' ?>><?= $escape($categorie->nom()) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit">Publier</button>
</form>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
