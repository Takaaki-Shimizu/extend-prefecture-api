<?php

namespace Tests\Unit\UseCases;

use App\Services\AddressParser;
use App\UseCases\ExtractPrefectureUseCase;
use Tests\TestCase;
use Mockery;
use Exception;

class ExtractPrefectureUseCaseTest extends TestCase
{
    public function test_returns_prefecture_when_direct_extraction_succeeds()
    {
        $addressParserMock = Mockery::mock(AddressParser::class);
        $addressParserMock->shouldReceive('extractPrefecture')
            ->with('茨城県鹿嶋市神向寺後山２６−２')
            ->andReturn('茨城県');

        $useCase = new ExtractPrefectureUseCase($addressParserMock);
        $result = $useCase->execute('茨城県鹿嶋市神向寺後山２６−２');

        $this->assertEquals('茨城県', $result);
    }

    public function test_throws_exception_when_prefecture_not_found()
    {
        $addressParserMock = Mockery::mock(AddressParser::class);
        $addressParserMock->shouldReceive('extractPrefecture')
            ->with('不明な住所')
            ->andReturn(null);

        $useCase = new ExtractPrefectureUseCase($addressParserMock);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('都道府県が見つかりませんでした。');

        $useCase->execute('不明な住所');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}