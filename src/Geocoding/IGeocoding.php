<?php

namespace App\Geocoding;

use App\Entity\LatLng;

interface IGeocoding
{
  public function getLatLon(string $location): ?LatLng;
}
