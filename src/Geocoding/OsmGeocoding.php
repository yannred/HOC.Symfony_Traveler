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
