<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrefectureExtractionTest extends TestCase
{
    /**
     * 都道府県名直接抽出のテスト
     * 住所文字列に都道府県名が含まれている場合、正規表現で抽出して即座に返却
     */
    public function test_can_extract_prefecture_directly_from_address()
    {
        $response = $this->postJson('/api/extract-prefecture', [
            'address' => '〒314-0007 茨城県鹿嶋市神向寺後山２６−２'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['prefecture' => '茨城県']);
    }

    /**
     * 都道府県名のみのパターンのテスト
     */
    public function test_can_extract_prefecture_from_simple_address()
    {
        $response = $this->postJson('/api/extract-prefecture', [
            'address' => '茨城県鹿嶋市神向寺後山２６−２'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['prefecture' => '茨城県']);
    }

    /**
     * 異なる都道府県での直接抽出テスト
     */
    public function test_can_extract_various_prefectures_directly()
    {
        $testCases = [
            ['address' => '北海道札幌市中央区南1条西1丁目', 'expected' => '北海道'],
            ['address' => '東京都新宿区歌舞伎町1-1-1', 'expected' => '東京都'],
            ['address' => '京都府京都市中京区烏丸通二条下ル', 'expected' => '京都府'],
            ['address' => '大阪府大阪市北区梅田1-1-1', 'expected' => '大阪府'],
            ['address' => '沖縄県那覇市泉崎1-2-3', 'expected' => '沖縄県'],
        ];

        foreach ($testCases as $testCase) {
            $response = $this->postJson('/api/extract-prefecture', [
                'address' => $testCase['address']
            ]);

            $response->assertStatus(200);
            $response->assertJson(['prefecture' => $testCase['expected']]);
        }
    }

    /**
     * バリデーションエラーのテスト - 空文字列
     */
    public function test_validation_fails_for_empty_address()
    {
        $response = $this->postJson('/api/extract-prefecture', [
            'address' => ''
        ]);

        $response->assertStatus(422);
    }

    /**
     * バリデーションエラーのテスト - 200文字超過
     */
    public function test_validation_fails_for_too_long_address()
    {
        $longAddress = str_repeat('あ', 201);

        $response = $this->postJson('/api/extract-prefecture', [
            'address' => $longAddress
        ]);

        $response->assertStatus(422);
    }

    /**
     * HeartRails API検索のテスト
     * 都道府県名が見つからない場合、市区町村+地名を抽出してHeartRails Geo APIに問い合わせ
     */
    public function test_can_extract_prefecture_via_heartrails_api()
    {
        $response = $this->postJson('/api/extract-prefecture', [
            'address' => '渋谷区渋谷1-1-1'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['prefecture' => '東京都']);
    }

    /**
     * 郵便番号＋市区町村のパターンでHeartRails API検索のテスト
     */
    public function test_can_extract_prefecture_via_heartrails_api_with_postal_code()
    {
        $response = $this->postJson('/api/extract-prefecture', [
            'address' => '〒150-0002 渋谷区渋谷1-1-1'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['prefecture' => '東京都']);
    }

    /**
     * 旧字表記でのHeartRails API検索のテスト
     */
    public function test_can_extract_prefecture_via_heartrails_api_with_old_character()
    {
        $response = $this->postJson('/api/extract-prefecture', [
            'address' => '渋谷区大字渋谷1-1-1'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['prefecture' => '東京都']);
    }
}