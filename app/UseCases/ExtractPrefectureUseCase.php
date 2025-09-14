<?php

namespace App\UseCases;

use App\Services\AddressParser;
use Exception;

class ExtractPrefectureUseCase
{
    private AddressParser $addressParser;

    public function __construct(AddressParser $addressParser)
    {
        $this->addressParser = $addressParser;
    }

    public function execute(string $address): string
    {
        // フェーズ1: 都道府県名の直接抽出
        $prefecture = $this->addressParser->extractPrefecture($address);

        if ($prefecture) {
            return $prefecture;
        }

        // TODO: フェーズ2: HeartRails API検索の実装
        // TODO: フェーズ3: AIフォールバックの実装

        throw new Exception('都道府県が見つかりませんでした。');
    }
}