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
