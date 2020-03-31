# Réalisation d'un service de Geocoding

Lors de la création d'une destination, nous souhaitons récupérer sa latitude et sa longitude à partir du nom de la ville et du nom du Pays.

Nous allons chercher ces informations sur l'API OpenStreetMap [Nominatim](https://wiki.openstreetmap.org/wiki/Nominatim).

## Construction de la requête

Nous trouvons un endpoint "/search" dans la documentation de l'API.

Cet endpoint accepte des paramètres GET :

- q : la recherche, sous forme de chaîne de caratères
- format : le format des résultats (`json` pour nous)
- limit : pour limiter le nombre de résultats renvoyés

Notre URL sera donc : `https://nominatim.openstreetmap.org/search?q=Ville,%20Pays&format=json&limit=1`

## Réalisation de la requête avec Guzzle

Installez [Guzzle](https://packagist.org/packages/guzzlehttp/guzzle) dans votre application.

```bash
composer require guzzlehttp/guzzle
```

> On pourrait également utiliser la classe [HttpClient](https://symfony.com/doc/current/components/http_client.html) introduite dans la version 4.3 de Symfony.

Pour commencer, nous allons effectuer notre requête vers l'API OpenStreetMap dans le contrôleur :

> Fichier : src/Controller/Admin/DestinationController.php

```php
// use GuzzleHttp\Client;

public function new(Request $request): Response
{
    //...

    if ($form->isSubmitted() && $form->isValid()) {
        $client = new Client();
        $location = $destination->getVille() . ', ' . $destination->getPays()->getNom();

        $response = $client->request(
            'GET',
            'https://nominatim.openstreetmap.org/search',
            [
                'query' => [
                    'q' => $location,
                    'format' => 'json',
                    'limit' => '1'
                ]
            ]
        );

        $content = json_decode((string) ($response->getBody()), true);

        if (!empty($content)) {
            $destination->setLat($content[0]['lat']);
            $destination->setLng($content[0]['lon']);
        }
        //...
    }

    //...
}
```

Plusieurs choses à noter ici :

`$client->request` nous renvoie une réponse sur laquelle nous pourrons exécuter la méthode `getBody()`, afin d'avoir le contenu de la réponse. En convertissant la réponse en chaîne de caractère, on pourra accéder à notre tableau JSON renvoyé.

Nous pouvons donc convertir ce tableau JSON en variable PHP avec la méthode `json_decode`.

Vu qu'on a un tableau de résultats, on vérifie si on en a au moins un (que le tableau n'est pas vide), puis on récupère les informations qui nous intéressent : `lat` et `lon`.

## Factorisation, séparation des responsabilités

Le code qui effectue la requête se trouve actuellement dans le contrôleur.

> Si nous voulons que notre code soit plus flexible, nous devons séparer la responsabilité de créer un client Guzzle, faire la requête puis caster le résultat retourné. Nous allons créer un service qui se chargera de faire ça pour nous, et nous renverra directement le tableau de résultats.
>
> Si on faisait des tests unitaires, notre code serait également plus facile à tester !

### Création du service (v1)

> Fichier : src/Geocoding/OsmGeocoding.php

```php
<?php

namespace App\Geocoding;

use GuzzleHttp\Client;

class OsmGeocoding
{
    /**
     * Geocodes a given location
     *
     * @param string $location
     * @return array empty if no result
     */
    public function geocode(string $location): array
    {
        $client = new Client();

        // TODO: Gérer les erreurs avec try/catch
        // http://docs.guzzlephp.org/en/stable/quickstart.html#exceptions
        $response = $client->request(
            'GET',
            'https://nominatim.openstreetmap.org/search',
            [
                'query' => [
                    'q' => $location,
                    'format' => 'json',
                    'limit' => '1'
                ]
            ]
        );

        $content = json_decode((string) ($response->getBody()), true);

        return $content;
    }
}

```

> **Note sur les services : quand vous créez un service dans votre application, vous créez comme une fonctionnalité que vous pourrez utiliser un peu partout dans votre application ensuite.**
>
> **Tous vos services sont "autowirés", c'est-à-dire que vous pouvez les type-hinter dans d'autres services ou contrôleurs directement, juste après les avoir écrits.**

Nous allons donc pouvoir utiliser notre service dans notre contrôleur :

> Fichier : src/Controller/Admin/DestinationController.php

```php
// use App\Geocoding\OsmGeocoding;

public function new(Request $request, OsmGeocoding $geocodingService): Response
{
    //...

    if ($form->isSubmitted() && $form->isValid()) {
        $location = $destination->getVille() . ', ' . $destination->getPays()->getNom();

        $geoData = $geocodingService->geocode($location);

        if (!empty($geoData)) {
            $destination->setLat($geoData[0]['lat']);
            $destination->setLng($geoData[0]['lon']);
        }

        //...
    }

    //...
}
```

On laisse la responsabilité au contrôleur de construire la chaîne `$location`.

*Sinon, on aurait pu concevoir notre méthode `geocode` pour qu'elle prenne en paramètre un objet `Destination`, et qu'elle génère toute seule la variable `$location`.*

**Mais cela aurait introduit un couplage trop fort entre notre service de geocoding et l'entité `Destination`. Notre servide de geocoding est là uniquement pour "geocoder des informations qu'on lui transmet", pas pour "geocoder une destination" spécifiquement. En fait, de manière générale, on va vouloir limiter le couplage entre objets pour rester plus flexible et plus ouvert aux évolutions de notre application (exemple : si j'avais couplé mon service `OsmGeocoding` à l'entité `Destination`, comment aurais-je fait si je devais géocoder les informations d'autres entités, ou d'autres sources ?**

### Evolution du service : paramètre d'application (v2)

L'URL de l'API OpenStreetMap se retrouve "codée en dur" dans notre méthode `geocode`.

Du coup, si j'ai besoin de faire une autre méthode dans mon service (l'API offre par exemple un endpoint "/reverse", pour rechercher une localisation à partir d'une latitude et une longitude par exemple), je devrai répéter la base de l'URL.

> Nous allons utiliser un paramètre d'application et la liaison manuelle d'arguments pour configurer automatiquement notre URL de base dans le service.

#### Préparation de la structure du service

> Fichier : src/Geocoding/OsmGeocoding.php

```php
<?php

namespace App\Geocoding;

use GuzzleHttp\Client;

class OsmGeocoding implements IGeocoding
{
    private $baseUrl;
    private $client;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $this->client = new Client();
    }

    /**
     * Geocodes a given location
     *
     * @param string $location
     * @return array empty if no result
     */
    public function geocode(string $location): array
    {
        // TODO: Gérer les erreurs avec try/catch
        // http://docs.guzzlephp.org/en/stable/quickstart.html#exceptions
        $response = $this->client->request(
            'GET',
            $this->baseUrl . '/search',
            [
                'query' => [
                    'q' => $location,
                    'format' => 'json',
                    'limit' => '1'
                ]
            ]
        );

        $content = json_decode((string) ($response->getBody()), true);

        return $content;
    }
}

```

> On en a profité pour passer la construction du `Client` dans le constructeur du service, et le client en attribut privé de la classe. On pourra également utiliser le client dans d'autres méthodes du coup.

#### Définition du paramètre et liaison d'argument pour le container de services

> Fichier : config/services.yaml

```yaml
parameters:
  #...
  api_osm_base_url: 'https://nominatim.openstreetmap.org'

services:
    #...
    App\Geocoding\OsmGeocoding:
      arguments:
        $baseUrl: '%api_osm_base_url%'
```

Ainsi, lorsque le container de services va créer le service `OsmGeocoding`, il injectera automatiquement l'URL de base.

- On pourra donc la réutiliser dans d'autres méthodes
- Si l'API d'OpenStreetMap change d'URL, nous n'aurons qu'à la changer dans nos paramètres d'application

### Evolution du service : inversion de dépendance (v3)

On est déjà pas mal pour le moment, mais il reste un problème dans notre application :

> Que se passe-t-il si à l'avenir, on n'utilise plus OpenStreetMap comme API de Geocoding, mais Google Maps par exemple ?

Nous voulons pouvoir changer l'implémentation de notre Geocoding, sans impacter le code qui consomme ce service.

Après tout, le contrôleur `new` dans `DestinationController` se fout d'utiliser OpenStreetMaps ou GoogleMaps, il veut simplement avoir des données de géolocalisation, peu importe le moyen utilisé.

> Nous allons donc faire en sorte que le contrôleur s'appuie sur une abstraction plutôt qu'une implémentation concrète, puis configurer notre container de services pour qu'il injecte le type de service qu'on voudra quand il rencontrera cette abstraction

#### Définition du contrat d'implémentation

Pour que notre contrôleur s'appuie sur une abstraction, il faut définir un contrat d'implémentation afin qu'on soit sûrs que notre contrôleur pourra appeler une méthode précise dans le service.

Nous allons donc créer une interface `IGeocoding`.

> Fichier : src/Geocoding/IGeocoding.php

```php
<?php

namespace App\Geocoding;

interface IGeocoding
{
    public function geocode(string $location): array;
}

```

Puis nous allons changer notre classe `OsmGeocoding` pour qu'elle implémente cette interface (ça tombe bien elle respecte déjà le contrat d'implémentation).

> Fichier : src/Geocoding/OsmGeocoding.php

```php
//...
class OsmGeocoding implements IGeocoding
//...
```

Enfin, dans notre contrôleur, nous allons injecter l'interface `IGeocoding` :

> Fichier : src/Controller/Admin/DestinationController.php

```php
/**
 * @Route("/new", name="destination_new", methods={"GET","POST"})
 */
public function new(Request $request, IGeocoding $geocodingService): Response
{
    //...
}
```

#### Configuration du container de services

Avec une seule implémentation, le container est suffisamment intelligent pour injecter notre service `OsmGeocoding`.

Mais nous allons le configurer explicitement pour lui dire que dès qu'il rencontre le type-hint `IGeocoding`, le type d'implémentation à utiliser sera `OsmGeocoding`.

> Fichier : config/services.yaml

```yaml
parameters:
    #...

services:
    #...

    App\Util\Geocoding\IGeocoding: '@App\Util\Geocoding\OsmGeocoding'
```

> Le '@' avant le FQCN du service permet de désigner le service lui-même, et qu'il ne s'agit pas d'une chaîne de caractère

Nous avons donc réalisé une **inversion de dépendance** : plutôt que notre contrôleur dépende d'une implémentation de la fonctionnalité de geocoding, il dépend d'une abstraction. C'est nous qui déciderons quelle implémentation sera utilisée.
