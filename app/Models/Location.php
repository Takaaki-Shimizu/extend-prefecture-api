<?php

namespace App\Models;

class Location
{
    private string $prefecture;
    private string $city;
    private string $town;

    public function __construct(array $locationData)
    {
        $this->prefecture = $locationData['prefecture'] ?? '';
        $this->city = $locationData['city'] ?? '';
        $this->town = $locationData['town'] ?? '';
    }

    public function prefecture(): string
    {
        return $this->prefecture;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function town(): string
    {
        return $this->town;
    }
}