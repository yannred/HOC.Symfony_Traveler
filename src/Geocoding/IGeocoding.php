<?php

namespace App\Geocoding;

interface IGeocoding
{
    public function geocode(string $location): array;
}