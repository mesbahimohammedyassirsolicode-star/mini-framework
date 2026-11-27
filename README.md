# Mini-framework MVC — blog

Architecture de référence d’un mini-framework MVC en PHP, illustrée par un blog avec uniquement deux entités métier : `Article` et `Categorie`.

## Structure

```text
mini-framework/
├── public/
│   └── index.php                 # Front controller : point d’entrée HTTP
├── src/
│   ├── Controllers/
│   │   └── ArticleController.php # Reçoit la requête et choisit la réponse
│   ├── Models/
│   │   ├── Article.php           # Entité principale du blog
│   │   └── Categorie.php         # Classification des articles
│   ├── Repositories/
│   │   ├── ArticleRepository.php # Contrat de persistance des articles
│   │   ├── CategorieRepository.php
│   │   ├── InMemoryArticleRepository.php # Adaptateurs de test
│   │   ├── InMemoryCategorieRepository.php
│   │   ├── JsonArticleRepository.php    # Persistance locale JSON
│   │   └── JsonCategorieRepository.php
│   ├── Routing/
│   │   ├── BlogRoutes.php         # Déclare les routes du blog
│   │   └── Router.php            # Associe URL/méthode HTTP à une action
│   ├── bootstrap.php              # Autoload et câblage des dépendances
│   ├── Services/
│   │   └── ArticleService.php    # Cas d’usage et règles applicatives
│   └── Views/
│       ├── articles/
│       │   ├── form.php
│       │   ├── index.php
│       │   └── show.php
│       ├── layouts/
│       │   └── main.php
│       └── ViewRenderer.php       # Rendu contrôlé des templates
├── data/
│   ├── articles.json              # Données persistantes locales
│   └── categories.json
├── tests/
│   ├── Mvc/
│   │   └── ModelViewControllerTest.php
│   ├── Persistence/
│   │   ├── JsonArticleRepositoryTest.php
│   │   └── run.php
│   └── Routing/
│       ├── RouterTest.php         # Tests sans framework externe
│       └── run.php                # Lance les tests du Router
└── docs/
    └── architecture.md           # Responsabilités et flux détaillés
```

Les couches applicatives, le routage et un câblage de démonstration sont implémentés dans `src/`. Le bootstrap utilise des Repositories JSON pour conserver les articles entre les requêtes. Les adaptateurs en mémoire restent disponibles pour les tests.

## Couches et responsabilités

- **Model** (`Article`, `Categorie`) : représente les concepts et règles métier. Un article appartient à une catégorie ; une catégorie peut classer plusieurs articles. Les modèles ne connaissent ni HTTP, ni HTML, ni le stockage.
- **View** : présente les données préparées par le Controller. Elle ne porte pas de règles métier ; toute donnée dynamique doit être échappée avant affichage.
- **Controller** : reçoit les paramètres de la requête, délègue le cas d’usage au Service et choisit une réponse (View, redirection ou erreur HTTP). Il ne contient pas de SQL ni de règles métier.
- **Service** : orchestre les cas d’usage et règles applicatives. Par exemple, la publication d’un article vérifie les données et l’existence de sa catégorie, puis délègue l’enregistrement au Repository.
- **Router** : associe méthode HTTP et URL à une action du Controller, et extrait les paramètres de route. Il ne réalise pas le cas d’usage.
- **Repository** (support de persistance) : isole la lecture et l’écriture des données. Ce n’est pas une entité métier et il ne remplace pas les deux Models.

## Interactions

```text
Requête HTTP
    ↓
public/index.php (front controller)
    ↓
Router ── associe méthode + URL à une action
    ↓
ArticleController ── valide les paramètres de transport et orchestre
    ↓
ArticleService ── exécute le cas d’usage
    ├── ArticleRepository ── lit/écrit Article
    └── CategorieRepository ─ lit Categorie
    ↓
Controller transmet les données à la View
    ↓
View rend le HTML ──→ réponse HTTP
```

La View ne rappelle pas le Service. Le Model ne dépend pas des couches HTTP, de présentation ou de persistance. Les erreurs applicatives sont converties en réponses HTTP au niveau du Controller/front controller.

## Exemple de flux : consulter un article

Pour `GET /articles/42` :

1. `public/index.php` démarre l’application et transmet la requête au `Router`.
2. Le `Router` appelle `ArticleController::show(42)`.
3. Le Controller délègue la recherche à `ArticleService`.
4. Le Service utilise `ArticleRepository` pour charger l’article et sa catégorie.
5. Le Controller donne les données à `Views/articles/show.php`.
6. La View échappe et affiche les valeurs, puis le résultat devient la réponse HTTP.

## Exemple de routes

| Méthode | URL | Action indicative |
|---|---|---|
| `GET` | `/articles` | `ArticleController::index()` — liste des articles |
| `GET` | `/articles/{id}` | `ArticleController::show($id)` — détail |
| `GET` | `/articles/create` | `ArticleController::create()` — formulaire |
| `POST` | `/articles` | `ArticleController::store()` — création |

Les routes sont déclarées en PHP avec des callbacks, par exemple `$router->get('/articles/{id}', [$controller, 'show'], 'articles.show')`. Les paramètres de chemin sont transmis à l’action dans l’ordre de déclaration. Pour `POST`, le tableau des données du formulaire est transmis après les paramètres de chemin. Les chemins sont normalisés sans slash final ; la query string est ignorée pour la correspondance.

Les tests de routage sont autonomes et ne requièrent pas de package externe. Depuis PowerShell, exécutez `php -d xdebug.mode=off tests/Routing/run.php`. Ils couvrent la liste, le détail avec paramètre, le formulaire, la création POST, les réponses 404/405 et la génération d’URL nommée.

Pour lancer la démonstration avec le serveur PHP intégré : `php -S 127.0.0.1:8000 -t public public/index.php`. Le processus PHP doit avoir les droits d’écriture sur `data/articles.json` (et le répertoire `data/`). Les catégories de démonstration sont définies dans `data/categories.json`.

Le test de persistance se lance avec `php -d xdebug.mode=off tests/Persistence/JsonArticleRepositoryTest.php`.
