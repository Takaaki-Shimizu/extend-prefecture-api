<?php

namespace Tests\Unit\Repositories;

use App\Repositories\HeartRailsApiRepository;
use App\Models\Location;
use App\Exceptions\ExternalResourceNotFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use Mockery;

class HeartRailsApiRepositoryTest extends TestCase
{
    private HeartRailsApiRepository $repository;
    private $mockClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockClient = Mockery::mock(Client::class);
        $this->repository = new HeartRailsApiRepository($this->mockClient);
    }

    public function test_can_find_location_by_address()
    {
        $mockResponse = new Response(200, [], json_encode([
            'response' => [
                'location' => [
                    [
                        'prefecture' => '茨城県',
                        'city' => '鹿嶋市',
                        'town' => '神向寺'
                    ]
                ]
            ]
        ]));

        $this->mockClient
            ->shouldReceive('request')
            ->once()
            ->with('GET', 'https://geoapi.heartrails.com/api/json', [
                'query' => [
                    'method' => 'suggest',
                    'matching' => 'like',
                    'keyword' => '鹿嶋市神向寺後山',
                ],
            ])
            ->andReturn($mockResponse);

        $result = $this->repository->findByAddress('鹿嶋市神向寺後山');

        $this->assertInstanceOf(Location::class, $result);
        $this->assertEquals('茨城県', $result->prefecture());
    }

    public function test_throws_exception_when_api_returns_error()
    {
        $mockResponse = new Response(200, [], json_encode([
            'response' => [
                'error' => 'Not found'
            ]
        ]));

        $this->mockClient
            ->shouldReceive('request')
            ->once()
            ->andReturn($mockResponse);

        $this->expectException(ExternalResourceNotFoundException::class);

        $this->repository->findByAddress('存在しない市');
    }

    public function test_handles_network_error()
    {
        $this->mockClient
            ->shouldReceive('request')
            ->once()
            ->andThrow(new \Exception('Network error'));

        $this->expectException(\Exception::class);

        $this->repository->findByAddress('鹿嶋市神向寺後山');
    }
}