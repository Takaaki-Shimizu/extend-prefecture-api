<?php

namespace Tests\Unit\Services;

use App\Services\AddressParser;
use Tests\TestCase;

class AddressParserTest extends TestCase
{
    private AddressParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new AddressParser();
    }

    public function test_can_extract_prefecture_name_directly()
    {
        $result = $this->parser->extractPrefecture('茨城県鹿嶋市神向寺後山２６−２');

        $this->assertEquals('茨城県', $result);
    }

    public function test_can_extract_various_prefectures()
    {
        $testCases = [
            '北海道札幌市中央区' => '北海道',
            '東京都新宿区歌舞伎町' => '東京都',
            '京都府京都市中京区' => '京都府',
            '大阪府大阪市北区' => '大阪府',
            '沖縄県那覇市泉崎' => '沖縄県',
        ];

        foreach ($testCases as $address => $expected) {
            $result = $this->parser->extractPrefecture($address);
            $this->assertEquals($expected, $result, "Failed to extract {$expected} from {$address}");
        }
    }

    public function test_returns_null_when_no_prefecture_found()
    {
        $result = $this->parser->extractPrefecture('鹿嶋市神向寺後山２６−２');

        $this->assertNull($result);
    }

    public function test_can_extract_prefecture_with_postal_code()
    {
        $result = $this->parser->extractPrefecture('〒314-0007 茨城県鹿嶋市神向寺後山２６−２');

        $this->assertEquals('茨城県', $result);
    }

    public function test_returns_null_for_empty_string()
    {
        $result = $this->parser->extractPrefecture('');

        $this->assertNull($result);
    }

    public function test_returns_null_for_invalid_prefecture_name()
    {
        $result = $this->parser->extractPrefecture('存在しない県の住所');

        $this->assertNull($result);
    }

    public function test_can_extract_city_and_town()
    {
        $result = $this->parser->extractCity('鹿嶋市神向寺後山２６−２');

        $this->assertEquals('鹿嶋市神向寺後山', $result);
    }

    public function test_can_extract_city_with_postal_code()
    {
        $result = $this->parser->extractCity('〒314-0007 鹿嶋市神向寺後山２６−２');

        $this->assertEquals('鹿嶋市神向寺後山', $result);
    }

    public function test_can_extract_city_with_old_character()
    {
        $result = $this->parser->extractCity('鹿嶋市大字神向寺後山２６−２');

        $this->assertEquals('鹿嶋市神向寺後山', $result);
    }

    public function test_can_extract_various_cities()
    {
        $testCases = [
            '札幌市中央区南1条' => '札幌市中央区南',
            '新宿区歌舞伎町1-1-1' => '新宿区歌舞伎町',
            '京都市中京区烏丸通2丁目' => '京都市中京区烏丸通',
            '大阪市北区梅田1番地' => '大阪市北区梅田',
            '那覇市泉崎1-2-3' => '那覇市泉崎',
        ];

        foreach ($testCases as $address => $expected) {
            $result = $this->parser->extractCity($address);
            $this->assertEquals($expected, $result, "Failed to extract {$expected} from {$address}");
        }
    }

    public function test_returns_null_when_no_city_found()
    {
        $result = $this->parser->extractCity('神向寺後山２６−２');

        $this->assertNull($result);
    }

    public function test_returns_null_for_empty_string_city_extraction()
    {
        $result = $this->parser->extractCity('');

        $this->assertNull($result);
    }
}