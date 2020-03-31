<?php

namespace App\Geocoding;

use App\Entity\LatLng;

final class OsmGeocoding extends AbstractGeocoding
{
  public function getLatLon(string $location): ?LatLng
  {
    $geoData = $this->geocode($location);

    if (empty($geoData)) {
      return null;
    }

    return new LatLng(
      $geoData[0]['lat'],
      $geoData[0]['lon']
    );
  }

  /**
   * Geocodes a given location
   *
   * @param string $location
   * @return array empty if no result
   */
  protected function geocode(string $location): array
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

    $content = json_decode((string) ($response->getContent()), true);

    return $content;
  }
}
