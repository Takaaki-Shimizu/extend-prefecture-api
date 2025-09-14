<?php

namespace App\UseCases;

use App\Services\AddressParser;
use App\Repositories\Interfaces\HeartRailsApiInterface;
use App\Exceptions\ExternalResourceNotFoundException;
use Exception;

class ExtractPrefectureUseCase
{
    private AddressParser $addressParser;
    private HeartRailsApiInterface $heartRailsApi;

    public function __construct(AddressParser $addressParser, HeartRailsApiInterface $heartRailsApi)
    {
        $this->addressParser = $addressParser;
        $this->heartRailsApi = $heartRailsApi;
    }

    public function execute(string $address): string
    {
        // フェーズ1: 都道府県名の直接抽出
        $prefecture = $this->addressParser->extractPrefecture($address);

        if ($prefecture) {
            return $prefecture;
        }

        // フェーズ2: HeartRails API検索
        $extractedCity = $this->addressParser->extractCity($address);
        if ($extractedCity) {
            try {
                $location = $this->heartRailsApi->findByAddress($extractedCity);
                if ($location) {
                    return $location->prefecture();
                }
            } catch (ExternalResourceNotFoundException $e) {
                // フェーズ3: AIフォールバック（未実装）
                // 現在はそのまま例外をスロー
            }
        }

        throw new Exception('都道府県が見つかりませんでした。');
    }
}