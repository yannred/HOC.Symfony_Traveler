<?php

namespace App\Geocoding;

use App\Entity\LatLng;

final class GoogleMapsGeocoding extends AbstractGeocodingWithApiKey
{
  public function getLatLon(string $location): ?LatLng
  {
    $geoData = $this->geocode($location);

    if (empty($geoData["results"])) {
      return null;
    }

    return new LatLng(
      $geoData["results"][0]["geometry"]["location"]['lat'],
      $geoData["results"][0]["geometry"]["location"]['lng']
    );
  }

  protected function geocode(string $location): array
  {
    return [
      "results" => [
        [
          // "address_components" : [...
          "geometry" => [
            "location" => [
              "lat" => 37.4224764,
              "lng" => -122.0842499
            ]
          ]
          // location_type ...
        ]
      ],
      "status" => "OK"
    ];
  }
}
