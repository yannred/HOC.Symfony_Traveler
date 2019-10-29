# Traveler - Partage de photos de voyages pour ses proches

On part du principe qu'on est un voyageur qui souhaite partager ses photos de voyages avec ses proches, sa famille.

L'application à réaliser est une interface web présentant les endroits où on a voyagé sur carte, avec la possibilité de voir les photos prises durant le voyage.

Cette interface sera protégée par une authentification, donc accessible à des utilisateurs créés au préalable.

## Création du projet

Créez le projet avec Composer, en utilisant le package [symfony/website-skeleton](https://packagist.org/packages/symfony/website-skeleton).

Une fois le projet créé, positionnez-vous dans le dossier du projet et exécutez la commande suivante pour vérifier le bon fonctionnement de l'application :

```bash
php bin/console server:run
```

## Base de données

Créez un nouvel utilisateur avec une base de données associée dans MySQL, puis enregistrez les coordonnées d'accès à cette base de données dans une [variable d'environnement](https://symfony.com/doc/current/configuration.html#config-dot-env).

>Créez un fichier .env.local qui ne sera pas intégré au contrôle de version, pour éviter de commit & push vos coordonnées de connexion à la base de données !

Vous devrez concevoir le schéma de bases de données pour stocker les données relatives à l'application.

Pour ce faire, vous allez créer des entités avec le [MakerBundle](https://symfony.com/doc/current/doctrine.html) de Symfony.

### Spécifications

- Il vous faudra des utilisateurs (champ "display" : login) avec un mot de passe chiffré. Plus d'infos [ici](https://symfony.com/doc/current/security.html#a-create-your-user-class).
- Il vous faudra également des "destinations" dans lesquelles vous stockerez un nom de ville, un pays, et des coordonnées GPS (2 champs, "lat" et "lng", format chaîne de caractères).
- Il faudra donc pouvoir relier un ou plusieurs voyages à une destination.
- Pour chaque voyage effectué, il faudra donc pouvoir enregistrer une ou plusieurs photos. Les champs seront représentés sous forme de chaîne de caractères, contenant le chemin vers l'image

### Date de création d'enregistrement

Dans chaque table, vous allez ajouter un champ `created` qui retiendra la date de création de chaque enregistrement.

Cette donnée peut nous servir, dans n'importe quelle application, à avoir une sorte d'historique technique disponible pour analyse en cas de problème.

La problématique va donc être d'enregistrer cette date automatiquement à la création d'un enregistrement.

Ce qui tombe bien, c'est qu'il existe un package qui peut s'en charger pour nous. Il suffit de l'installer et le configurer correctement.

Vous vous intéresserez et documenterez à propos du package [StofDoctrineExtensionsBundle](https://github.com/stof/StofDoctrineExtensionsBundle).

Ce package est bien noté, maintenu, et possède même une [section dans la documentation officielle de Symfony](https://symfony.com/doc/current/bundles/StofDoctrineExtensionsBundle/index.html).

Vous pourrez également trouver une recette pour ce package dans le registre flex.symfony.com.

Ce package vient intégrer le package [DoctrineExtensions](https://github.com/Atlantic18/DoctrineExtensions) dans une application Symfony 4.

>Documentez-vous pour voir quelle extension il faut activer dans la configuration, afin d'activer l'enregistrement automatique de la date de création pour toutes les entités
---
>Facultatif : si vous voulez, créez également un champ `updated` ou `updatedAt` contenant la date de mise à jour de l'enregistrement

## Arborescence

> Toute l'application sera protégée par authentification. On va mettre en oeuvre le système de rôles de Symfony, en autorisant une partie de l'application aux utilisateurs normaux (amis, familles) et une autre partie de l'application à l'administrateur (nous-mêmes)

### Utilisateur

Un utilisateur, une fois connecté, accèdera à une page d'accueil présentant une carte interactive des voyages enregistrés. Pour générer la carte on pourra utiliser [Leaflet](https://leafletjs.com/).

**Dans un premier temps n'affichez pas la carte, préparez votre arborescence.**

On aura également une fiche de voyage, qui présentera les infos sur le voyage et les différentes photos prises pendant le voyage.

### Administrateur

Une fois connecté, l'administrateur pourra également accéder à la carte interactive, comme un utilisateur normal, ainsi qu'à une fiche voyage, mais également à une **interface d'administration**.

>L'interface d'administration sera accessible pour l'administrateur uniquement. **Nous allons implémenter l'authentification et le contrôle d'accès après. Pour le moment nous définissons notre arborescence.

L'interface d'administration aura plusieurs pages :

- Liste des destinations
- Ajouter une destination
- Editer une destination
- Supprimer une destination
- Liste des voyages
- Ajouter un voyage
- Editer un voyage
- Supprimer un voyage
- Liste des utilisateurs
- Ajouter un utilisateur
- Editer un utilisateur
- Supprimer un utilisateur

>Donc pour résumer : un CRUD sur les destinations, les voyages et les utilisateurs. Plus d'infos [ici](https://symfony.com/blog/introducing-the-symfony-maker-bundle) et [ici](https://stackoverflow.com/a/49997747).

Il faudra isoler la partie d'administration derrière un préfixe de route **/admin**

Plus d'infos [ici](https://symfony.com/blog/new-in-symfony-4-1-prefix-imported-route-names), **attention nous utiliserons la configuration YAML !**

## Sécurité

Une fois l'arborescence définie, nous allons restreindre l'accès à certaines parties de l'application.

Nous allons donc générer un [formulaire de login](https://symfony.com/doc/current/security/form_login_setup.html) de type 1 (Login form authenticator, voir dans le lien fourni)

L'idée principale est la suivante :

- Restreindre toute l'application aux utilisateurs authentifiés uniquement
- Restreindre les URL d'administration (qui commencent donc par /admin) aux utilisateurs ayant le rôle `ROLE_ADMIN`

Vous agirez sur le fichier de configuration yaml du bundle `Security` de Symfony pour appliquer ce système.

## Fixtures

Vous utiliserez les [fixtures](https://symfony.com/doc/master/bundles/DoctrineFixturesBundle/index.html) pour générer des données de tests dans votre base de données.

Vous devrez générer des fixtures au moins pour les utilisateurs afin de créer :

- au moins un utilisateur normal (sans rôle ou bien avec le rôle `ROLE_USER` uniquement)
- un administrateur (avec le rôle `ROLE_ADMIN`)

Ainsi vous pourrez tester les accès aux différentes sections de l'application, avec un utilisateur ou un administrateur.

>N'oubliez pas que pour un utilisateur, vous devrez chiffrer le mot de passe

*Pour les autres entités, pour le moment nous les laissons de côté. Nous allons nous concentrer sur la réalisation des interfaces de saisie (CRUD) dans l'administration afin de mettre en place les différents liens et le système qui récupère les données de géolocalisation.*

*Une fois ces fonctionnalités en place, nous pourrons retourner dans nos fixtures pour créer des données de tests pour les destinations, les voyages, etc...nous utiliserons à ce moment-là un package externe nous permettant de [simuler des données de manière aléatoire](https://github.com/fzaninotto/Faker).*

## Layouts / Templates

Vous allez utiliser [Twig](https://twig.symfony.com/doc/2.x/templates.html) pour réaliser vos interfaces. Vous pourrez explorer les templates générés par Symfony lorsque vous faites un contrôleur par exemple, ou encore un formulaire d'identification ou bien un CRUD.

Pour les liens, vous utiliserez la fonction [path](https://symfony.com/doc/current/reference/twig_reference.html#path), extension ajoutée par Symfony vous permettant de réaliser des liens avec des noms de routes déclarés dans vos contrôleurs.

Pour les listes, vous aurez besoin de faire des [boucles](https://twig.symfony.com/doc/2.x/tags/for.html).

Consultez aussi [cette page](https://symfony.com/doc/3.4/templating/app_variable.html) pour vous renseigner sur la manière d'afficher des éléments de manière conditionnelle, en fonction du contexte (utilisateur connecté ou non)

>Mettez en oeuvre le mécanisme d'[héritage de templates](https://twig.symfony.com/doc/2.x/templates.html#template-inheritance) pour éviter d'avoir à répéter des sections communes
---
>Utilisez la fonction d'[inclusion de template](https://twig.symfony.com/doc/2.x/templates.html#including-other-templates) pour factoriser des templates dont vous aurez besoin dans plusieurs pages
---

Pour faire des liens vers vos CSS, JS, et images, vous utiliserez le composant [asset](https://symfony.com/doc/current/templates.html#linking-to-css-javascript-and-image-assets) de Symfony. **Il est déjà inclus dans ce qu'on a installé avec le website-skeleton !**

>### Facultatif : Pour les plus motivés d'entre vous, vous pouvez utiliser [Webpack Encore](https://symfony.com/doc/current/frontend.html). Mais attention, je vous conseille de réaliser votre interface sans Webpack Encore dans un premier temps. Oui ce serait bien d'utiliser Yarn, Webpack et des packages Javascript, mais vu le temps imparti on ne va pas pouvoir aller trop loin non plus

### CRUD

Vous allez donc réaliser le CRUD à partir des fichiers précédemment générés par Symfony.

Dans des formulaires comme celui de la destination, il va falloir générer des listes déroulantes contenant [les données d'une autre entité](https://symfony.com/doc/current/reference/forms/types/entity.html) (la table Pays par exemple).

>Cherchez sur le net un script contenant les pays du monde, et importez ces données dans votre table Pays. Si vous voulez éviter que les fixtures ne suppriment vos données, vous pouvez aussi les définir dans les fixtures
---
>Plus d'infos pour personnaliser vos formulaires [ici](https://symfony.com/doc/current/form/form_customization.html#form-rendering-functions) (générer les champs séparément, les labels, etc...)

### Images

Dans votre formulaire de création de voyages, vous allez uploader une ou plusieurs images.

Utilisez un [formulaire Symfony](https://symfony.com/doc/current/forms.html) classique avec [upload d'images](https://symfonycasts.com/screencast/symfony-uploads/upload-in-form).

Pour afficher vos images, il faudra les redimensionner. Cherchez un package Composer adapté et fonctionnant bien avec Symfony (présent dans la documentation officielle).

>Vous pouvez utiliser le Maker bundle avec `php bin/console make:form` pour créer un formulaire, normalement si vous avez fait un `make:crud` Symfony devrait avoir généré les formulaires

## Géolocalisation

Lors de la création d'une destination, vous allez saisir une ville et un pays.

Le but va être d'appeler automatiquement une [API OpenStreetMaps](https://wiki.openstreetmap.org/wiki/Nominatim) qui nous renverra des données de géolocalisation au format JSON à partir de la ville et du pays.

>### **Attention, prenez bien connaissance des conditions d'utilisation de l'API (Usage policy)**

Pour effectuer la requête, vous chercherez **un package Composer faisant office de client HTTP**. Pour valider votre choix, demandez-moi si c'est le bon et motivez votre choix.

>Vous réaliserez donc un [service](https://symfony.com/doc/current/service_container.html) de géolocalisation que vous pourrez type-hinter dans un contrôleur ou un autre service pour l'injecter automatiquement
---
>Facultatif : vous étudierez les possibilités d'automatiser l'enregistrement de la géolocalisation lorsque vous créerez ou mettrez à jour une destination, en vous renseignant sur les [listeners et les événements Doctrine](https://symfony.com/doc/4.1/doctrine/event_listeners_subscribers.html). **Mais dans un premier temps, vous réaliserez la fonctionnalité directement dans le contrôleur, en utilisant le service de géolocalisation en tant que dépendance**

Une fois la fonctionnalité de géolocalisation réalisée, vous pourrez commencer à implémenter la carte dans la page d'accueil de la partie publique, présentant dans un premier temps les destinations enregistrées dans votre système (pas encore les voyages).
