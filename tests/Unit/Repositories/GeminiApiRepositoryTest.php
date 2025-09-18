<?php

namespace Tests\Unit\Repositories;

use App\Repositories\GeminiApiRepository;
use App\Exceptions\ExternalResourceNotFoundException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class GeminiApiRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('ai.gemini.api_key', 'test-api-key');
        Config::set('ai.gemini.model', 'gemini-pro');
        Config::set('prefectures.list', [
            '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
            '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
            '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
            '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
            '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
            '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
            '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
        ]);
    }

    public function test_extractPrefectureByAi_成功時は都道府県名を返す()
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => '大阪府'
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $repository = new GeminiApiRepository();
        $result = $repository->extractPrefectureByAi('大阪府大阪市北区梅田1-1-1');

        $this->assertEquals('大阪府', $result);
    }

    public function test_extractPrefectureByAi_不明な場合はnullを返す()
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => '不明'
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $repository = new GeminiApiRepository();
        $result = $repository->extractPrefectureByAi('海外の住所');

        $this->assertNull($result);
    }

    public function test_extractPrefectureByAi_API失敗時は例外をスロー()
    {
        Http::fake([
            '*' => Http::response([], 500)
        ]);

        $repository = new GeminiApiRepository();

        $this->expectException(ExternalResourceNotFoundException::class);
        $repository->extractPrefectureByAi('大阪府大阪市北区梅田1-1-1');
    }

    public function test_extractPrefectureByAi_レスポンスに都道府県名が含まれていない場合はnullを返す()
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => '関西地方'
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $repository = new GeminiApiRepository();
        $result = $repository->extractPrefectureByAi('関西地方のどこか');

        $this->assertNull($result);
    }
}