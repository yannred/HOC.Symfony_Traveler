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

Nous allons donc générer un [formulaire de login](https://symfony.com/doc/current/security/form_login_setup.html) de type 1 (Login form authenticator)

L'idée principale est la suivante :

- Restreindre toute l'application aux utilisateurs authentifiés uniquement
- Restreindre les URL d'administration (qui commencent donc par /admin) aux utilisateurs ayant le rôle `ROLE_ADMIN`
