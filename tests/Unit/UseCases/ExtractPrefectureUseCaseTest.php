<?php

namespace Tests\Unit\UseCases;

use App\Services\AddressParser;
use App\Services\AiApiFactory;
use App\UseCases\ExtractPrefectureUseCase;
use App\Repositories\Interfaces\HeartRailsApiInterface;
use App\Repositories\Interfaces\AiApiInterface;
use App\Exceptions\ExternalResourceNotFoundException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Mockery;
use Exception;

class ExtractPrefectureUseCaseTest extends TestCase
{
    public function test_returns_prefecture_when_direct_extraction_succeeds()
    {
        $addressParserMock = Mockery::mock(AddressParser::class);
        $heartRailsApiMock = Mockery::mock(HeartRailsApiInterface::class);

        $addressParserMock->shouldReceive('extractPrefecture')
            ->with('茨城県鹿嶋市神向寺後山２６−２')
            ->andReturn('茨城県');

        $useCase = new ExtractPrefectureUseCase($addressParserMock, $heartRailsApiMock);
        $result = $useCase->execute('茨城県鹿嶋市神向寺後山２６−２');

        $this->assertEquals('茨城県', $result);
    }

    public function test_uses_ai_fallback_when_direct_extraction_and_heartrails_fail()
    {
        // HTTPリクエストをモック
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => '東京都'
                        ]
                    ]
                ]
            ], 200)
        ]);

        Config::set('ai.openai.api_key', 'test-key');
        Config::set('prefectures.list', [
            '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
            '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
            '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
            '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
            '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
            '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
            '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
        ]);

        $addressParserMock = Mockery::mock(AddressParser::class);
        $heartRailsApiMock = Mockery::mock(HeartRailsApiInterface::class);

        // フェーズ1: 直接抽出失敗
        $addressParserMock->shouldReceive('extractPrefecture')
            ->with('曖昧な住所')
            ->andReturn(null);

        // フェーズ2: 都市抽出は成功するがHeartRails API失敗
        $addressParserMock->shouldReceive('extractCity')
            ->with('曖昧な住所')
            ->andReturn('渋谷区');

        $heartRailsApiMock->shouldReceive('findByAddress')
            ->with('渋谷区')
            ->andThrow(new ExternalResourceNotFoundException('API failed'));

        $useCase = new ExtractPrefectureUseCase($addressParserMock, $heartRailsApiMock);
        $result = $useCase->execute('曖昧な住所');

        $this->assertEquals('東京都', $result);
    }

    public function test_throws_exception_when_all_methods_fail()
    {
        Config::set('ai.openai.api_key', null);
        Config::set('ai.gemini.api_key', null);

        $addressParserMock = Mockery::mock(AddressParser::class);
        $heartRailsApiMock = Mockery::mock(HeartRailsApiInterface::class);

        // フェーズ1: 直接抽出失敗
        $addressParserMock->shouldReceive('extractPrefecture')
            ->with('不明な住所')
            ->andReturn(null);

        // フェーズ2: 都市抽出も失敗
        $addressParserMock->shouldReceive('extractCity')
            ->with('不明な住所')
            ->andReturn(null);

        $useCase = new ExtractPrefectureUseCase($addressParserMock, $heartRailsApiMock);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('都道府県が見つかりませんでした。（AI APIが利用できません）');

        $useCase->execute('不明な住所');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}