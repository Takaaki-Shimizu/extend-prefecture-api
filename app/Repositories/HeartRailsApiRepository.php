<?php

namespace App\Repositories;

use App\Repositories\Interfaces\HeartRailsApiInterface;
use App\Models\Location;
use App\Exceptions\ExternalResourceNotFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HeartRailsApiRepository implements HeartRailsApiInterface
{
    private const HEART_RAILS_GEO_API_URL = 'https://geoapi.heartrails.com/api/json';

    private Client $http;

    public function __construct(Client $http)
    {
        $this->http = $http;
    }

    public function findByAddress(string $extractedCity): ?Location
    {
        try {
            $response = $this->http->request('GET', self::HEART_RAILS_GEO_API_URL, [
                'query' => [
                    'method' => 'suggest',
                    'matching' => 'like',
                    'keyword' => $extractedCity,
                ],
            ]);

            $contents = json_decode($response->getBody()->getContents(), true);

            if (isset($contents['response']['error'])) {
                throw new ExternalResourceNotFoundException('HeartRails API returned error: ' . $contents['response']['error']);
            }

            if (!isset($contents['response']['location']) || empty($contents['response']['location'])) {
                throw new ExternalResourceNotFoundException('Location not found in HeartRails API response');
            }

            return new Location($contents['response']['location'][0]);
        } catch (GuzzleException $e) {
            throw new ExternalResourceNotFoundException('Failed to connect to HeartRails API: ' . $e->getMessage());
        }
    }
}