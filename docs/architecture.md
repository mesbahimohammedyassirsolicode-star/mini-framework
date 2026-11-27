# Architecture du mini-framework MVC

## Périmètre métier

Le blog comporte exactement deux entités métier :

- **Article** : entité principale, contenant au minimum un identifiant, un titre, un contenu, une date de publication et la référence à une catégorie.
- **Categorie** : entité de classification, contenant au minimum un identifiant et un nom.

Relation : un article appartient à une catégorie ; une catégorie peut être associée à plusieurs articles. Les Repositories décrits ci-dessous sont des composants d’accès aux données, pas des entités supplémentaires.

## Arborescence cible

```text
public/
└── index.php
src/
├── Controllers/
│   └── ArticleController.php
├── Models/
│   ├── Article.php
│   └── Categorie.php
├── Repositories/
│   ├── ArticleRepository.php
│   └── CategorieRepository.php
├── Routing/
│   └── Router.php
├── Services/
│   └── ArticleService.php
└── Views/
    ├── articles/
    │   ├── form.php
    │   ├── index.php
    │   └── show.php
    └── layouts/
        └── main.php
```

Cette arborescence est un squelette de conception. Elle ne prétend pas fournir une application exécutable ou des classes déjà implémentées.

## Responsabilités par couche

### Model

`Article` et `Categorie` portent les données et invariants métier propres à chaque entité. Ils ne lisent pas directement la base de données, ne connaissent pas les requêtes HTTP et ne produisent pas de HTML.

### View

Les templates affichent les données qui leur sont fournies. Ils ne chargent pas eux-mêmes les données et ne décident pas des règles métier. Les valeurs provenant des articles et catégories doivent être échappées pour prévenir l’injection HTML.

### Controller

Le Controller est l’adaptateur entre HTTP et les cas d’usage : il reçoit les paramètres, effectue les contrôles propres au transport, appelle le Service et produit une réponse. Il ne doit pas contenir de SQL ou concentrer les règles métier.

### Service

Le Service exprime les cas d’usage applicatifs. `ArticleService` peut coordonner les vérifications nécessaires pour créer ou publier un Article, notamment confirmer que la Categorie référencée existe. Il délègue la persistance aux Repositories et n’a pas connaissance des templates ou des détails HTTP.

### Router

Le Router associe une méthode et un chemin HTTP à une action de Controller, extrait les paramètres du chemin et transmet la requête. Il ne contient ni accès aux données ni logique métier.

### Repository

Les Repositories isolent la persistance et les requêtes de stockage. `ArticleRepository` charge et enregistre les Articles ; `CategorieRepository` charge les Catégories nécessaires. Ce découpage évite que Controller et Service dépendent des détails SQL.

## Sens des interactions

```text
Navigateur
  → public/index.php
  → Router
  → ArticleController
  → ArticleService
  → Repositories
  → stockage

Résultat :
  stockage → Repositories → Service → Controller → View → réponse HTTP
```

Le Router appelle le Controller ; le Controller appelle le Service ; le Service utilise les Repositories ; les Repositories retournent les Models. Pour une réponse HTML, le Controller fournit les données à la View. La View ne rappelle pas le Controller ou le Service.

## Cas d’usage : afficher un article

1. Le navigateur demande `GET /articles/42`.
2. Le point d’entrée transmet la requête au Router.
3. Le Router fait correspondre le chemin à `ArticleController::show(42)`.
4. Le Controller demande l’article au `ArticleService`.
5. Le Service demande les données à l’`ArticleRepository` et, si nécessaire, à la `CategorieRepository`.
6. Le Controller fournit l’article (avec sa catégorie) au template de détail.
7. La View rend le HTML ; le point d’entrée renvoie la réponse.

## Cas d’usage : créer un article

1. Le Router achemine `POST /articles` vers `ArticleController::store()`.
2. Le Controller extrait les champs reçus et délègue la création au Service.
3. Le Service vérifie les règles applicatives, dont la validité de la catégorie choisie.
4. Le Service demande au Repository d’enregistrer l’Article.
5. Le Controller redirige vers la page de détail ou retourne une réponse d’erreur adaptée.

La validation serveur reste obligatoire, même si le formulaire effectue aussi une validation côté navigateur.

## Routes indicatives

| Méthode | Chemin | Contrôleur | But |
|---|---|---|---|
| `GET` | `/articles` | `ArticleController::index()` | Afficher la liste |
| `GET` | `/articles/{id}` | `ArticleController::show($id)` | Afficher un article |
| `GET` | `/articles/create` | `ArticleController::create()` | Afficher le formulaire |
| `POST` | `/articles` | `ArticleController::store()` | Créer un article |

Ces routes sont des exemples de conception, pas des endpoints implémentés.
