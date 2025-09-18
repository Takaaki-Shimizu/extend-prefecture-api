<?php

namespace App\UseCases;

use App\Services\AddressParser;
use App\Services\AiApiFactory;
use App\Repositories\Interfaces\HeartRailsApiInterface;
use App\Exceptions\ExternalResourceNotFoundException;
use Illuminate\Support\Facades\Log;
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
                // フェーズ3: AIフォールバック
                Log::info('HeartRails API failed, trying AI fallback', ['address' => $address]);
                return $this->tryAiFallback($address);
            }
        }

        // HeartRails APIでも見つからない場合はAIフォールバックを試行
        Log::info('City not extracted, trying AI fallback directly', ['address' => $address]);
        return $this->tryAiFallback($address);
    }

    private function tryAiFallback(string $address): string
    {
        $availableProviders = AiApiFactory::getAvailableProviders();

        if (empty($availableProviders)) {
            Log::warning('No AI providers available');
            throw new Exception('都道府県が見つかりませんでした。（AI APIが利用できません）');
        }

        foreach ($availableProviders as $provider) {
            try {
                $aiApi = AiApiFactory::create($provider);
                $prefecture = $aiApi->extractPrefectureByAi($address);

                if ($prefecture) {
                    Log::info('AI fallback succeeded', [
                        'provider' => $provider,
                        'address' => $address,
                        'prefecture' => $prefecture
                    ]);
                    return $prefecture;
                }
            } catch (ExternalResourceNotFoundException $e) {
                Log::warning('AI provider failed', [
                    'provider' => $provider,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        Log::error('All AI providers failed', ['address' => $address]);
        throw new Exception('都道府県が見つかりませんでした。');
    }
}